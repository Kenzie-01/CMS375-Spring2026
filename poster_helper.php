<?php
/**
 * poster_helper.php
 * Poster utility functions for MTM Studios movie database.
 *
 * Usage:
 *   include __DIR__ . '/poster_helper.php';
 *   $url = getMoviePoster($movie);           // returns URL string or null
 *   echo renderPosterImg($movie, 'card');     // returns <img> or fallback HTML
 */

/**
 * Returns a validated poster URL for a movie row, or null if none is stored.
 *
 * A "real" poster URL must begin with http:// or https://.
 * Placeholder values like "Poster URL", empty strings, or NULL are treated as missing.
 *
 * @param  array       $movie  Associative row from the Movies table.
 * @return string|null         Absolute URL ready for use in src="…", or null.
 */
function getMoviePoster(array $movie): ?string
{
    $raw = trim($movie['PosterURL'] ?? '');

    // Reject blank values and obvious placeholder text
    if ($raw === '' || stripos($raw, 'poster') === 0) {
        return null;
    }

    // Accept only proper http/https URLs
    if (!preg_match('#^https?://#i', $raw)) {
        return null;
    }

    return $raw;
}

/**
 * Returns the inline CSS background-image value for a poster, or empty string.
 * Useful when you want to use the poster as a CSS background on a card.
 *
 * @param  array  $movie
 * @return string  e.g. "url('https://…')" or ""
 */
function getPosterCssBackground(array $movie): string
{
    $url = getMoviePoster($movie);
    if ($url === null) return '';
    // Escape single quotes inside the URL just in case
    return "url('" . str_replace("'", "%27", $url) . "')";
}

/**
 * Renders a full poster <img> tag, or a styled text-based fallback <div>
 * when no valid poster URL exists.
 *
 * @param  array  $movie    Movie row from DB.
 * @param  string $context  'card'  – compact thumbnail inside a movie card
 *                          'page'  – larger display on the movie detail page
 * @return string           HTML string (not escaped; safe to echo directly).
 */
function renderPosterImg(array $movie, string $context = 'card'): string
{
    $url   = getMoviePoster($movie);
    $title = htmlspecialchars($movie['Title'] ?? '');
    $year  = htmlspecialchars($movie['ReleaseYear'] ?? '');
    $genre = htmlspecialchars(explode(',', $movie['Genre'] ?? '')[0]);

    if ($context === 'page') {
        if ($url !== null) {
            return '<img src="' . htmlspecialchars($url) . '" alt="' . $title . ' poster"
                        class="poster-img"
                        onerror="this.style.display=\'none\';
                                 this.nextElementSibling.style.display=\'flex\';">'
                 . '<div class="poster-fallback" style="display:none;">'
                 .   '<div class="poster-fallback-genre">' . $genre . '</div>'
                 .   '<div class="poster-fallback-title">' . $title . '</div>'
                 .   '<div class="poster-fallback-year">' . $year . '</div>'
                 . '</div>';
        }
        return '<div class="poster-fallback">'
             .   '<div class="poster-fallback-genre">' . $genre . '</div>'
             .   '<div class="poster-fallback-title">' . $title . '</div>'
             .   '<div class="poster-fallback-year">' . $year . '</div>'
             . '</div>';
    }

    // 'card' context — image fills card, fallback is a styled solid block
    if ($url !== null) {
        return '<img src="' . htmlspecialchars($url) . '" alt="' . $title . ' poster"
                    class="card-poster-img"
                    onerror="this.style.display=\'none\';
                             this.nextElementSibling.style.display=\'flex\';">'
             . '<div class="card-poster-fallback" style="display:none;">'
             .   '<div class="card-fallback-genre">' . $genre . '</div>'
             .   '<div class="card-fallback-title">' . $title . '</div>'
             . '</div>';
    }

    return '<div class="card-poster-fallback">'
         .   '<div class="card-fallback-genre">' . $genre . '</div>'
         .   '<div class="card-fallback-title">' . $title . '</div>'
         . '</div>';
}
