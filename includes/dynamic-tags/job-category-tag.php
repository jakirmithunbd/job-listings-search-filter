<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Category_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-category';
    }

    public function get_title() {
        return esc_html__('Job Category', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'separator',
            [
                'label' => esc_html__('Separator', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => ', ',
            ]
        );
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $terms = get_the_terms($post_id, 'job_listing_category');
        
        if (empty($terms) || is_wp_error($terms)) {
            return;
        }

        $settings = $this->get_settings();
        $separator = $settings['separator'];
        
        $categories = array_map(function($term) {
            return esc_html($term->name);
        }, $terms);

        echo implode($separator, $categories);
    }
}
