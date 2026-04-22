<?php
// debug_posters.php
// drop this in your project folder and open it in the browser
// it will tell you exactly why posters arent loading
// delete it when youre done

include __DIR__ . '/db_connect.php';

echo "<style>body{font-family:monospace;background:#0a0a0a;color:#eee;padding:30px;} .ok{color:#50c878;} .fail{color:#ff5050;} .warn{color:#f0c040;} h2{color:#5b80a8;margin:20px 0 8px;}</style>";

echo "<h1 style='color:#5b80a8'>Poster Debug</h1>";

// 1. Check allow_url_fopen
echo "<h2>1. allow_url_fopen</h2>";
if (ini_get('allow_url_fopen')) {
    echo "<span class='ok'>✓ ON - file_get_contents can fetch URLs</span><br>";
} else {
    echo "<span class='fail'>✗ OFF - file_get_contents cannot fetch external URLs (this is your problem)</span><br>";
}

// 2. Check cURL
echo "<h2>2. cURL extension</h2>";
if (function_exists('curl_init')) {
    echo "<span class='ok'>✓ Available</span><br>";
} else {
    echo "<span class='fail'>✗ Not available</span><br>";
}

// 3. Check API key
echo "<h2>3. OMDB API Key</h2>";
$key = defined('OMDB_API_KEY') ? OMDB_API_KEY : '';
if ($key && $key !== 'PASTE_KEY_HERE') {
    echo "<span class='ok'>✓ Key is set: " . htmlspecialchars($key) . "</span><br>";
} else {
    echo "<span class='fail'>✗ Key not set</span><br>";
}

// 4. Try a live OMDB request
echo "<h2>4. Live OMDB test (The Godfather - tt0068646)</h2>";
$test_url = "https://www.omdbapi.com/?i=tt0068646&apikey=" . urlencode($key);

$result = null;
$method = '';

if (function_exists('curl_init')) {
    $ch = curl_init($test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    $err    = curl_error($ch);
    curl_close($ch);
    $method = 'cURL';
    if ($err) echo "<span class='fail'>cURL error: " . htmlspecialchars($err) . "</span><br>";
} elseif (ini_get('allow_url_fopen')) {
    $ctx    = stream_context_create(['http' => ['timeout' => 8]]);
    $result = @file_get_contents($test_url, false, $ctx);
    $method = 'file_get_contents';
}

if ($result) {
    $data = json_decode($result, true);
    echo "<span class='ok'>✓ Got response via $method</span><br>";
    echo "Response: " . htmlspecialchars(substr($result, 0, 300)) . "<br>";
    if (!empty($data['Poster']) && $data['Poster'] !== 'N/A') {
        echo "<br><span class='ok'>✓ Poster URL: " . htmlspecialchars($data['Poster']) . "</span><br>";
        echo "<img src='" . htmlspecialchars($data['Poster']) . "' style='height:150px;margin-top:10px;border-radius:6px;'>";
    } else {
        echo "<span class='fail'>✗ No poster in response</span><br>";
    }
} else {
    echo "<span class='fail'>✗ Could not reach OMDB - check your internet connection or firewall</span><br>";
}

// 5. Check a few movies in the DB
echo "<h2>5. Sample PosterURLs from database</h2>";
$rows = mysqli_query($conn, "SELECT MovieID, Title, PosterURL FROM Movies LIMIT 5");
while ($r = mysqli_fetch_assoc($rows)) {
    $url = trim($r['PosterURL'] ?? '');
    $is_real = preg_match('#^https?://#i', $url) && stripos($url, 'poster') !== 0;
    $status  = $is_real ? "<span class='ok'>✓ has URL</span>" : "<span class='warn'>⚠ placeholder</span>";
    echo htmlspecialchars($r['Title']) . " — $status — " . htmlspecialchars($url) . "<br>";
}

mysqli_close($conn);
?>
