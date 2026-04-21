<?php
session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_id'] === 'Guest') {
    echo json_encode([]);
    exit();
}

$q      = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$movies = [];

if ($q !== '') {
    $sql    = "SELECT MovieID, Title, Genre, Rating, ReleaseYear FROM Movies WHERE Title LIKE '%$q%' ORDER BY Rating DESC LIMIT 10";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $movies[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($movies);
mysqli_close($conn);
?>
