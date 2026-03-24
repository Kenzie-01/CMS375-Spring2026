<?php

include __DIR__ . '/db_connect.php';

$sql = "SELECT Reviews.*, Movies.Title, Users.UserType
        FROM Reviews
        JOIN Movies ON Reviews.MovieID = Movies.MovieID
        JOIN Users ON Reviews.UserID = Users.UserID
        ORDER BY Reviews.Score DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - MTM Studios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0d0d0d; color: #f0f0f0; }
        .navbar { background-color: #1a1a2e; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e94560; }
        .navbar .logo { font-size: 24px; font-weight: bold; color: #e94560; text-decoration: none; }
        .navbar .nav-links a { color: #f0f0f0; text-decoration: none; margin-left: 25px; font-size: 16px; }
        .navbar .nav-links a:hover { color: #e94560; }
        .navbar .nav-links a.active { color: #e94560; border-bottom: 2px solid #e94560; padding-bottom: 3px; }
        .page-header { text-align: center; padding: 35px 20px; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        .page-header h1 { font-size: 30px; margin-bottom: 8px; }
        .page-header p { color: #aaa; font-size: 15px; }
        .reviews-container { max-width: 900px; margin: 25px auto; padding: 0 30px; }
        .review-card { background-color: #16213e; border-radius: 10px; padding: 20px; margin-bottom: 15px; border: 1px solid #1a1a3e; }
        .review-card .movie-title { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
        .review-card .movie-title a { color: #e94560; text-decoration: none; }
        .review-card .movie-title a:hover { text-decoration: underline; }
        .review-card .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .review-card .user-info { font-size: 13px; color: #aaa; }
        .review-card .role { background-color: rgba(233, 69, 96, 0.2); color: #e94560; padding: 2px 8px; border-radius: 8px; font-size: 11px; margin-left: 8px; }
        .review-card .review-score { background-color: #e94560; color: white; padding: 3px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; }
        .review-card .review-text { font-size: 14px; line-height: 1.6; color: #ccc; }
        .footer { text-align: center; padding: 20px; color: #555; font-size: 13px; border-top: 1px solid #1a1a2e; margin-top: 30px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM Studios</a>
    <div class="nav-links">
        <a href="index.php">Browse Movies</a>
        <a href="reviews.php" class="active">Reviews</a>
        <a href="users.php">Users</a>
    </div>
</nav>

<div class="page-header">
    <h1>All Reviews</h1>
    <p><?php echo mysqli_num_rows($result); ?> reviews from our community</p>
</div>

<div class="reviews-container">
    <?php while ($review = mysqli_fetch_assoc($result)): ?>
        <div class="review-card">
            <div class="movie-title">
                <a href="movie.php?id=<?php echo urlencode($review['MovieID']); ?>">
                    <?php echo htmlspecialchars($review['Title']); ?>
                </a>
            </div>
            <div class="review-header">
                <div class="user-info">
                    <?php echo htmlspecialchars($review['UserID']); ?>
                    <span class="role"><?php echo htmlspecialchars($review['UserType']); ?></span>
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
