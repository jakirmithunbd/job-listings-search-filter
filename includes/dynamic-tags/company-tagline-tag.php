<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Company_Tagline_Tag extends Tag {

    public function get_name() {
        return 'wpjm-company-tagline';
    }

    public function get_title() {
        return esc_html__('Company Tagline', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'tagline_fallback',
            [
                'label' => esc_html__('Fallback Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $tagline = get_post_meta($post_id, '_company_tagline', true);
        
        if (empty($tagline)) {
            $settings = $this->get_settings();
            if (!empty($settings['tagline_fallback'])) {
                echo esc_html($settings['tagline_fallback']);
            }
            return;
        }

        echo esc_html($tagline);
    }
}
