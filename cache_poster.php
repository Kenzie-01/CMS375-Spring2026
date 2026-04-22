<?php
// cache_poster.php
// called by javascript after it fetches a poster directly from OMDB
// saves the url to the database so next time the page loads the poster is instant
// doesnt return anything - its purely a fire-and-forget cache write

session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['logged_in'])) exit();

$id  = isset($_GET['id'])  ? preg_replace('/[^a-zA-Z0-9]/', '', trim($_GET['id']))  : '';
$url = isset($_GET['url']) ? trim($_GET['url']) : '';

// validate both values before touching the database
if (!preg_match('/^tt\d+$/i', $id)) exit();
if (!preg_match('#^https?://#i', $url)) exit();

$safe_id  = mysqli_real_escape_string($conn, $id);
$safe_url = mysqli_real_escape_string($conn, $url);

mysqli_query($conn, "UPDATE Movies SET PosterURL = '$safe_url' WHERE MovieID = '$safe_id'");
mysqli_close($conn);
?>
