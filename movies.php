<?php
session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

$is_guest = ($_SESSION['user_id'] === 'Guest');

// --- Collect filter inputs ---
$search           = isset($_GET['search'])    ? mysqli_real_escape_string($conn, trim($_GET['search']))    : '';
$genre_filter     = isset($_GET['genre'])     ? mysqli_real_escape_string($conn, $_GET['genre'])           : '';
$year_filter      = isset($_GET['year'])      ? intval($_GET['year'])                                      : 0;
$streaming_filter = isset($_GET['streaming']) ? mysqli_real_escape_string($conn, $_GET['streaming'])       : '';
$sort             = (isset($_GET['sort']) && in_array($_GET['sort'], ['rating','year_desc','year_asc','title']))
                    ? $_GET['sort'] : 'rating';

$sql = "SELECT * FROM Movies WHERE 1=1";
if ($search != "")           $sql .= " AND (Title LIKE '%$search%' OR Actors LIKE '%$search%' OR Description LIKE '%$search%')";
if ($genre_filter != "")     $sql .= " AND Genre = '$genre_filter'";
if ($year_filter > 0)        $sql .= " AND ReleaseYear = $year_filter";
if ($streaming_filter != "") $sql .= " AND StreamingServices LIKE '%$streaming_filter%'";

switch ($sort) {
    case 'year_desc': $sql .= " ORDER BY ReleaseYear DESC";  break;
    case 'year_asc':  $sql .= " ORDER BY ReleaseYear ASC";   break;
    case 'title':     $sql .= " ORDER BY Title ASC";         break;
    default:          $sql .= " ORDER BY Rating DESC";
}

$result     = mysqli_query($conn, $sql);
$all_movies = [];
while ($m = mysqli_fetch_assoc($result)) $all_movies[] = $m;

// --- Options for filter dropdowns ---
$genre_rows  = mysqli_query($conn, "SELECT DISTINCT Genre FROM Movies ORDER BY Genre");
$all_genres  = [];
while ($g = mysqli_fetch_assoc($genre_rows)) $all_genres[] = $g['Genre'];

$year_rows  = mysqli_query($conn, "SELECT DISTINCT ReleaseYear FROM Movies WHERE ReleaseYear IS NOT NULL ORDER BY ReleaseYear DESC");
$all_years  = [];
while ($y = mysqli_fetch_assoc($year_rows)) $all_years[] = $y['ReleaseYear'];

$stream_rows   = mysqli_query($conn, "SELECT DISTINCT StreamingServices FROM Movies ORDER BY StreamingServices");
$all_streaming = [];
while ($s = mysqli_fetch_assoc($stream_rows)) $all_streaming[] = $s['StreamingServices'];

