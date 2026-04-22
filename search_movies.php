<?php
session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_id'] === 'Guest') {
    echo json_encode([]);
    exit();
}

$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$movies = [];

if ($q !== '') {
    $search_param = "%$q%";
    $stmt = $conn->prepare("SELECT MovieID, Title, Genre, Rating, ReleaseYear FROM Movies WHERE Title LIKE ? ORDER BY Rating DESC LIMIT 10");
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($movies);
mysqli_close($conn);
?>