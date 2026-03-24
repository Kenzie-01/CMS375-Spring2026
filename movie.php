<?php

include __DIR__ . '/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0d0d0d; color: #f0f0f0; }
        .navbar { background-color: #1a1a2e; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e94560; }
        .navbar .logo { font-size: 24px; font-weight: bold; color: #e94560; text-decoration: none; }
        .navbar .nav-links a { color: #f0f0f0; text-decoration: none; margin-left: 25px; font-size: 16px; }
        .navbar .nav-links a:hover { color: #e94560; }
        .movie-detail { max-width: 900px; margin: 40px auto; padding: 0 30px; }
        .back-link { color: #e94560; text-decoration: none; font-size: 14px; display: inline-block; margin-bottom: 20px; }
        .back-link:hover { text-decoration: underline; }
        .movie-header { background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 12px; padding: 30px; border: 1px solid #1a1a3e; }
        .movie-header h1 { font-size: 32px; margin-bottom: 15px; }
        .movie-meta { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 15px; }
        .meta-item { font-size: 14px; color: #aaa; }
        .meta-item span { color: #e94560; font-weight: bold; }
        .movie-header .score { display: inline-block; background-color: #e94560; color: white; padding: 6px 16px; border-radius: 20px; font-size: 18px; font-weight: bold; }
        .genre-tags { margin-top: 15px; }
        .genre-tag { display: inline-block; background-color: rgba(233, 69, 96, 0.15); color: #e94560; padding: 4px 12px; border-radius: 12px; font-size: 13px; margin: 3px 4px 3px 0; }
        .reviews-section { margin-top: 35px; }
        .reviews-section h2 { font-size: 22px; margin-bottom: 20px; color: #e94560; }
        .review-card { background-color: #16213e; border-radius: 10px; padding: 20px; margin-bottom: 15px; border: 1px solid #1a1a3e; }
        .review-card .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .review-card .user-info { font-size: 14px; color: #aaa; }
        .review-card .user-info .role { background-color: rgba(233, 69, 96, 0.2); color: #e94560; padding: 2px 8px; border-radius: 8px; font-size: 11px; margin-left: 8px; }
        .review-card .review-score { background-color: #e94560; color: white; padding: 3px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; }
        .review-card .review-text { font-size: 15px; line-height: 1.6; color: #ccc; }
        .no-reviews { color: #666; font-size: 16px; text-align: center; padding: 30px; }
        .footer { text-align: center; padding: 20px; color: #555; font-size: 13px; border-top: 1px solid #1a1a2e; margin-top: 40px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM Studios</a>
    <div class="nav-links">
        <a href="index.php">Browse Movies</a>
        <a href="reviews.php">Reviews</a>
        <a href="users.php">Users</a>
    </div>
</nav>

<div class="movie-detail">
    <a href="index.php" class="back-link">&larr; Back to all movies</a>
    <div class="movie-header">
        <h1><?php echo htmlspecialchars($movie['Title']); ?></h1>
        <div class="movie-meta">
            <div class="meta-item"><span>Movie ID:</span> <?php echo htmlspecialchars($movie['MovieID']); ?></div>
            <div class="meta-item"><span>Streaming on:</span> <?php echo htmlspecialchars($movie['StreamingServices']); ?></div>
        </div>
        <span class="score"><?php echo $movie['Rating']; ?>/10</span>
        <div class="genre-tags">
            <?php $genres = explode(", ", $movie['Genre']); foreach ($genres as $g): ?>
                <span class="genre-tag"><?php echo trim($g); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="reviews-section">
        <h2>Reviews (<?php echo mysqli_num_rows($review_result); ?>)</h2>
        <?php if (mysqli_num_rows($review_result) > 0): ?>
            <?php while ($review = mysqli_fetch_assoc($review_result)): ?>
                <div class="review-card">
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
        <?php else: ?>
            <div class="no-reviews">No reviews yet for this movie.</div>
        <?php endif; ?>
    </div>
</div>
<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>
