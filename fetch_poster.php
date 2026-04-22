<?php
// fetch_poster.php
// ajax endpoint called by javascript on movies.php and movie.php
// takes ?id=tt0068646, returns json with the poster url
// checks the database first so we only hit the OMDB api once per movie

session_start();
include __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// must be logged in to use this endpoint
if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['error' => 'not logged in']);
    exit();
}

// sanitize and validate the imdb id
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$id = preg_replace('/[^a-zA-Z0-9]/', '', $id);
if (!preg_match('/^tt\d+$/i', $id)) {
    echo json_encode(['error' => 'invalid id: ' . $id]);
    exit();
}

$safe_id = mysqli_real_escape_string($conn, $id);

// check if we already have a real poster url cached in the database
$result = mysqli_query($conn, "SELECT PosterURL FROM Movies WHERE MovieID = '$safe_id'");
if (!$result) {
    echo json_encode(['error' => 'db error: ' . mysqli_error($conn)]);
    exit();
}

$row = mysqli_fetch_assoc($result);
$raw = trim($row['PosterURL'] ?? '');

// if its a real url, return it right away without calling OMDB
$is_cached = ($raw !== '' && stripos($raw, 'poster') !== 0 && preg_match('#^https?://#i', $raw));
if ($is_cached) {
    echo json_encode(['poster' => $raw]);
    mysqli_close($conn);
    exit();
}

// not cached yet - need to call OMDB
$key = defined('OMDB_API_KEY') ? OMDB_API_KEY : '';
if (!$key || $key === 'PASTE_KEY_HERE') {
    echo json_encode(['error' => 'api key not configured in db_connect.php']);
    exit();
}

$omdb_url = 'https://www.omdbapi.com/?i=' . urlencode($id) . '&apikey=' . urlencode($key);
$response = null;
$fetch_error = '';

// try curl first - needed on XAMPP where allow_url_fopen is often off
if (function_exists('curl_init')) {
    $ch = curl_init($omdb_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // needed on XAMPP
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);      // needed on XAMPP Windows
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response    = curl_exec($ch);
    $fetch_error = curl_error($ch);
    curl_close($ch);
    if (!$response) {
        // report back the exact curl error so we can diagnose it
        echo json_encode(['error' => 'curl failed: ' . $fetch_error]);
        exit();
    }
} elseif (ini_get('allow_url_fopen')) {
    $ctx      = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $response = @file_get_contents($omdb_url, false, $ctx);
    if (!$response) {
        echo json_encode(['error' => 'file_get_contents failed - both curl and allow_url_fopen are unavailable or blocked']);
        exit();
    }
} else {
    echo json_encode(['error' => 'no http method available - enable curl in XAMPP php.ini: uncomment extension=curl']);
    exit();
}

$data   = json_decode($response, true);
$poster = $data['Poster'] ?? '';

if ($poster === '' || $poster === 'N/A') {
    echo json_encode(['error' => 'omdb returned no poster', 'omdb_response' => substr($response, 0, 200)]);
    exit();
}

// save to database so we never have to fetch this one again
$safe_poster = mysqli_real_escape_string($conn, $poster);
mysqli_query($conn, "UPDATE Movies SET PosterURL = '$safe_poster' WHERE MovieID = '$safe_id'");

echo json_encode(['poster' => $poster]);
mysqli_close($conn);
?>
