<?php
if (!defined('ABSPATH')) exit;

class RR_NHLScores_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct('rr_nhl_scores_widget', 'Rigged Roster: NHL Scores', [
            'description' => 'Displays NHL scores (same output as [nhl_scores]).'
        ]);
    }
    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo do_shortcode('[nhl_scores]');
        echo $args['after_widget'];
    }
    public function form($instance) {
        echo '<p>No settings yet. Uses global plugin settings.</p>';
    }
}

add_action('widgets_init', function () {
    register_widget('RR_NHLScores_Widget');
});
