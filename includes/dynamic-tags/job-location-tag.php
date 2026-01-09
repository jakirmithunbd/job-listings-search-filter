<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Location_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-location';
    }

    public function get_title() {
        return esc_html__('Job Location', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'link_to_map',
            [
                'label' => esc_html__('Link to Map', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $location = get_post_meta($post_id, '_job_location', true);
        
        if (empty($location)) {
            return;
        }

        $settings = $this->get_settings();

        if ('yes' === $settings['link_to_map']) {
            $map_url = 'https://www.google.com/maps/search/' . urlencode($location);
            echo '<a href="' . esc_url($map_url) . '" target="_blank" rel="nofollow">' . esc_html($location) . '</a>';
        } else {
            echo esc_html($location);
        }
    }
}
