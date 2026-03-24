<?php
include __DIR__ . '/db_connect.php';

$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$sql = "SELECT * FROM Movies WHERE 1=1";
if ($search != "") {
    $sql .= " AND Title LIKE '%$search%'";
}
$sql .= " ORDER BY Rating DESC";

$result = mysqli_query($conn, $sql);

$movies_by_genre = [];
while ($movie = mysqli_fetch_assoc($result)) {
    $genres     = explode(", ", $movie['Genre']);
    $firstGenre = trim($genres[0]);
    $movies_by_genre[$firstGenre][] = $movie;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies - MTM Studios</title>
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

        .genre-section { padding: 24px 30px 12px; }

        .genre-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .genre-label {
            font-size: 15px;
            font-weight: bold;
            color: #ffffff;
            border-bottom: 2px solid #5b80a8;
            display: inline-block;
            padding-bottom: 5px;
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-form span { font-size: 16px; color: #aaa; }

        .search-form input {
            padding: 7px 14px;
            background: #111;
            border: 1px solid #ffffff;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            width: 240px;
        }

        .search-form input:focus { outline: none; border-color: #5b80a8; }
        .search-form input::placeholder { color: #555; }

        .search-form button {
            padding: 7px 16px;
            background: #5b80a8;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-form button:hover { background: #4a6a90; }

        .clear-link {
            color: #5b80a8;
            text-decoration: none;
            font-size: 13px;
            white-space: nowrap;
        }

        .movie-row {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .movie-row::-webkit-scrollbar { height: 4px; }
        .movie-row::-webkit-scrollbar-track { background: #111; }
        .movie-row::-webkit-scrollbar-thumb { background: #5b80a8; border-radius: 4px; }

        .movie-card {
            min-width: 240px;
            max-width: 240px;
            height: 160px;
            background-color: #0a0a0a;
            border: 2px solid #ffffff;
            border-radius: 14px;
            padding: 18px 16px;
            text-decoration: none;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.2s, transform 0.2s;
            flex-shrink: 0;
        }

        .movie-card:hover {
            border-color: #5b80a8;
            transform: translateY(-4px);
        }

        .card-title {
            font-size: 14px;
            font-weight: bold;
            line-height: 1.35;
        }

        .card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .card-service {
            font-size: 11px;
            color: #777;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-score {
            display: inline-block;
            background: #5b80a8;
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #333;
            font-size: 12px;
            border-top: 1px solid #1a1a1a;
            margin-top: 20px;
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

<?php if (empty($movies_by_genre)): ?>
    <p style="text-align:center; color:#555; padding:60px;">No movies found.</p>
<?php endif; ?>

<?php $firstGenre = true; ?>
<?php foreach ($movies_by_genre as $genre => $movies): ?>
    <div class="genre-section">
        <div class="genre-header">
            <div class="genre-label"><?php echo htmlspecialchars($genre); ?></div>
            <?php if ($firstGenre): ?>
                <form class="search-form" method="GET" action="movies.php">
                    <span>&#128269;</span>
                    <input type="text" name="search"
                           placeholder="Search movies..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                    <?php if ($search != ""): ?>
                        <a href="movies.php" class="clear-link">&#10005; Clear</a>
                    <?php endif; ?>
                </form>
                <?php $firstGenre = false; ?>
            <?php endif; ?>
        </div>
        <div class="movie-row">
            <?php foreach ($movies as $movie): ?>
                <a href="movie.php?id=<?php echo urlencode($movie['MovieID']); ?>" class="movie-card">
                    <div class="card-title"><?php echo htmlspecialchars($movie['Title']); ?></div>
                    <div class="card-bottom">
                        <div class="card-service"><?php echo htmlspecialchars($movie['StreamingServices']); ?></div>
                        <span class="card-score"><?php echo $movie['Rating']; ?>/10</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>
