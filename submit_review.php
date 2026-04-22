<?php
session_start();
include __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_id'] === 'Guest') {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$movie_id = isset($_POST['movie_id'])    ? mysqli_real_escape_string($conn, trim($_POST['movie_id']))    : '';
$score    = isset($_POST['score'])       ? intval($_POST['score'])                                        : 0;
$text     = isset($_POST['review_text']) ? mysqli_real_escape_string($conn, trim($_POST['review_text'])) : '';
$user_id  = mysqli_real_escape_string($conn, $_SESSION['user_id']);

if (empty($movie_id)) {
    echo json_encode(['success' => false, 'message' => 'No movie selected.']);
    exit();
}
if ($score < 1 || $score > 10) {
    echo json_encode(['success' => false, 'message' => 'Score must be between 1 and 10.']);
    exit();
}
if (empty($text)) {
    echo json_encode(['success' => false, 'message' => 'Review text cannot be empty.']);
    exit();
}

// Check movie exists
$movie_check = mysqli_query($conn, "SELECT MovieID FROM Movies WHERE MovieID = '$movie_id'");
if (mysqli_num_rows($movie_check) === 0) {
    echo json_encode(['success' => false, 'message' => 'Movie not found.']);
    exit();
}

// Check if user already reviewed this movie
$dup_check = mysqli_query($conn, "SELECT ReviewID FROM Reviews WHERE UserID = '$user_id' AND MovieID = '$movie_id'");
if (mysqli_num_rows($dup_check) > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this movie.']);
    exit();
}

// Generate review ID
$max_q   = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(ReviewID, 5) AS UNSIGNED)) AS max_id FROM Reviews WHERE ReviewID LIKE 'rev_%'");
$max_row = mysqli_fetch_assoc($max_q);
$next_id = (int)($max_row['max_id'] ?? 0) + 1;
$rev_id  = 'rev_' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

// Insert review
mysqli_query($conn, "INSERT INTO Reviews (ReviewID, MovieID, UserID, Score, Text) VALUES ('$rev_id', '$movie_id', '$user_id', $score, '$text')");

echo json_encode(['success' => true]);
mysqli_close($conn);
?>
