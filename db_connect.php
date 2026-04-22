<?php
// db_connect.php
// this file handles the database connection and is included at the top of every page
// changed the db name to moviesdb when we set up the schema

$conn = mysqli_connect("localhost", "root", "", "moviesdb");

// make sure the connection actually worked
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// OMDB api key used for retreiving movie poster images automatically
// you can get a free key at omdbapi.com - it uses the imdb ID to look up the right poster
// the key gets passed to poster_helper.php which handles the actual fetching
define('OMDB_API_KEY', 'cef71baf');
?>
