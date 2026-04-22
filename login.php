<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "moviesdb");

$error = "";

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != 'Guest') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id  = $_POST['user_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['logged_in']  = true;
            $_SESSION['user_id']    = $user['UserID'];
            $_SESSION['user_type']  = $user['UserType'];
            $_SESSION['fav_genre']  = $user['FavoriteGenre'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MTM Studios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #0a0a0a; color: #ffffff; display: flex; flex-direction: column; min-height: 100vh; }
        .navbar { background-color: #0a0a0a; border-bottom: 2px solid #ffffff; padding: 14px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 20px; font-weight: bold; color: #5b80a8; text-decoration: none; letter-spacing: 2px; }
        .nav-register { color: #5b80a8; text-decoration: none; font-size: 13px; border: 1px solid #5b80a8; padding: 6px 14px; border-radius: 8px; transition: all 0.2s; }
        .nav-register:hover { background: #5b80a8; color: #fff; }
        .login-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
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
        .register-link { display: inline-block; margin-top: 20px; color: #555; text-decoration: none; font-size: 13px; }
        .register-link span { color: #5b80a8; }
        .register-link:hover span { text-decoration: underline; }
        .footer { text-align: center; padding: 20px; color: #333; font-size: 12px; border-top: 1px solid #1a1a1a; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM STUDIOS</a>
    <a href="register.php" class="nav-register">Create Account</a>
</nav>

<div class="login-container">
    <div class="login-box">
        <h1>SIGN <span>IN</span></h1>
        <div class="subtitle">Enter your credentials to continue</div>

        <?php if ($error != ""): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>User ID / Username</label>
                <input type="text" name="user_id" placeholder="e.g. user_001" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">LOG IN</button>
        </form>

        <a href="register.php" class="register-link">New here? <span>Create an account</span></a>
    </div>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>