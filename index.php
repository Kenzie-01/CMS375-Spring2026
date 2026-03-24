<?php

include __DIR__ . '/db_connect.php';


$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$genre_filter = "";

if (isset($_GET['genre']) && $_GET['genre'] != "") {
    $genre_filter = mysqli_real_escape_string($conn, $_GET['genre']);
}


$service_filter = "";
if (isset($_GET['service']) && $_GET['service'] != "") {
    $service_filter = mysqli_real_escape_string($conn, $_GET['service']);
}


$sql = "SELECT * FROM Movies WHERE 1=1";

if ($search != "") {
    $sql .= " AND Title LIKE '%$search%'";
}
if ($genre_filter != "") {
    $sql .= " AND Genre LIKE '%$genre_filter%'";
}
if ($service_filter != "") {
    $sql .= " AND StreamingServices LIKE '%$service_filter%'";
}

$sql .= " ORDER BY Rating DESC";

$result = mysqli_query($conn, $sql);

$genre_sql = "SELECT DISTINCT Genre FROM Movies";
$genre_result = mysqli_query($conn, $genre_sql);
$all_genres = [];
while ($row = mysqli_fetch_assoc($genre_result)) {
    $genres = explode(", ", $row['Genre']);
    foreach ($genres as $g) {
        $g = trim($g);
        if (!in_array($g, $all_genres)) {
            $all_genres[] = $g;
        }
    }
}
sort($all_genres);

$service_sql = "SELECT DISTINCT StreamingServices FROM Movies";
$service_result = mysqli_query($conn, $service_sql);
$all_services = [];
while ($row = mysqli_fetch_assoc($service_result)) {
    $services = explode(", ", $row['StreamingServices']);
    foreach ($services as $s) {
        $s = trim($s);
        if (!in_array($s, $all_services)) {
            $all_services[] = $s;
        }
    }
}
sort($all_services);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTM Studios - All-In-One Movies</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0d0d0d;
            color: #f0f0f0;
        }

        .navbar {
            background-color: #1a1a2e;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e94560;
        }
        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
            color: #e94560;
            text-decoration: none;
        }
        .navbar .nav-links a {
            color: #f0f0f0;
            text-decoration: none;
            margin-left: 25px;
            font-size: 16px;
        }
        .navbar .nav-links a:hover { color: #e94560; }
        .navbar .nav-links a.active {
            color: #e94560;
            border-bottom: 2px solid #e94560;
            padding-bottom: 3px;
        }

        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .hero h1 { font-size: 36px; margin-bottom: 10px; }
        .hero p { color: #aaa; font-size: 16px; margin-bottom: 25px; }

        .search-bar {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            max-width: 900px;
            margin: 0 auto;
        }
        .search-bar input[type="text"] {
            padding: 12px 20px;
            font-size: 16px;
            border: 1px solid #333;
            border-radius: 8px;
            background-color: #1a1a2e;
            color: #f0f0f0;
            width: 300px;
        }
        .search-bar select {
            padding: 12px 15px;
            font-size: 14px;
            border: 1px solid #333;
            border-radius: 8px;
            background-color: #1a1a2e;
            color: #f0f0f0;
        }
        .search-bar button {
            padding: 12px 25px;
            font-size: 16px;
            background-color: #e94560;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .search-bar button:hover { background-color: #c73550; }

        .stats {
            text-align: center;
            padding: 15px;
            color: #aaa;
            font-size: 14px;
        }
        .stats a { color: #e94560; }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px 30px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .movie-card {
            background-color: #16213e;
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #1a1a3e;
        }
        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.2);
            border-color: #e94560;
        }
        .movie-card h3 { font-size: 18px; margin-bottom: 12px; color: #fff; }
        .movie-card h3 a { color: #fff; text-decoration: none; }
        .movie-card h3 a:hover { color: #e94560; }
        .movie-card .info { font-size: 13px; color: #aaa; margin-bottom: 6px; }
        .movie-card .info span { color: #e94560; font-weight: bold; }
        .movie-card .score {
            display: inline-block;
            background-color: #e94560;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        .movie-card .genre-tags { margin-top: 10px; }
        .movie-card .genre-tag {
            display: inline-block;
            background-color: rgba(233, 69, 96, 0.15);
            color: #e94560;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px 3px 2px 0;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 18px;
            grid-column: 1 / -1;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #555;
            font-size: 13px;
            border-top: 1px solid #1a1a2e;
            margin-top: 20px;
        }
    </style>
</head>
<body>


<nav class="navbar">
    <a href="index.php" class="logo">MTM Studios</a>
    <div class="nav-links">
        <a href="index.php" class="active">Browse Movies</a>
        <a href="reviews.php">Reviews</a>
        <a href="users.php">Users</a>
    </div>
</nav>


<div class="hero">
    <h1>All-In-One Movies & TV Platform</h1>
    <p>Browse, search, and discover movies across all streaming services</p>


    <form method="GET" action="index.php" class="search-bar">
        <input type="text" name="search" placeholder="Search movies by title..."
               value="<?php echo htmlspecialchars($search); ?>">

        <select name="genre">
            <option value="">All Genres</option>
            <?php foreach ($all_genres as $genre): ?>
                <option value="<?php echo $genre; ?>"
                    <?php if ($genre_filter == $genre) echo 'selected'; ?>>
                    <?php echo $genre; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="service">
            <option value="">All Services</option>
            <?php foreach ($all_services as $service): ?>
                <option value="<?php echo $service; ?>"
                    <?php if ($service_filter == $service) echo 'selected'; ?>>
                    <?php echo $service; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
    </form>
</div>

<div class="stats">
    <?php echo mysqli_num_rows($result); ?> movie(s) found
    <?php if ($search != "" || $genre_filter != "" || $service_filter != "") : ?>
        — <a href="index.php">Clear filters</a>
    <?php endif; ?>
</div>


<div class="movie-grid">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($movie = mysqli_fetch_assoc($result)): ?>
            <div class="movie-card">
                <h3>
                    <a href="movie.php?id=<?php echo urlencode($movie['MovieID']); ?>">
                        <?php echo htmlspecialchars($movie['Title']); ?>
                    </a>
                </h3>
                <div class="info">
                    <span>Streaming on:</span> <?php echo htmlspecialchars($movie['StreamingServices']); ?>
                </div>
                <div class="info">
                    <span>ID:</span> <?php echo htmlspecialchars($movie['MovieID']); ?>
                </div>
                <div class="genre-tags">
                    <?php
                    $genres = explode(", ", $movie['Genre']);
                    foreach ($genres as $g):
                    ?>
                        <span class="genre-tag"><?php echo trim($g); ?></span>
                    <?php endforeach; ?>
                </div>
                <span class="score"><?php echo $movie['Rating']; ?>/10</span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-results">No movies found. Try a different search.</div>
    <?php endif; ?>
</div>


<div class="footer">
    MTM Studios &copy; 2026 | CMS 375 Database Project
</div>

</body>
</html>

<?php mysqli_close($conn); ?>