// --- Group by genre for default (unfiltered) view ---
$is_filtered    = ($search != '' || $genre_filter != '' || $year_filter > 0 || $streaming_filter != '');
$movies_by_genre = [];
if (!$is_filtered) {
    foreach ($all_movies as $movie) {
        $g = trim(explode(", ", $movie['Genre'])[0]);
        $movies_by_genre[$g][] = $movie;
    }
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
        body { font-family: 'Segoe UI', sans-serif; background-color: #0a0a0a; color: #ffffff; }

        /* NAV */
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

        /* FILTER BAR */
        .filter-bar {
            padding: 20px 30px;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .filter-bar form { display: contents; }
        .search-input-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 200px;
            max-width: 340px;
        }
        .search-input-wrap input {
            flex: 1;
            padding: 8px 14px;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
        }
        .search-input-wrap input:focus { outline: none; border-color: #5b80a8; }
        .search-input-wrap input::placeholder { color: #555; }
        .btn-search {
            padding: 8px 16px;
            background: #5b80a8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .btn-search:hover { background: #4a6a90; }
        .filter-select {
            padding: 8px 12px;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
        }
        .filter-select:focus { outline: none; border-color: #5b80a8; }
        .filter-select option { background: #111; }
        .clear-btn {
            padding: 8px 14px;
            background: transparent;
            color: #ff5050;
            border: 1px solid #ff5050;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .clear-btn:hover { background: rgba(255,80,80,0.1); }

        /* RESULTS INFO */
        .results-bar {
            padding: 12px 30px;
            font-size: 12px;
            color: #555;
            border-bottom: 1px solid #111;
        }
        .results-bar span { color: #fff; font-weight: 600; }

        /* GENRE SECTIONS (default view) */
        .genre-section { padding: 24px 30px 12px; }
        .genre-label { font-size: 15px; font-weight: bold; border-bottom: 2px solid #5b80a8; display: inline-block; padding-bottom: 5px; margin-bottom: 16px; }
        .movie-row { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 10px; }
        .movie-row::-webkit-scrollbar { height: 4px; }
        .movie-row::-webkit-scrollbar-track { background: #111; }
        .movie-row::-webkit-scrollbar-thumb { background: #5b80a8; border-radius: 4px; }

        /* MOVIE CARDS */
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
        .movie-card:hover { border-color: #5b80a8; transform: translateY(-4px); }
        .card-title { font-size: 14px; font-weight: bold; line-height: 1.35; }
        .card-year { font-size: 11px; color: #555; margin-top: 4px; }
        .card-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .card-service { font-size: 11px; color: #777; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-score { background: #5b80a8; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; white-space: nowrap; flex-shrink: 0; }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            padding: 24px 30px;
        }
        .movie-grid .movie-card {
            min-width: unset;
            max-width: unset;
            width: 100%;
        }

    
        .empty-state {
            text-align: center;
            color: #444;
            font-size: 14px;
            padding: 60px 30px;
        }
        .empty-state p { margin-bottom: 8px; }
        .empty-state .sub { font-size: 12px; color: #333; }

        .footer { text-align: center; padding: 20px; color: #333; font-size: 12px; border-top: 1px solid #1a1a1a; margin-top: 20px; }
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

<!-- FILTER BAR -->
<form method="GET" action="movies.php">
    <div class="filter-bar">
        <div class="search-input-wrap">
            <input type="text" name="search"
                   placeholder="Search title, actor, description..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">&#128269;</button>
        </div>

        <select name="genre" class="filter-select" onchange="this.form.submit()">
            <option value="">All Genres</option>
            <?php foreach ($all_genres as $g): ?>
                <option value="<?php echo htmlspecialchars($g); ?>"
                    <?php echo ($genre_filter == $g) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($g); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="year" class="filter-select" onchange="this.form.submit()">
            <option value="">All Years</option>
            <?php foreach ($all_years as $y): ?>
                <option value="<?php echo $y; ?>"
                    <?php echo ($year_filter == $y) ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="streaming" class="filter-select" onchange="this.form.submit()">
            <option value="">All Services</option>
            <?php foreach ($all_streaming as $s): ?>
                <option value="<?php echo htmlspecialchars($s); ?>"
                    <?php echo ($streaming_filter == $s) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="rating"    <?php echo ($sort == 'rating')    ? 'selected' : ''; ?>>Top Rated</option>
            <option value="year_desc" <?php echo ($sort == 'year_desc') ? 'selected' : ''; ?>>Newest First</option>
            <option value="year_asc"  <?php echo ($sort == 'year_asc')  ? 'selected' : ''; ?>>Oldest First</option>
            <option value="title"     <?php echo ($sort == 'title')     ? 'selected' : ''; ?>>A – Z</option>
        </select>

        <?php if ($is_filtered): ?>
            <!-- Preserve sort when clearing -->
            <a href="movies.php?sort=<?php echo urlencode($sort); ?>" class="clear-btn">&#10005; Clear Filters</a>
        <?php endif; ?>
    </div>
</form>

<div class="results-bar">
    <?php if ($is_filtered): ?>
        <span><?php echo count($all_movies); ?></span> result<?php echo count($all_movies) != 1 ? 's' : ''; ?> found
        <?php if ($search != ''): ?> for "<em><?php echo htmlspecialchars($search); ?></em>"<?php endif; ?>
    <?php else: ?>
        <span><?php echo count($all_movies); ?></span> movies in library
    <?php endif; ?>
</div>

<?php if (empty($all_movies)): ?>
    <div class="empty-state">
        <p>No movies match your search.</p>
        <p class="sub">Try adjusting your filters or <a href="movies.php" style="color:#5b80a8;">clearing them</a>.</p>
    </div>

<?php elseif ($is_filtered): ?>
    
    <div class="movie-grid">
        <?php foreach ($all_movies as $movie): ?>
            <a href="movie.php?id=<?php echo urlencode($movie['MovieID']); ?>" class="movie-card">
                <div>
                    <div class="card-title"><?php echo htmlspecialchars($movie['Title']); ?></div>
                    <div class="card-year"><?php echo $movie['ReleaseYear'] ?? ''; ?> &bull; <?php echo htmlspecialchars($movie['Genre']); ?></div>
                </div>
                <div class="card-bottom">
                    <div class="card-service"><?php echo htmlspecialchars($movie['StreamingServices']); ?></div>
                    <span class="card-score"><?php echo $movie['Rating']; ?>/10</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <!-- DEFAULT: grouped by genre, horizontal scroll -->
    <?php foreach ($movies_by_genre as $genre => $movies): ?>
        <div class="genre-section">
            <div class="genre-label"><?php echo htmlspecialchars($genre); ?></div>
            <div class="movie-row">
                <?php foreach ($movies as $movie): ?>
                    <a href="movie.php?id=<?php echo urlencode($movie['MovieID']); ?>" class="movie-card">
                        <div>
                            <div class="card-title"><?php echo htmlspecialchars($movie['Title']); ?></div>
                            <div class="card-year"><?php echo $movie['ReleaseYear'] ?? ''; ?></div>
                        </div>
                        <div class="card-bottom">
                            <div class="card-service"><?php echo htmlspecialchars($movie['StreamingServices']); ?></div>
                            <span class="card-score"><?php echo $movie['Rating']; ?>/10</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>