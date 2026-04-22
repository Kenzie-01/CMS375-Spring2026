<?php
session_start();
include __DIR__ . '/db_connect.php';
include __DIR__ . '/poster_helper.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: movies.php");
    exit();
}

$is_guest = ($_SESSION['user_id'] === 'Guest');
$movie_id = $_GET['id'];

$review_error   = "";
$review_success = isset($_GET['reviewed']) && $_GET['reviewed'] == '1';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if ($is_guest) {
        $review_error = "You must create an account to write reviews.";
    } else {
        $score   = intval($_POST['score']);
        $text    = trim($_POST['review_text']);
        $user_id = $_SESSION['user_id'];

        if ($score < 1 || $score > 10) {
            $review_error = "Score must be between 1 and 10.";
        } elseif (empty($text)) {
            $review_error = "Review text cannot be empty.";
        } else {
            // Check duplicate
            $stmt = $conn->prepare("SELECT ReviewID FROM Reviews WHERE UserID = ? AND MovieID = ?");
            $stmt->bind_param("ss", $user_id, $movie_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $review_error = "You have already reviewed this movie.";
            }
            $stmt->close();

            if (!$review_error) {
                // Generate review ID
                $max_stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(ReviewID, 5) AS UNSIGNED)) AS max_id FROM Reviews WHERE ReviewID LIKE 'rev_%'");
                $max_stmt->execute();
                $max_result = $max_stmt->get_result();
                $max_row    = $max_result->fetch_assoc();
                $next_id    = (int)($max_row['max_id'] ?? 0) + 1;
                $rev_id     = 'rev_' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
                $max_stmt->close();

                $stmt = $conn->prepare("INSERT INTO Reviews (ReviewID, MovieID, UserID, Score, Text) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $rev_id, $movie_id, $user_id, $score, $text);
                $stmt->execute();
                $stmt->close();

                header("Location: movie.php?id=" . urlencode($movie_id) . "&reviewed=1");
                exit();
            }
        }
    }
}

// Fetch movie
$stmt = $conn->prepare("SELECT * FROM Movies WHERE MovieID = ?");
$stmt->bind_param("s", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<p style='color:#fff;padding:40px;'>Movie not found.</p>"; exit();
}
$movie = $result->fetch_assoc();
$stmt->close();

// Check if current user already reviewed
$user_already_reviewed = false;
if (!$is_guest) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT ReviewID FROM Reviews WHERE UserID = ? AND MovieID = ?");
    $stmt->bind_param("ss", $uid, $movie_id);
    $stmt->execute();
    $stmt->store_result();
    $user_already_reviewed = ($stmt->num_rows > 0);
    $stmt->close();
}

