<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "moviesdb");

$error = "";

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $user_id  = mysqli_real_escape_string($conn, $_POST['user_id']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM Users WHERE UserID = '$user_id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['Password']) || $password === $user['Password']) {
            $_SESSION['user_id']   = $user['UserID'];
            $_SESSION['user_type'] = $user['UserType'];
            $_SESSION['fav_genre'] = $user['FavoriteGenre'];
            $_SESSION['logged_in'] = true;
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}

// Handle Continue as Guest
if (isset($_POST['guest'])) {
    $_SESSION['logged_in']  = true;
    $_SESSION['user_id']    = 'Guest';
    $_SESSION['user_type']  = 'Guest';
    header("Location: index.php");
    exit();
}

// Show login page if not logged in
if (!isset($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTM Studios - Sign In</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            background-color: #0a0a0a;
            border-bottom: 2px solid #ffffff;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 20px; font-weight: bold; color: #5b80a8; text-decoration: none; letter-spacing: 2px; }
        .nav-register { color: #5b80a8; text-decoration: none; font-size: 13px; border: 1px solid #5b80a8; padding: 6px 14px; border-radius: 8px; transition: all 0.2s; }
        .nav-register:hover { background: #5b80a8; color: #fff; }
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .login-box { width: 100%; max-width: 400px; text-align: center; }
        .login-box h1 { font-size: 36px; font-weight: 800; letter-spacing: 4px; margin-bottom: 8px; }
        .login-box h1 span { color: #5b80a8; }
        .subtitle { font-size: 13px; color: #555; margin-bottom: 36px; letter-spacing: 1px; }
        .form-group { margin-bottom: 16px; text-align: left; }
        .form-group label { display: block; font-size: 11px; color: #555; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 12px 16px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 14px; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: #5b80a8; }
        .form-group input::placeholder { color: #444; }
        .error-msg { background: rgba(255,80,80,0.1); border: 1px solid rgba(255,80,80,0.3); color: #ff5050; padding: 10px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .login-btn { width: 100%; padding: 12px; background: #5b80a8; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; letter-spacing: 1px; cursor: pointer; transition: background 0.2s, transform 0.15s; margin-top: 8px; }
        .login-btn:hover { background: #4a6a90; transform: translateY(-2px); }
        .divider { display: flex; align-items: center; margin: 24px 0; gap: 16px; }
        .divider-line { flex: 1; height: 1px; background: #222; }
        .divider-text { font-size: 11px; color: #444; text-transform: uppercase; letter-spacing: 2px; }
        .guest-btn { width: 100%; padding: 12px; background: transparent; color: #fff; border: 2px solid #ffffff; border-radius: 8px; font-size: 14px; font-weight: 600; letter-spacing: 1px; cursor: pointer; transition: background 0.2s, transform 0.15s; }
        .guest-btn:hover { background: #1a1a1a; transform: translateY(-2px); }
        .register-link { display: inline-block; margin-top: 20px; color: #555; text-decoration: none; font-size: 13px; }
        .register-link span { color: #5b80a8; }
        .register-link:hover span { text-decoration: underline; }
        .footer { text-align: center; padding: 20px; color: #333; font-size: 12px; border-top: 1px solid #1a1a1a; }
    </style>
</head>
<body>

<nav class="navbar">
    <span class="logo">MTM STUDIOS</span>
    <a href="register.php" class="nav-register">Create Account</a>
</nav>

<div class="login-container">
    <div class="login-box">
        <h1>SIGN <span>IN</span></h1>
        <div class="subtitle">Enter your credentials to continue</div>

        <?php if ($error != ""): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label>User ID</label>
                <input type="text" name="user_id" placeholder="e.g. user_001" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="login-btn">LOG IN</button>
        </form>

        <div class="divider">
            <div class="divider-line"></div>
            <div class="divider-text">or</div>
            <div class="divider-line"></div>
        </div>

        <form method="POST" action="index.php">
            <button type="submit" name="guest" class="guest-btn">CONTINUE AS GUEST</button>
        </form>

        <a href="register.php" class="register-link">New here? <span>Create an account</span></a>
    </div>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php
mysqli_close($conn);
exit();
endif;

// --- Home page (logged in) ---
$stats_sql = "SELECT
    (SELECT COUNT(*) FROM Movies)  AS total_movies,
    (SELECT COUNT(*) FROM Reviews) AS total_reviews,
    (SELECT COUNT(*) FROM Users)   AS total_users";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

$top_sql    = "SELECT * FROM Movies ORDER BY Rating DESC LIMIT 5";
$top_result = mysqli_query($conn, $top_sql);

$is_guest = ($_SESSION['user_id'] === 'Guest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTM Studios</title>
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
        .hero { padding: 80px 30px 60px; text-align: center; border-bottom: 1px solid #1a1a1a; }
        .hero h1 { font-size: 56px; font-weight: 800; letter-spacing: 6px; line-height: 1.1; margin-bottom: 18px; }
        .hero h1 span { color: #5b80a8; }
        .hero p { font-size: 15px; color: #666; max-width: 460px; margin: 0 auto 36px; line-height: 1.7; }
        .hero-actions { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .btn-primary { padding: 11px 28px; background: #5b80a8; color: #fff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; transition: background 0.2s, transform 0.15s; }
        .btn-primary:hover { background: #4a6a90; transform: translateY(-2px); }
        .btn-outline { padding: 11px 28px; background: transparent; color: #fff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; border: 2px solid #ffffff; transition: background 0.2s, transform 0.15s; }
        .btn-outline:hover { background: #1a1a1a; transform: translateY(-2px); }
        .stats-section { display: flex; justify-content: center; border-bottom: 1px solid #1a1a1a; }
        .stat-block { flex: 1; max-width: 240px; padding: 36px 20px; text-align: center; border-right: 1px solid #1a1a1a; }
        .stat-block:last-child { border-right: none; }
        .stat-number { font-size: 40px; font-weight: 800; letter-spacing: -1px; line-height: 1; margin-bottom: 8px; }
        .stat-number span { color: #5b80a8; }
        .stat-label { font-size: 11px; color: #555; text-transform: uppercase; letter-spacing: 2px; }
        .section { padding: 40px 30px; }
        .section-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 20px; }
        .section-title { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border-bottom: 2px solid #5b80a8; padding-bottom: 5px; }
        .section-link { font-size: 12px; color: #5b80a8; text-decoration: none; letter-spacing: 1px; }
        .section-link:hover { text-decoration: underline; }
        .top-list { display: flex; flex-direction: column; gap: 1px; }
        .top-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: #0f0f0f; border: 1px solid #1c1c1c; border-radius: 10px; text-decoration: none; color: #ffffff; transition: border-color 0.2s, background 0.2s; gap: 16px; }
        .top-item:hover { border-color: #5b80a8; background: #111; }
        .top-item-left { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .top-rank { font-size: 11px; color: #444; font-weight: 700; width: 20px; text-align: right; flex-shrink: 0; }
        .top-title { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .top-genre { font-size: 11px; color: #555; }
        .top-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .top-service { font-size: 11px; color: #555; }
        .top-score { background: #5b80a8; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .guest-banner { background: #111; border: 1px solid #2a2a2a; border-radius: 10px; padding: 16px 24px; margin: 24px 30px 0; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .guest-banner p { font-size: 14px; color: #777; }
        .guest-banner a { background: #5b80a8; color: #fff; text-decoration: none; padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; white-space: nowrap; transition: background 0.2s; }
        .guest-banner a:hover { background: #4a6a90; }
        .footer { text-align: center; padding: 20px; color: #333; font-size: 12px; border-top: 1px solid #1a1a1a; margin-top: 10px; }
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
        <div class="user-info">
            <span class="user-badge"><?php echo htmlspecialchars($_SESSION['user_type']); ?></span>
            <?php echo htmlspecialchars($_SESSION['user_id']); ?>
            <?php if ($is_guest): ?>
                <a href="login.php" class="logout-link">Sign In</a>
                <a href="register.php" class="create-acct-link">Create Account</a>
            <?php else: ?>
                <a href="logout.php" class="logout-link">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if ($is_guest): ?>
<div class="guest-banner">
    <p>You're browsing as a guest. Create an account to write reviews and track your favorites.</p>
    <a href="register.php">Create Account</a>
</div>
<?php endif; ?>

<div class="hero">
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
                        <div class="top-genre"><?php echo htmlspecialchars($movie['Genre']); ?> &bull; <?php echo $movie['ReleaseYear'] ?? '—'; ?></div>
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