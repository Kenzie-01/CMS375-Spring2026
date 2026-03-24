<?php

include __DIR__ . '/db_connect.php';

$role_filter = "";
if (isset($_GET['role']) && $_GET['role'] != "") {
    $role_filter = mysqli_real_escape_string($conn, $_GET['role']);
}

$sql = "SELECT * FROM Users";
if ($role_filter != "") {
    $sql .= " WHERE UserType = '$role_filter'";
}
$sql .= " ORDER BY ReviewCount DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - MTM Studios</title>
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
        .page-header p { color: #aaa; font-size: 15px; margin-bottom: 20px; }
        .filter-bar { display: flex; justify-content: center; gap: 10px; }
        .filter-bar a { padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 14px; border: 1px solid #333; color: #aaa; }
        .filter-bar a:hover { border-color: #e94560; color: #e94560; }
        .filter-bar a.active { background-color: #e94560; color: white; border-color: #e94560; }
        .table-container { max-width: 1000px; margin: 25px auto; padding: 0 30px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background-color: #16213e; border-radius: 10px; overflow: hidden; }
        th { background-color: #1a1a2e; padding: 14px 18px; text-align: left; font-size: 13px; color: #e94560; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 12px 18px; font-size: 14px; border-bottom: 1px solid #1a1a3e; color: #ccc; }
        tr:hover { background-color: rgba(233, 69, 96, 0.05); }
        .role-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 12px; font-weight: bold; }
        .role-admin { background-color: rgba(233, 69, 96, 0.2); color: #e94560; }
        .role-critic { background-color: rgba(96, 163, 233, 0.2); color: #60a3e9; }
        .role-regular { background-color: rgba(100, 200, 150, 0.2); color: #64c896; }
        .footer { text-align: center; padding: 20px; color: #555; font-size: 13px; border-top: 1px solid #1a1a2e; margin-top: 30px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">MTM Studios</a>
    <div class="nav-links">
        <a href="index.php">Browse Movies</a>
        <a href="reviews.php">Reviews</a>
        <a href="users.php" class="active">Users</a>
    </div>
</nav>

<div class="page-header">
    <h1>Users</h1>
    <p><?php echo mysqli_num_rows($result); ?> registered users</p>
    <div class="filter-bar">
        <a href="users.php" class="<?php if ($role_filter == '') echo 'active'; ?>">All</a>
        <a href="users.php?role=Admin" class="<?php if ($role_filter == 'Admin') echo 'active'; ?>">Admins</a>
        <a href="users.php?role=Critic" class="<?php if ($role_filter == 'Critic') echo 'active'; ?>">Critics</a>
        <a href="users.php?role=Regular" class="<?php if ($role_filter == 'Regular') echo 'active'; ?>">Regular</a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Role</th>
                <th>Favorite Genre</th>
                <th>Reviews Written</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                    <td>
                        <?php
                        $role_class = 'role-regular';
                        if ($user['UserType'] == 'Admin') $role_class = 'role-admin';
                        if ($user['UserType'] == 'Critic') $role_class = 'role-critic';
                        ?>
                        <span class="role-badge <?php echo $role_class; ?>">
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
