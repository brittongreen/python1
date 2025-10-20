<?php
if (!defined('ABSPATH')) exit;

function rr_core_shortcode_nhl_scores($atts = []) {
    $attributes = shortcode_atts([
        'date' => '', // YYYY-MM-DD
    ], $atts, 'nhl_scores');

    $timestamp = current_time('timestamp');
    if (!empty($attributes['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $attributes['date'])) {
        $parsed = strtotime($attributes['date'] . ' 00:00:00');
        if ($parsed !== false) {
            $timestamp = $parsed;
        }
    }

    $espnDate = gmdate('Ymd', $timestamp);
    $cache_key = 'rr_core_nhl_scores_' . $espnDate;

    $ttl_seconds = function_exists('rr_core_get_cache_ttl_seconds') ? rr_core_get_cache_ttl_seconds() : 600;

    if ($ttl_seconds > 0) {
        $cached = get_transient($cache_key);
        if (!empty($cached)) {
            return $cached;
        }
    }

    $endpoint = 'https://site.web.api.espn.com/apis/v2/sports/hockey/nhl/scoreboard?dates=' . rawurlencode($espnDate);
    $response = wp_remote_get($endpoint, [
        'timeout' => 12,
        'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'RiggedRosterCore/1.0 (+https://riggedroster.com)'
        ],
    ]);

    if (is_wp_error($response)) {
        return '<div class="rr-nhl-scores rr-error">' . esc_html__('Unable to load NHL scores at this time.', 'riggedroster-core') . '</div>';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data) || empty($data['events']) || !is_array($data['events'])) {
        return '<div class="rr-nhl-scores rr-empty">' . esc_html__('No NHL games found for this date.', 'riggedroster-core') . '</div>';
    }

    $html_parts = [];
    $html_parts[] = '<div class="rr-nhl-scores" data-date="' . esc_attr(gmdate('Y-m-d', $timestamp)) . '">';

    foreach ($data['events'] as $event) {
        if (empty($event['competitions'][0]['competitors'])) continue;
        $competitors = $event['competitions'][0]['competitors'];

        $home = null; $away = null;
        foreach ($competitors as $team) {
            if (!isset($team['homeAway'])) continue;
            if ($team['homeAway'] === 'home') $home = $team;
            if ($team['homeAway'] === 'away') $away = $team;
        }
        if (!$home || !$away) continue;

        $home_name  = isset($home['team']['abbreviation']) ? $home['team']['abbreviation'] : ($home['team']['displayName'] ?? 'Home');
        $away_name  = isset($away['team']['abbreviation']) ? $away['team']['abbreviation'] : ($away['team']['displayName'] ?? 'Away');
        $home_score = isset($home['score']) ? (string) $home['score'] : '';
        $away_score = isset($away['score']) ? (string) $away['score'] : '';

        $status = $event['status']['type']['shortDetail'] ?? ($event['status']['type']['description'] ?? '');
        $status_slug = strtolower(str_replace(' ', '-', $event['status']['type']['name'] ?? ''));

        $html_parts[] = '<div class="rr-nhl-game rr-status-' . esc_attr($status_slug) . '">'
            . '<span class="rr-team rr-away">' . esc_html($away_name) . '</span>'
            . '<span class="rr-score rr-away">' . esc_html($away_score) . '</span>'
            . '<span class="rr-team rr-home">' . esc_html($home_name) . '</span>'
            . '<span class="rr-score rr-home">' . esc_html($home_score) . '</span>'
            . '<span class="rr-status">' . esc_html($status) . '</span>'
            . '</div>';
    }

    $html_parts[] = '</div>';
    $html = implode('', $html_parts);

    if ($ttl_seconds > 0) {
        set_transient($cache_key, $html, $ttl_seconds);
    }

    return $html;
}
add_shortcode('nhl_scores', 'rr_core_shortcode_nhl_scores');