// Fetch reviews
$stmt = $conn->prepare("SELECT Reviews.*, Users.UserID, Users.UserType
                         FROM Reviews
                         JOIN Users ON Reviews.UserID = Users.UserID
                         WHERE Reviews.MovieID = ?
                         ORDER BY Reviews.Score DESC");
$stmt->bind_param("s", $movie_id);
$stmt->execute();
$review_result = $stmt->get_result();
$review_count  = $review_result->num_rows;
$stmt->close();

// Avg score
$stmt = $conn->prepare("SELECT AVG(Score) AS avg_score FROM Reviews WHERE MovieID = ?");
$stmt->bind_param("s", $movie_id);
$stmt->execute();
$avg_row   = $stmt->get_result()->fetch_assoc();
$avg_score = $avg_row['avg_score'] ? number_format($avg_row['avg_score'], 1) : null;
$stmt->close();

// Fetch actors
$stmt = $conn->prepare("SELECT Actor_Name FROM Actors WHERE MovieID = ? ORDER BY ActorID");
$stmt->bind_param("s", $movie_id);
$stmt->execute();
$actors_result = $stmt->get_result();
$actor_list = [];
while ($a = $actors_result->fetch_assoc()) $actor_list[] = $a['Actor_Name'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['Title']); ?> - MTM Studios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #0a0a0a; color: #ffffff; }
        .navbar { background-color: #0a0a0a; border-bottom: 2px solid #ffffff; padding: 14px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 20px; font-weight: bold; color: #5b80a8; text-decoration: none; letter-spacing: 2px; }
        .nav-right { display: flex; align-items: center; }
        .nav-divider { width: 2px; height: 28px; background-color: #ffffff; margin: 0 16px; }
        .nav-icons { display: flex; align-items: center; gap: 16px; }
        .nav-icons a { color: #aaa; text-decoration: none; font-size: 22px; padding: 6px 10px; border: 1px solid transparent; border-radius: 8px; transition: all 0.2s; }
        .nav-icons a:hover, .nav-icons a.active { color: #fff; border-color: #fff; background-color: #1a1a1a; }
        .user-info { display: flex; align-items: center; gap: 10px; margin-left: 16px; font-size: 13px; color: #888; }
        .user-badge { background: #5b80a8; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .logout-link { color: #555; text-decoration: none; font-size: 12px; transition: color 0.2s; }
        .logout-link:hover { color: #ff5050; }
        .create-acct-link { color: #5b80a8; text-decoration: none; font-size: 12px; border: 1px solid #5b80a8; padding: 4px 10px; border-radius: 6px; transition: all 0.2s; }
        .create-acct-link:hover { background: #5b80a8; color: #fff; }
        .page-content { max-width: 900px; margin: 0 auto; padding: 32px 30px 60px; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: #5b80a8; text-decoration: none; font-size: 13px; margin-bottom: 24px; transition: color 0.2s; }
        .back-link:hover { color: #fff; }
        .movie-header { border: 2px solid #ffffff; border-radius: 14px; padding: 28px; margin-bottom: 20px; display: flex; gap: 24px; align-items: flex-start; }
        .poster-column { flex-shrink: 0; width: 160px; }
        .poster-wrap { width: 160px; height: 240px; border-radius: 10px; overflow: hidden; position: relative; background: #111; border: 1px solid #2a2a2a; }
        .poster-img { width: 100%; height: 100%; object-fit: cover; object-position: center top; display: block; }
        .poster-fallback { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px; background: linear-gradient(145deg, #1a2535 0%, #0d1520 100%); text-align: center; gap: 12px; }
        .poster-fallback-genre { font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #5b80a8; font-weight: 700; }
        .poster-fallback-title { font-size: 13px; font-weight: 800; color: #ddd; line-height: 1.35; }
        .poster-fallback-year { font-size: 11px; color: #555; }
        .header-details { flex: 1; min-width: 0; }
        .movie-header h1 { font-size: 26px; font-weight: 800; margin-bottom: 12px; line-height: 1.2; }
        .movie-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
        .meta-pill { font-size: 12px; background: #111; border: 1px solid #2a2a2a; border-radius: 20px; padding: 4px 14px; color: #aaa; }
        .meta-pill span { color: #fff; font-weight: 600; }
        .header-bottom { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
        .genre-tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .genre-tag { font-size: 12px; border: 1px solid #5b80a8; color: #5b80a8; padding: 3px 12px; border-radius: 20px; }
        .score-wrap { display: flex; align-items: center; gap: 10px; }
        .big-score { background: #5b80a8; color: #fff; padding: 6px 20px; border-radius: 24px; font-size: 20px; font-weight: 800; white-space: nowrap; }
        .user-score { background: #1a1a1a; border: 1px solid #2a2a2a; color: #aaa; padding: 6px 14px; border-radius: 24px; font-size: 14px; white-space: nowrap; }
        @media (max-width: 560px) { .movie-header { flex-direction: column; align-items: center; } .poster-column { width: 100%; display: flex; justify-content: center; } }
        .movie-info { border: 1px solid #1c1c1c; border-radius: 14px; padding: 24px; margin-bottom: 20px; background: #0f0f0f; }
        .info-section { margin-bottom: 18px; }
        .info-section:last-child { margin-bottom: 0; }
        .info-label { font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; font-weight: 600; }
        .info-text { font-size: 14px; color: #bbb; line-height: 1.7; }
        .actor-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .actor-tag { font-size: 12px; background: #111; border: 1px solid #2a2a2a; color: #aaa; padding: 3px 12px; border-radius: 6px; }
        .review-form-box { border: 1px solid #2a2a2a; border-radius: 14px; padding: 24px; margin-bottom: 20px; background: #0f0f0f; }
        .review-form-title { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border-bottom: 2px solid #5b80a8; padding-bottom: 5px; margin-bottom: 18px; }
        .score-row { display: flex; align-items: center; gap: 16px; margin-bottom: 14px; flex-wrap: wrap; }
        .score-row label { font-size: 11px; color: #555; text-transform: uppercase; letter-spacing: 2px; white-space: nowrap; }
        .score-select { padding: 8px 14px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 14px; }
        .score-select:focus { outline: none; border-color: #5b80a8; }
        .score-select option { background: #111; }
        .review-textarea { width: 100%; padding: 12px 16px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 14px; line-height: 1.6; resize: vertical; min-height: 100px; font-family: inherit; margin-bottom: 14px; }
        .review-textarea:focus { outline: none; border-color: #5b80a8; }
        .review-textarea::placeholder { color: #444; }
        .submit-btn { padding: 10px 24px; background: #5b80a8; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s, transform 0.15s; }
        .submit-btn:hover { background: #4a6a90; transform: translateY(-2px); }
        .form-error { background: rgba(255,80,80,0.1); border: 1px solid rgba(255,80,80,0.3); color: #ff5050; padding: 10px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; }
        .form-success { background: rgba(80,200,120,0.1); border: 1px solid rgba(80,200,120,0.3); color: #50c878; padding: 10px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; }
        .already-reviewed { font-size: 13px; color: #555; padding: 12px 0; }
        .already-reviewed span { color: #5b80a8; }
        .guest-prompt { font-size: 13px; color: #555; }
        .guest-prompt a { color: #5b80a8; text-decoration: none; }
        .guest-prompt a:hover { text-decoration: underline; }
        .reviews-header { display: flex; align-items: baseline; gap: 10px; margin-bottom: 16px; }
        .reviews-title { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border-bottom: 2px solid #5b80a8; padding-bottom: 5px; }
        .reviews-count { font-size: 12px; color: #555; }
        .review-card { border: 1px solid #1c1c1c; border-radius: 12px; padding: 18px 20px; margin-bottom: 10px; background: #0f0f0f; transition: border-color 0.2s; }
        .review-card:hover { border-color: #2a2a2a; }
        .review-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .review-user { display: flex; align-items: center; gap: 8px; }
        .user-id { font-size: 13px; font-weight: 600; color: #ccc; }
        .user-role { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; background: #1a1a1a; border: 1px solid #2a2a2a; color: #5b80a8; padding: 2px 8px; border-radius: 8px; }
        .review-score { background: #5b80a8; color: #fff; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .review-text { font-size: 14px; line-height: 1.7; color: #888; }
        .no-reviews { text-align: center; color: #444; font-size: 14px; padding: 40px; border: 1px dashed #1c1c1c; border-radius: 12px; }
        .footer { text-align: center; padding: 20px; color: #333; font-size: 12px; border-top: 1px solid #1a1a1a; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM STUDIOS</a>
    <div class="nav-right">
        <div class="nav-divider"></div>
        <div class="nav-icons">
            <a href="index.php" title="Home">&#8962;</a>
            <a href="movies.php" class="active" title="Movies">&#9654;</a>
            <a href="reviews.php" title="Reviews">&#9733;</a>
            <a href="users.php" title="Users">&#128100;</a>
        </div>
        <div class="user-info">
            <span class="user-badge"><?php echo htmlspecialchars($_SESSION['user_type']); ?></span>
            <?php echo htmlspecialchars($_SESSION['user_id']); ?>
            <?php if ($is_guest): ?>
                <a href="register.php" class="create-acct-link">Create Account</a>
            <?php else: ?>
                <a href="logout.php" class="logout-link">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="page-content">
    <a href="movies.php" class="back-link">&larr; Back to movies</a>

    <div class="movie-header">
        <div class="poster-column">
            <div class="poster-wrap">
                <?php echo renderPosterImg($movie, 'page'); ?>
            </div>
        </div>
        <div class="header-details">
            <h1><?php echo htmlspecialchars($movie['Title']); ?></h1>
            <div class="movie-meta">
                <div class="meta-pill"><span>ID:</span> <?php echo htmlspecialchars($movie['MovieID']); ?></div>
                <?php if ($movie['ReleaseYear']): ?>
                    <div class="meta-pill"><span>Year:</span> <?php echo $movie['ReleaseYear']; ?></div>
                <?php endif; ?>
                <div class="meta-pill"><span>Streaming:</span> <?php echo htmlspecialchars($movie['StreamingService']); ?></div>
            </div>
            <div class="header-bottom">
                <div class="genre-tags">
                    <?php foreach (explode(", ", $movie['Genre']) as $g): ?>
                        <span class="genre-tag"><?php echo htmlspecialchars(trim($g)); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="score-wrap">
                    <?php if ($avg_score): ?>
                        <div class="user-score">&#9733; <?php echo $avg_score; ?> user avg</div>
                    <?php endif; ?>
                    <div class="big-score"><?php echo $movie['Rating']; ?>/10</div>
                </div>
            </div>
        </div>
    </div>

    <div class="movie-info">
        <?php if (!empty($movie['Description'])): ?>
        <div class="info-section">
            <div class="info-label">Synopsis</div>
            <div class="info-text"><?php echo htmlspecialchars($movie['Description']); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($actor_list)): ?>
        <div class="info-section">
            <div class="info-label">Cast</div>
            <div class="actor-list">
                <?php foreach ($actor_list as $actor): ?>
                    <span class="actor-tag"><?php echo htmlspecialchars($actor); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="review-form-box">
        <div class="review-form-title">Write a Review</div>
        <?php if ($review_success): ?>
            <div class="form-success">&#10003; Your review has been submitted!</div>
        <?php endif; ?>
        <?php if ($is_guest): ?>
            <p class="guest-prompt"><a href="register.php">Create an account</a> or <a href="index.php">sign in</a> to write a review.</p>
        <?php elseif ($user_already_reviewed): ?>
            <p class="already-reviewed">You've already reviewed this movie. <span>Thanks for your feedback!</span></p>
        <?php else: ?>
            <?php if ($review_error): ?>
                <div class="form-error"><?php echo htmlspecialchars($review_error); ?></div>
            <?php endif; ?>
            <form method="POST" action="movie.php?id=<?php echo urlencode($movie_id); ?>">
                <div class="score-row">
                    <label>Your Score</label>
                    <select name="score" class="score-select">
                        <?php for ($i = 10; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>/10</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <textarea name="review_text" class="review-textarea" placeholder="Share your thoughts about this film..."><?php echo isset($_POST['review_text']) ? htmlspecialchars($_POST['review_text']) : ''; ?></textarea>
                <button type="submit" name="submit_review" class="submit-btn">Submit Review</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="reviews-header">
        <div class="reviews-title">Reviews</div>
        <div class="reviews-count"><?php echo $review_count; ?> total</div>
    </div>

    <?php if ($review_count > 0): ?>
        <?php while ($review = $review_result->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-top">
                    <div class="review-user">
                        <div class="user-id"><?php echo htmlspecialchars($review['UserID']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($review['UserType']); ?></div>
                    </div>
                    <span class="review-score"><?php echo $review['Score']; ?>/10</span>
                </div>
                <div class="review-text"><?php echo htmlspecialchars($review['Text']); ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-reviews">No reviews yet. Be the first to review this film!</div>
    <?php endif; ?>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.querySelector('.poster-fallback[data-fetch]');
    if (!el) return;
    var id      = el.getAttribute('data-fetch');
    var API_KEY = '<?php echo OMDB_API_KEY; ?>';
    fetch('https://www.omdbapi.com/?i=' + encodeURIComponent(id) + '&apikey=' + API_KEY)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.Poster || data.Poster === 'N/A') return;
            var wrap = el.closest('.poster-wrap');
            if (!wrap) return;
            var img = document.createElement('img');
            img.className = 'poster-img';
            img.alt = '';
            img.src = data.Poster;
            wrap.insertBefore(img, el);
            el.style.display = 'none';
            fetch('cache_poster.php?id=' + encodeURIComponent(id) + '&url=' + encodeURIComponent(data.Poster));
        })
        .catch(function () {});
});
</script>
</body>
</html>
<?php mysqli_close($conn); ?>