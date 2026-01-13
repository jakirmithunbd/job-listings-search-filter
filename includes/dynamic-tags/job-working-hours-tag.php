<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Working_Hours_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-working-hours';
    }

    public function get_title() {
        return esc_html__('Working Hours', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'fallback_text',
            [
                'label' => esc_html__('Fallback Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'description' => esc_html__('Text to display if working hours are not set', 'job-listings-search-filter'),
            ]
        );
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $working_hours = get_post_meta($post_id, '_job_working_hours', true);
        
        if (empty($working_hours)) {
            $settings = $this->get_settings();
            $fallback = $settings['fallback_text'];
            if (!empty($fallback)) {
                echo esc_html($fallback);
            }
            return;
        }

        echo esc_html($working_hours);
    }
}
