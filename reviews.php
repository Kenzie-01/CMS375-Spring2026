<?php
session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

$is_guest = ($_SESSION['user_id'] === 'Guest');

$score_filter = isset($_GET['score'])    ? intval($_GET['score'])                                : 0;
$role_filter  = isset($_GET['role'])     ? mysqli_real_escape_string($conn, $_GET['role'])       : '';
$sort         = (isset($_GET['sort']) && in_array($_GET['sort'], ['score_desc','score_asc','movie']))
                ? $_GET['sort'] : 'score_desc';

$sql = "SELECT Reviews.*, Users.UserType, Movies.Title AS MovieTitle, Movies.Genre
        FROM Reviews
        JOIN Users  ON Reviews.UserID  = Users.UserID
        JOIN Movies ON Reviews.MovieID = Movies.MovieID
        WHERE 1=1";

if ($score_filter > 0) $sql .= " AND Reviews.Score = $score_filter";
if ($role_filter != "") $sql .= " AND Users.UserType = '$role_filter'";

switch ($sort) {
    case 'score_asc': $sql .= " ORDER BY Reviews.Score ASC";    break;
    case 'movie':     $sql .= " ORDER BY Movies.Title ASC";     break;
    default:          $sql .= " ORDER BY Reviews.Score DESC";
}

$result = mysqli_query($conn, $sql);
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - MTM Studios</title>
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

        .page-header { padding: 28px 30px 16px; border-bottom: 1px solid #1a1a1a; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
        .page-header-left { display: flex; align-items: baseline; gap: 14px; }
        .page-title { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border-bottom: 2px solid #5b80a8; padding-bottom: 5px; }
        .page-count { font-size: 12px; color: #555; }

        .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; padding: 16px 30px; border-bottom: 1px solid #111; }
        .filter-select { padding: 7px 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 13px; cursor: pointer; }
        .filter-select:focus { outline: none; border-color: #5b80a8; }
        .filter-select option { background: #111; }
        .clear-btn { padding: 7px 14px; background: transparent; color: #ff5050; border: 1px solid #ff5050; border-radius: 8px; font-size: 13px; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .clear-btn:hover { background: rgba(255,80,80,0.1); }

        .reviews-container { padding: 20px 30px 60px; }

        .review-card { border: 1px solid #1c1c1c; border-radius: 12px; padding: 18px 20px; margin-bottom: 10px; background: #0f0f0f; transition: border-color 0.2s; }
        .review-card:hover { border-color: #2a2a2a; }
        .review-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; gap: 12px; flex-wrap: wrap; }
        .review-left { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .user-id { font-size: 13px; font-weight: 600; color: #ccc; }
        .user-role { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; background: #1a1a1a; border: 1px solid #2a2a2a; color: #5b80a8; padding: 2px 8px; border-radius: 8px; }
        .review-movie { font-size: 12px; color: #555; }
        .review-movie a { color: #5b80a8; text-decoration: none; }
        .review-movie a:hover { text-decoration: underline; }
        .review-score { background: #5b80a8; color: #fff; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; white-space: nowrap; flex-shrink: 0; }
        .review-text { font-size: 14px; line-height: 1.7; color: #888; }

        .empty-state { text-align: center; color: #444; font-size: 14px; padding: 60px; }
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
            <a href="movies.php" title="Movies">&#9654;</a>
            <a href="reviews.php" class="active" title="Reviews">&#9733;</a>
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

<div class="page-header">
    <div class="page-header-left">
        <div class="page-title">Reviews</div>
        <div class="page-count"><?php echo $total; ?> total</div>
    </div>
</div>

<form method="GET" action="reviews.php">
    <div class="filter-bar">
        <select name="score" class="filter-select" onchange="this.form.submit()">
            <option value="">All Scores</option>
            <?php for ($i = 10; $i >= 1; $i--): ?>
                <option value="<?php echo $i; ?>" <?php echo ($score_filter == $i) ? 'selected' : ''; ?>>
                    <?php echo $i; ?>/10
                </option>
            <?php endfor; ?>
        </select>

        <select name="role" class="filter-select" onchange="this.form.submit()">
            <option value="">All User Types</option>
            <option value="Admin"   <?php echo ($role_filter == 'Admin')   ? 'selected' : ''; ?>>Admin</option>
            <option value="Critic"  <?php echo ($role_filter == 'Critic')  ? 'selected' : ''; ?>>Critic</option>
            <option value="Regular" <?php echo ($role_filter == 'Regular') ? 'selected' : ''; ?>>Regular</option>
        </select>

        <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="score_desc" <?php echo ($sort == 'score_desc') ? 'selected' : ''; ?>>Highest Score</option>
            <option value="score_asc"  <?php echo ($sort == 'score_asc')  ? 'selected' : ''; ?>>Lowest Score</option>
            <option value="movie"      <?php echo ($sort == 'movie')      ? 'selected' : ''; ?>>By Movie (A–Z)</option>
        </select>

        <?php if ($score_filter > 0 || $role_filter != ''): ?>
            <a href="reviews.php" class="clear-btn">&#10005; Clear Filters</a>
        <?php endif; ?>
    </div>
</form>

<div class="reviews-container">
    <?php if ($total == 0): ?>
        <div class="empty-state">No reviews match the selected filters.</div>
    <?php else: ?>
        <?php while ($review = mysqli_fetch_assoc($result)): ?>
            <div class="review-card">
                <div class="review-top">
                    <div>
                        <div class="review-left">
                            <span class="user-id"><?php echo htmlspecialchars($review['UserID']); ?></span>
                            <span class="user-role"><?php echo htmlspecialchars($review['UserType']); ?></span>
                        </div>
                        <div class="review-movie" style="margin-top:5px;">
                            on <a href="movie.php?id=<?php echo urlencode($review['MovieID']); ?>">
                                <?php echo htmlspecialchars($review['MovieTitle']); ?>
                            </a>
                            <span style="color:#333;">&bull;</span>
                            <?php echo htmlspecialchars($review['Genre']); ?>
                        </div>
                    </div>
                    <span class="review-score"><?php echo $review['Score']; ?>/10</span>
                </div>
                <div class="review-text"><?php echo htmlspecialchars($review['Text']); ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>