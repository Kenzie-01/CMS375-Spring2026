<?php
include __DIR__ . '/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: movies.php");
    exit();
}

$movie_id = mysqli_real_escape_string($conn, $_GET['id']);

$sql = "SELECT * FROM Movies WHERE MovieID = '$movie_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "Movie not found.";
    exit();
}

$movie = mysqli_fetch_assoc($result);

$review_sql = "SELECT Reviews.*, Users.UserID, Users.UserType
               FROM Reviews
               JOIN Users ON Reviews.UserID = Users.UserID
               WHERE Reviews.MovieID = '$movie_id'
               ORDER BY Reviews.Score DESC";
$review_result = mysqli_query($conn, $review_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['Title']); ?> - MTM Studios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
        }

        .navbar {
            background-color: #0a0a0a;
            border-bottom: 2px solid #ffffff;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #5b80a8;
            text-decoration: none;
            letter-spacing: 2px;
        }

        .nav-right { display: flex; align-items: center; }

        .nav-divider {
            width: 2px;
            height: 28px;
            background-color: #ffffff;
            margin: 0 16px;
        }

        .nav-icons { display: flex; align-items: center; gap: 16px; }

        .nav-icons a {
            color: #aaa;
            text-decoration: none;
            font-size: 22px;
            padding: 6px 10px;
            border: 1px solid transparent;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-icons a:hover,
        .nav-icons a.active {
            color: #fff;
            border-color: #fff;
            background-color: #1a1a1a;
        }

        /* ---- CONTENT ---- */
        .page-content {
            max-width: 860px;
            margin: 0 auto;
            padding: 32px 30px 60px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #5b80a8;
            text-decoration: none;
            font-size: 13px;
            margin-bottom: 24px;
            transition: color 0.2s;
        }

        .back-link:hover { color: #fff; }

        /* ---- MOVIE HEADER ---- */
        .movie-header {
            border: 2px solid #ffffff;
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 30px;
        }

        .movie-header h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .movie-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .meta-pill {
            font-size: 12px;
            background: #111;
            border: 1px solid #2a2a2a;
            border-radius: 20px;
            padding: 4px 14px;
            color: #aaa;
        }

        .meta-pill span { color: #ffffff; font-weight: 600; }

        .header-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .genre-tags { display: flex; flex-wrap: wrap; gap: 8px; }

        .genre-tag {
            font-size: 12px;
            border: 1px solid #5b80a8;
            color: #5b80a8;
            padding: 3px 12px;
            border-radius: 20px;
        }

        .big-score {
            background: #5b80a8;
            color: #fff;
            padding: 6px 20px;
            border-radius: 24px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        /* ---- REVIEWS ---- */
        .reviews-header {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 16px;
        }

        .reviews-title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #ffffff;
            border-bottom: 2px solid #5b80a8;
            padding-bottom: 5px;
        }

        .reviews-count {
            font-size: 12px;
            color: #555;
        }

        .review-card {
            border: 1px solid #1c1c1c;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 10px;
            background: #0f0f0f;
            transition: border-color 0.2s;
        }

        .review-card:hover { border-color: #2a2a2a; }

        .review-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-id {
            font-size: 13px;
            font-weight: 600;
            color: #ccc;
        }

        .user-role {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            color: #5b80a8;
            padding: 2px 8px;
            border-radius: 8px;
        }

        .review-score {
            background: #5b80a8;
            color: #fff;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .review-text {
            font-size: 14px;
            line-height: 1.7;
            color: #888;
        }

        .no-reviews {
            text-align: center;
            color: #444;
            font-size: 14px;
            padding: 40px;
            border: 1px dashed #1c1c1c;
            border-radius: 12px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #333;
            font-size: 12px;
            border-top: 1px solid #1a1a1a;
        }
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
    </div>
</nav>

<div class="page-content">
    <a href="movies.php" class="back-link">&larr; Back to movies</a>

    <div class="movie-header">
        <h1><?php echo htmlspecialchars($movie['Title']); ?></h1>
        <div class="movie-meta">
            <div class="meta-pill"><span>ID:</span> <?php echo htmlspecialchars($movie['MovieID']); ?></div>
            <div class="meta-pill"><span>Streaming on:</span> <?php echo htmlspecialchars($movie['StreamingServices']); ?></div>
        </div>
        <div class="header-bottom">
            <div class="genre-tags">
                <?php $genres = explode(", ", $movie['Genre']); foreach ($genres as $g): ?>
                    <span class="genre-tag"><?php echo trim($g); ?></span>
                <?php endforeach; ?>
            </div>
            <div class="big-score"><?php echo $movie['Rating']; ?>/10</div>
        </div>
    </div>

    <div class="reviews-header">
        <div class="reviews-title">Reviews</div>
        <div class="reviews-count"><?php echo mysqli_num_rows($review_result); ?> total</div>
    </div>

    <?php if (mysqli_num_rows($review_result) > 0): ?>
        <?php while ($review = mysqli_fetch_assoc($review_result)): ?>
            <div class="review-card">
                <div class="review-top">
                    <div class="user-info">
                        <div class="user-id"><?php echo htmlspecialchars($review['UserID']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($review['UserType']); ?></div>
                    </div>
                    <span class="review-score"><?php echo $review['Score']; ?>/10</span>
                </div>
                <div class="review-text"><?php echo htmlspecialchars($review['Text']); ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-reviews">No reviews yet for this movie.</div>
    <?php endif; ?>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>
