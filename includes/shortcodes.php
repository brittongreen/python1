<?php
if (!defined('ABSPATH')) exit;

/**
 * Helper: fetch with caching
 */
function rr_core_get_json_cached($url, $key, $ttl) {
    $cached = get_transient($key);
    if ($cached !== false) return $cached;

    $resp = wp_remote_get($url, ['timeout' => 10]);
    if (is_wp_error($resp)) return ['error' => $resp->get_error_message()];

    $code = wp_remote_retrieve_response_code($resp);
    if ($code !== 200) return ['error' => 'HTTP ' . $code];

    $json = json_decode(wp_remote_retrieve_body($resp), true);
    if (!is_array($json)) return ['error' => 'Invalid JSON'];

    set_transient($key, $json, $ttl);
    return $json;
}

/**
 * Format a date string (Y-m-d) safely; default to site timezone today.
 */
function rr_sanitize_date_or_today($dateStr) {
    $tz = wp_timezone();
    if (is_string($dateStr) && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $dateStr)) {
        return $dateStr;
    }
    $now = new DateTime('now', $tz);
    return $now->format('Y-m-d');
}

/**
 * Map NHL API status to a short label
 */
function rr_nhl_status_badge($abstractGameState, $detailedState) {
    switch ($abstractGameState) {
        case 'Preview':
            return 'PRE';
        case 'Live':
            // Examples: In Progress, In Progress - Critical
            if ($detailedState === 'In Progress' || strpos($detailedState, 'In Progress') === 0) {
                return 'LIVE';
            }
            return 'LIVE';
        case 'Final':
            return 'FT';
        default:
            return strtoupper(substr($abstractGameState, 0, 3));
    }
}

/**
 * Shortcode: [nhl_scores date="YYYY-MM-DD" limit="5"]
 * Fetches schedule for a given date from NHL Stats API.
 */
function rr_nhl_scores_shortcode($atts = []) {
    $atts = shortcode_atts([
        'limit' => 5,
        'date' => '',
    ], $atts, 'nhl_scores');

    $limit = (int) $atts['limit'];
    if ($limit <= 0) $limit = 5;

    $date = rr_sanitize_date_or_today($atts['date']);

    $ttl = (int) get_option('rr_cache_ttl', 900);

    // NHL Stats API schedule endpoint
    // Docs: https://statsapi.web.nhl.com/api/v1/schedule?date=YYYY-MM-DD
    $endpoint = 'https://statsapi.web.nhl.com/api/v1/schedule?date=' . rawurlencode($date);

    // Include the date in the cache key to avoid collisions across dates
    $cache_key = 'rr_nhl_scores_' . md5($endpoint);

    $data = rr_core_get_json_cached($endpoint, $cache_key, $ttl);

    ob_start();
    echo '<div class="rr-box"><h3 class="rr-title">NHL Scores (' . esc_html($date) . ")</h3>";

    if (isset($data['error'])) {
        echo '<div class="rr-error">Error: ' . esc_html($data['error']) . '</div>';
        echo '</div>';
        return ob_get_clean();
    }

    $games = [];
    if (isset($data['dates']) && is_array($data['dates']) && !empty($data['dates'][0]['games'])) {
        $games = $data['dates'][0]['games'];
    }

    if (empty($games)) {
        echo '<div class="rr-empty">No games scheduled.</div>';
        echo '</div>';
        return ob_get_clean();
    }

    echo '<ul class="rr-list">';
    $count = 0;
    foreach ($games as $g) {
        if ($count++ >= $limit) break;

        $status = isset($g['status']) ? $g['status'] : [];
        $badge = rr_nhl_status_badge(
            isset($status['abstractGameState']) ? $status['abstractGameState'] : '',
            isset($status['detailedState']) ? $status['detailedState'] : ''
        );

        $teams = isset($g['teams']) ? $g['teams'] : [];
        $away = isset($teams['away']) ? $teams['away'] : [];
        $home = isset($teams['home']) ? $teams['home'] : [];

        $awayName = isset($away['team']['name']) ? $away['team']['name'] : 'Away';
        $homeName = isset($home['team']['name']) ? $home['team']['name'] : 'Home';

        $awayScore = isset($away['score']) ? (int)$away['score'] : 0;
        $homeScore = isset($home['score']) ? (int)$home['score'] : 0;

        $line = $awayName . ' ' . $awayScore . ' @ ' . $homeName . ' ' . $homeScore;

        echo '<li class="rr-item"><span class="rr-badge">' . esc_html($badge) . '</span> ' . esc_html($line) . '</li>';
    }
    echo '</ul>';

    echo '</div>';
    return ob_get_clean();
}
add_shortcode('nhl_scores', 'rr_nhl_scores_shortcode');
