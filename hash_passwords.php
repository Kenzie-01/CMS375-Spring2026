<?php


$conn = mysqli_connect("localhost", "root", "", "moviesdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$alter_sql = "ALTER TABLE Users MODIFY Password VARCHAR(255)";
$alter_result = mysqli_query($conn, $alter_sql);

if ($alter_result) {
    echo "Step 1: Password column updated to VARCHAR(255)<br><br>";
} else {
    echo "Step 1 Error: " . mysqli_error($conn) . "<br><br>";
}


$sql = "SELECT UserID, Password FROM Users";
$result = mysqli_query($conn, $sql);

$count = 0;
while ($user = mysqli_fetch_assoc($result)) {
    $plain_password = $user['Password'];

    if (strpos($plain_password, '$2y$') === 0) {
        echo $user['UserID'] . " - already hashed, skipping<br>";
        continue;
    }

    $hashed = password_hash($plain_password, PASSWORD_DEFAULT);

    $update_sql = "UPDATE Users SET Password = '" . mysqli_real_escape_string($conn, $hashed) . "' WHERE UserID = '" . $user['UserID'] . "'";
    mysqli_query($conn, $update_sql);

    echo $user['UserID'] . " - password hashed successfully<br>";
    $count++;
}

echo "<br><strong>Done! $count passwords hashed.</strong>";
echo "<br><br><strong style='color:red;'>DELETE THIS FILE NOW (hash_passwords.php) — it should only be run once.</strong>";

mysqli_close($conn);
?>
