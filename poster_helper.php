<?php
// poster_helper.php
// handles poster image display for movies.php and movie.php
// posters are fetched asynchronously by JavaScript after the page loads
// so the page never hangs waiting on the OMDB api

// checks if the PosterURL stored in the database is a real url
// filters out the "Poster URL" placeholder that ships with the default data
function getMoviePoster(array $movie): ?string
{
    $raw = trim($movie['PosterURL'] ?? '');
    if ($raw === '') return null;
    if (stripos($raw, 'poster') === 0) return null;  // catches "Poster URL" placeholder
    if (!preg_match('#^https?://#i', $raw)) return null;
    return $raw;
}

// returns html for a poster image or a styled fallback div
// if no poster is cached yet, the fallback gets a data-fetch attribute
// which the javascript on each page uses to trigger an async OMDB lookup
// context: 'card' for the movies grid, 'page' for the movie detail page
function renderPosterImg(array $movie, string $context = 'card'): string
{
    $url   = getMoviePoster($movie);
    $id    = htmlspecialchars($movie['MovieID'] ?? '');
    $alt   = htmlspecialchars($movie['Title'] ?? '');
    $title = htmlspecialchars($movie['Title'] ?? '');
    $genre = htmlspecialchars(trim(explode(',', $movie['Genre'] ?? '')[0]));
    $year  = htmlspecialchars($movie['ReleaseYear'] ?? '');

    if ($context === 'page') {
        $fallback = '<div class="poster-fallback"'
                  . ($url === null ? ' data-fetch="' . $id . '"' : '')
                  . '>'
                  .   '<div class="poster-fallback-genre">' . $genre . '</div>'
                  .   '<div class="poster-fallback-title">' . $title . '</div>'
                  .   '<div class="poster-fallback-year">'  . $year  . '</div>'
                  . '</div>';

        if ($url !== null) {
            // if the image fails to load, swap in the fallback using onerror
            $img = '<img src="' . htmlspecialchars($url) . '" alt="' . $alt . ' poster" class="poster-img"'
                 . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">';
            return $img . str_replace('class="poster-fallback"', 'class="poster-fallback" style="display:none;"', $fallback);
        }
        return $fallback;
    }

    // card context - the overlay at the bottom already shows title/year/rating
    // so the fallback only needs to show the genre
    $fallback = '<div class="card-poster-fallback"'
              . ($url === null ? ' data-fetch="' . $id . '"' : '')
              . '>'
              .   '<div class="card-fallback-genre">' . $genre . '</div>'
              . '</div>';

    if ($url !== null) {
        $img = '<img src="' . htmlspecialchars($url) . '" alt="' . $alt . ' poster" class="card-poster-img"'
             . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">';
        return $img . str_replace('class="card-poster-fallback"', 'class="card-poster-fallback" style="display:none;"', $fallback);
    }
    return $fallback;
}
