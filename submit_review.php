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

$movie_id = isset($_POST['movie_id'])    ? trim($_POST['movie_id'])    : '';
$score    = isset($_POST['score'])       ? intval($_POST['score'])      : 0;
$text     = isset($_POST['review_text']) ? trim($_POST['review_text'])  : '';
$user_id  = $_SESSION['user_id'];

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
$stmt = $conn->prepare("SELECT MovieID FROM Movies WHERE MovieID = ?");
$stmt->bind_param("s", $movie_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Movie not found.']);
    $stmt->close();
    exit();
}
$stmt->close();

// Check duplicate review
$stmt = $conn->prepare("SELECT ReviewID FROM Reviews WHERE UserID = ? AND MovieID = ?");
$stmt->bind_param("ss", $user_id, $movie_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this movie.']);
    $stmt->close();
    exit();
}
$stmt->close();

// Generate review ID
$max_stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(ReviewID, 5) AS UNSIGNED)) AS max_id FROM Reviews WHERE ReviewID LIKE 'rev_%'");
$max_stmt->execute();
$max_result = $max_stmt->get_result();
$max_row    = $max_result->fetch_assoc();
$next_id    = (int)($max_row['max_id'] ?? 0) + 1;
$rev_id     = 'rev_' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
$max_stmt->close();

// Insert review
$stmt = $conn->prepare("INSERT INTO Reviews (ReviewID, MovieID, UserID, Score, Text) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssis", $rev_id, $movie_id, $user_id, $score, $text);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
mysqli_close($conn);
?>