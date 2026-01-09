<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Title_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-title';
    }

    public function get_title() {
        return esc_html__('Job Title', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY, Module::URL_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'link',
            [
                'label' => esc_html__('Link', 'job-listings-search-filter'),
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

        $title = get_the_title($post_id);
        $settings = $this->get_settings();

        if ('yes' === $settings['link']) {
            $url = get_permalink($post_id);
            echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        } else {
            echo esc_html($title);
        }
    }
}
