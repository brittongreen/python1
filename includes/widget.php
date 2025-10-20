<?php
if (!defined('ABSPATH')) exit;

class RR_NHL_Scores_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'rr_nhl_scores_widget',
            __('NHL Scores (Rigged Roster)', 'riggedroster-core'),
            ['description' => __('Shows NHL scores for a date', 'riggedroster-core')]
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        $title = isset($instance['title']) ? $instance['title'] : __('NHL Scores', 'riggedroster-core');
        if (!empty($title)) {
            echo $args['before_title'] . apply_filters('widget_title', esc_html($title)) . $args['after_title'];
        }
        $date = isset($instance['date']) ? $instance['date'] : '';
        $shortcode = '[nhl_scores' . (!empty($date) ? ' date="' . esc_attr($date) . '"' : '') . ']';
        echo do_shortcode($shortcode);
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : __('NHL Scores', 'riggedroster-core');
        $date  = isset($instance['date']) ? $instance['date'] : '';
        $title_id = $this->get_field_id('title');
        $title_name = $this->get_field_name('title');
        $date_id = $this->get_field_id('date');
        $date_name = $this->get_field_name('date');
        ?>
        <p>
            <label for="<?php echo esc_attr($title_id); ?>"><?php esc_html_e('Title:', 'riggedroster-core'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($title_id); ?>" name="<?php echo esc_attr($title_name); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($date_id); ?>"><?php esc_html_e('Date (YYYY-MM-DD). Leave blank for today.', 'riggedroster-core'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($date_id); ?>" name="<?php echo esc_attr($date_name); ?>" type="text" value="<?php echo esc_attr($date); ?>" placeholder="YYYY-MM-DD">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $raw_date = isset($new_instance['date']) ? trim($new_instance['date']) : '';
        $instance['date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_date) ? $raw_date : '';
        return $instance;
    }
}

add_action('widgets_init', function () {
    register_widget('RR_NHL_Scores_Widget');
});
