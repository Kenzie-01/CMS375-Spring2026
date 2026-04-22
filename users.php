<?php
session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

$is_guest = ($_SESSION['user_id'] === 'Guest');

$role_filter = "";
if (isset($_GET['role']) && $_GET['role'] != "") {
    $role_filter = mysqli_real_escape_string($conn, $_GET['role']);
}

$sql = "SELECT * FROM Users";
if ($role_filter != "") {
    $sql .= " WHERE UserType = '$role_filter'";
}

$query = "SELECT u.*, COALESCE(r.ReviewCount, 0) AS ReviewCount
          FROM Users u
          LEFT JOIN (SELECT UserID, COUNT(ReviewID) AS ReviewCount 
                     FROM Reviews GROUP BY UserID) r 
          ON u.UserID = r.UserID
          ORDER BY ReviewCount DESC";

$result = mysqli_query($conn, $sql);
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - MTM Studios</title>
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

        .page-header { padding: 36px 30px 24px; border-bottom: 1px solid #1a1a1a; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
        .page-header-left { display: flex; align-items: baseline; gap: 14px; }
        .page-title { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border-bottom: 2px solid #5b80a8; padding-bottom: 5px; }
        .page-count { font-size: 12px; color: #555; }

        .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-bar a { padding: 5px 16px; border-radius: 20px; text-decoration: none; font-size: 12px; border: 1px solid #2a2a2a; color: #777; transition: all 0.2s; }
        .filter-bar a:hover { border-color: #5b80a8; color: #5b80a8; }
        .filter-bar a.active { background: #5b80a8; color: #fff; border-color: #5b80a8; }

        .table-container { padding: 28px 30px 60px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { border-bottom: 2px solid #1c1c1c; }
        th { padding: 10px 16px; text-align: left; font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        td { padding: 13px 16px; font-size: 14px; border-bottom: 1px solid #111; color: #ccc; }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover td { background: #0f0f0f; }

        .role-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; }
        .role-Admin   { background: rgba(91,128,168,0.15); border: 1px solid #5b80a8; color: #5b80a8; }
        .role-Critic  { background: rgba(255,255,255,0.06); border: 1px solid #555; color: #aaa; }
        .role-Regular { background: rgba(255,255,255,0.04); border: 1px solid #2a2a2a; color: #666; }

        .footer { text-align: center; padding: 20px; color: #333; font-size: 12px; border-top: 1px solid #1a1a1a; }
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
            <a href="reviews.php" title="Reviews">&#9733;</a>
            <a href="users.php" class="active" title="Users">&#128100;</a>
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

<div class="page-header">
    <div class="page-header-left">
        <div class="page-title">Users</div>
        <div class="page-count"><?php echo $total; ?> total</div>
    </div>
    <div class="filter-bar">
        <a href="users.php"              class="<?php echo ($role_filter == '')        ? 'active' : ''; ?>">All</a>
        <a href="users.php?role=Admin"   class="<?php echo ($role_filter == 'Admin')   ? 'active' : ''; ?>">Admin</a>
        <a href="users.php?role=Critic"  class="<?php echo ($role_filter == 'Critic')  ? 'active' : ''; ?>">Critic</a>
        <a href="users.php?role=Regular" class="<?php echo ($role_filter == 'Regular') ? 'active' : ''; ?>">Regular</a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Name</th>
                <th>Role</th>
                <th>Favorite Genre</th>
                <th>Reviews Written</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                    <td><?php
                        $name = trim(($user['FirstName'] ?? '') . ' ' . ($user['LastName'] ?? ''));
                        echo $name ? htmlspecialchars($name) : '<span style="color:#333;">—</span>';
                    ?></td>
                    <td>
                        <span class="role-badge role-<?php echo htmlspecialchars($user['UserType']); ?>">
                            <?php echo htmlspecialchars($user['UserType']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($user['FavoriteGenre']); ?></td>
                    <td><?php echo $user['ReviewCount']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="footer">MTM Studios &copy; 2026 | CMS 375 Database Project</div>
</body>
</html>
<?php mysqli_close($conn); ?> 
