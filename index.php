<?php
include __DIR__ . '/db_connect.php';

$stats_sql = "SELECT
    (SELECT COUNT(*) FROM Movies) AS total_movies,
    (SELECT COUNT(*) FROM Reviews) AS total_reviews,
    (SELECT COUNT(*) FROM Users) AS total_users,
    (SELECT AVG(Rating) FROM Movies) AS avg_rating";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

$top_sql = "SELECT * FROM Movies ORDER BY Rating DESC LIMIT 5";
$top_result = mysqli_query($conn, $top_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTM Studios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
        }

        /* ---- NAVBAR ---- */
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

        /* ---- HERO ---- */
        .hero {
            padding: 80px 30px 60px;
            text-align: center;
            border-bottom: 1px solid #1a1a1a;
        }

        .hero-eyebrow {
            font-size: 11px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #5b80a8;
            margin-bottom: 18px;
        }

        .hero h1 {
            font-size: 56px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #ffffff;
            line-height: 1.1;
            margin-bottom: 18px;
        }

        .hero h1 span { color: #5b80a8; }

        .hero p {
            font-size: 15px;
            color: #666;
            max-width: 460px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 11px 28px;
            background: #5b80a8;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-primary:hover { background: #4a6a90; transform: translateY(-2px); }

        .btn-outline {
            padding: 11px 28px;
            background: transparent;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: 2px solid #ffffff;
            letter-spacing: 0.5px;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-outline:hover { background: #1a1a1a; transform: translateY(-2px); }

        /* ---- STATS ---- */
        .stats-section {
            display: flex;
            justify-content: center;
            gap: 0;
            border-bottom: 1px solid #1a1a1a;
        }

        .stat-block {
            flex: 1;
            max-width: 240px;
            padding: 36px 20px;
            text-align: center;
            border-right: 1px solid #1a1a1a;
        }

        .stat-block:last-child { border-right: none; }

        .stat-number {
            font-size: 40px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -1px;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-number span { color: #5b80a8; }

        .stat-label {
            font-size: 11px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* ---- TOP RATED ---- */
        .section {
            padding: 40px 30px;
        }

        .section-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #ffffff;
            border-bottom: 2px solid #5b80a8;
            padding-bottom: 5px;
        }

        .section-link {
            font-size: 12px;
            color: #5b80a8;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .section-link:hover { text-decoration: underline; }

        .top-list {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .top-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #0f0f0f;
            border: 1px solid #1c1c1c;
            border-radius: 10px;
            text-decoration: none;
            color: #ffffff;
            transition: border-color 0.2s, background 0.2s;
            gap: 16px;
        }

        .top-item:hover {
            border-color: #5b80a8;
            background: #111;
        }

        .top-item-left {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .top-rank {
            font-size: 11px;
            color: #444;
            font-weight: 700;
            width: 20px;
            text-align: right;
            flex-shrink: 0;
        }

        .top-title {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .top-genre {
            font-size: 11px;
            color: #555;
            white-space: nowrap;
        }

        .top-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .top-service {
            font-size: 11px;
            color: #555;
        }

        .top-score {
            background: #5b80a8;
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        /* ---- FOOTER ---- */
        .footer {
            text-align: center;
            padding: 20px;
            color: #333;
            font-size: 12px;
            border-top: 1px solid #1a1a1a;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM STUDIOS</a>
    <div class="nav-right">
        <div class="nav-divider"></div>
        <div class="nav-icons">
            <a href="index.php" class="active" title="Home">&#8962;</a>
            <a href="movies.php" title="Movies">&#9654;</a>
            <a href="reviews.php" title="Reviews">&#9733;</a>
            <a href="users.php" title="Users">&#128100;</a>
        </div>
    </div>
</nav>

<div class="hero">
    <div class="hero-eyebrow">Movie Tracking &amp; Management</div>
    <h1>MTM<br><span>STUDIOS</span></h1>
    <p>Browse films, read critic and community reviews, and track what's streaming — all in one place.</p>
    <div class="hero-actions">
        <a href="movies.php" class="btn-primary">&#9654; &nbsp;Browse Movies</a>
        <a href="reviews.php" class="btn-outline">&#9733; &nbsp;All Reviews</a>
    </div>
</div>

<div class="stats-section">
    <div class="stat-block">
        <div class="stat-number"><?php echo $stats['total_movies']; ?></div>
        <div class="stat-label">Movies</div>
    </div>
    <div class="stat-block">
        <div class="stat-number"><?php echo $stats['total_reviews']; ?></div>
        <div class="stat-label">Reviews</div>
    </div>
    <div class="stat-block">
        <div class="stat-number"><?php echo $stats['total_users']; ?></div>
        <div class="stat-label">Users</div>
    </div>
    <div class="stat-block">
        <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?><span>/10</span></div>
        <div class="stat-label">Avg Rating</div>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <div class="section-title">Top Rated</div>
        <a href="movies.php" class="section-link">View All &rsaquo;</a>
    </div>
    <div class="top-list">
        <?php $rank = 1; while ($movie = mysqli_fetch_assoc($top_result)): ?>
            <a href="movie.php?id=<?php echo urlencode($movie['MovieID']); ?>" class="top-item">
                <div class="top-item-left">
                    <div class="top-rank">#<?php echo $rank++; ?></div>
                    <div>
                        <div class="top-title"><?php echo htmlspecialchars($movie['Title']); ?></div>
                        <div class="top-genre"><?php echo htmlspecialchars($movie['Genre']); ?></div>
                    </div>
                </div>
                <div class="top-right">
                    <div class="top-service"><?php echo htmlspecialchars($movie['StreamingServices']); ?></div>
                    <span class="top-score"><?php echo $movie['Rating']; ?>/10</span>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>

</body>
</html>
<?php mysqli_close($conn); ?>
