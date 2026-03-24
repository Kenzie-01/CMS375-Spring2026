<?php
include __DIR__ . '/db_connect.php';

$sql = "SELECT Reviews.*, Movies.Title, Users.UserType
        FROM Reviews
        JOIN Movies ON Reviews.MovieID = Movies.MovieID
        JOIN Users ON Reviews.UserID = Users.UserID
        ORDER BY Reviews.Score DESC";
$result = mysqli_query($conn, $sql);
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - MTM Studios</title>
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

        /* ---- PAGE HEADER ---- */
        .page-header {
            padding: 36px 30px 24px;
            border-bottom: 1px solid #1a1a1a;
        }

        .page-header-inner {
            display: flex;
            align-items: baseline;
            gap: 14px;
        }

        .page-title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #ffffff;
            border-bottom: 2px solid #5b80a8;
            padding-bottom: 5px;
        }

        .page-count { font-size: 12px; color: #555; }

        /* ---- REVIEWS LIST ---- */
        .reviews-container {
            max-width: 860px;
            margin: 0 auto;
            padding: 28px 30px 60px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .review-card {
            border: 1px solid #1c1c1c;
            border-radius: 12px;
            padding: 18px 20px;
            background: #0f0f0f;
            transition: border-color 0.2s;
        }

        .review-card:hover { border-color: #2a2a2a; }

        .movie-link {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
            transition: color 0.2s;
        }

        .movie-link:hover { color: #5b80a8; }

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
            <a href="movies.php" title="Movies">&#9654;</a>
            <a href="reviews.php" class="active" title="Reviews">&#9733;</a>
            <a href="users.php" title="Users">&#128100;</a>
        </div>
    </div>
</nav>

<div class="page-header">
    <div class="page-header-inner">
        <div class="page-title">Reviews</div>
        <div class="page-count"><?php echo $total; ?> total</div>
    </div>
</div>

<div class="reviews-container">
    <?php while ($review = mysqli_fetch_assoc($result)): ?>
        <div class="review-card">
            <a href="movie.php?id=<?php echo urlencode($review['MovieID']); ?>" class="movie-link">
                <?php echo htmlspecialchars($review['Title']); ?>
            </a>
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
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>
