<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "moviesdb");

// Already logged in -> go home
if (isset($_SESSION['logged_in']) && $_SESSION['user_id'] != 'Guest') {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $username   = trim($_POST['username']);
    $fav_genre  = trim($_POST['fav_genre']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be between 3 and 20 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already taken
        $check_user = mysqli_real_escape_string($conn, $username);
        $check_sql  = "SELECT UserID FROM Users WHERE UserID = '$check_user'";
        $check_res  = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_res) > 0) {
            $error = "That username is already taken. Please choose another.";
        } else {
            // Check if email already registered
            $check_email = mysqli_real_escape_string($conn, $email);
            $email_sql   = "SELECT UserID FROM Users WHERE Email = '$check_email'";
            $email_res   = mysqli_query($conn, $email_sql);
            if (mysqli_num_rows($email_res) > 0) {
                $error = "An account with that email already exists.";
            } else {
                // All good — insert user
                $hashed   = password_hash($password, PASSWORD_DEFAULT);
                $fn_esc   = mysqli_real_escape_string($conn, $first_name);
                $ln_esc   = mysqli_real_escape_string($conn, $last_name);
                $em_esc   = mysqli_real_escape_string($conn, $email);
                $un_esc   = mysqli_real_escape_string($conn, $username);
                $fg_esc   = mysqli_real_escape_string($conn, $fav_genre);
                $pw_esc   = mysqli_real_escape_string($conn, $hashed);

                $insert_sql = "INSERT INTO Users (UserID, FirstName, LastName, Email, UserType, FavoriteGenre, ReviewCount, Password)
                               VALUES ('$un_esc', '$fn_esc', '$ln_esc', '$em_esc', 'Regular', '$fg_esc', 0, '$pw_esc')";

                if (mysqli_query($conn, $insert_sql)) {
                    // Auto-login
                    $_SESSION['logged_in']  = true;
                    $_SESSION['user_id']    = $username;
                    $_SESSION['user_type']  = 'Regular';
                    $_SESSION['fav_genre']  = $fav_genre;
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}

$genres = ['Action','Adventure','Animation','Biography','Comedy','Crime','Drama',
           'Family','Fantasy','History','Horror','Music','Mystery','Romance',
           'Sci-Fi','Thriller','War','Western'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MTM Studios</title>
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
        .nav-back { color: #555; text-decoration: none; font-size: 13px; transition: color 0.2s; }
        .nav-back:hover { color: #5b80a8; }

        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .register-box {
            width: 100%;
            max-width: 480px;
            text-align: center;
        }
        .register-box h1 {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 4px;
            margin-bottom: 6px;
        }
        .register-box h1 span { color: #5b80a8; }
        .subtitle { font-size: 13px; color: #555; margin-bottom: 32px; letter-spacing: 1px; }

        .form-row { display: flex; gap: 12px; }
        .form-row .form-group { flex: 1; }

        .form-group {
            margin-bottom: 14px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 11px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 16px;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group select option { background: #111; }
        .form-group input:focus,
        .form-group select:focus { outline: none; border-color: #5b80a8; }
        .form-group input::placeholder { color: #444; }

        .error-msg {
            background: rgba(255, 80, 80, 0.1);
            border: 1px solid rgba(255, 80, 80, 0.3);
            color: #ff5050;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            text-align: left;
        }
        .register-btn {
            width: 100%;
            padding: 12px;
            background: #5b80a8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.15s;
        }
        .register-btn:hover { background: #4a6a90; transform: translateY(-2px); }
        .login-link {
            display: inline-block;
            margin-top: 20px;
            color: #555;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        .login-link span { color: #5b80a8; }
        .login-link:hover span { text-decoration: underline; }
        .footer {
            text-align: center;
            padding: 20px;
            color: #333;
            font-size: 12px;
            border-top: 1px solid #1a1a1a;
        }
        .hint { font-size: 11px; color: #444; margin-top: 4px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM STUDIOS</a>
    <a href="index.php" class="nav-back">&larr; Back to home</a>
</nav>

<div class="register-container">
    <div class="register-box">
        <h1>CREATE <span>ACCOUNT</span></h1>
        <div class="subtitle">Join MTM Studios to write reviews and more</div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" placeholder="Jane"
                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" placeholder="Doe"
                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="jane@example.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="jane_doe"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       maxlength="20" required>
                <div class="hint">3–20 characters. Letters, numbers, and underscores only.</div>
            </div>

            <div class="form-group">
                <label>Favorite Genre</label>
                <select name="fav_genre" required>
                    <option value="" disabled selected>Select a genre</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g; ?>"
                            <?php echo (isset($_POST['fav_genre']) && $_POST['fav_genre'] == $g) ? 'selected' : ''; ?>>
                            <?php echo $g; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="register-btn">CREATE ACCOUNT</button>
        </form>

        <a href="index.php" class="login-link">Already have an account? <span>Sign in</span></a>
    </div>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?>