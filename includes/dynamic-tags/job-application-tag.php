<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Application_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-application';
    }

    public function get_title() {
        return esc_html__('Application URL/Email', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY, Module::URL_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'display',
            [
                'label' => esc_html__('Display', 'job-listings-search-filter'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'url' => esc_html__('URL/Email', 'job-listings-search-filter'),
                    'button' => esc_html__('Button Text', 'job-listings-search-filter'),
                ],
                'default' => 'button',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => esc_html__('Button Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Apply Now', 'job-listings-search-filter'),
                'condition' => [
                    'display' => 'button',
                ],
            ]
        );
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $application = get_post_meta($post_id, '_application', true);
        
        if (empty($application)) {
            return;
        }

        $settings = $this->get_settings();

        if ('button' === $settings['display']) {
            if (is_email($application)) {
                echo '<a href="mailto:' . esc_attr($application) . '">' . esc_html($settings['button_text']) . '</a>';
            } else {
                echo '<a href="' . esc_url($application) . '" target="_blank" rel="nofollow">' . esc_html($settings['button_text']) . '</a>';
            }
        } else {
            if (is_email($application)) {
                echo '<a href="mailto:' . esc_attr($application) . '">' . esc_html($application) . '</a>';
            } else {
                echo esc_html($application);
            }
        }
    }
}
