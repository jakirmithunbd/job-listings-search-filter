<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Date_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-date';
    }

    public function get_title() {
        return esc_html__('Job Posted Date', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'format',
            [
                'label' => esc_html__('Format', 'job-listings-search-filter'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'default' => esc_html__('Default', 'job-listings-search-filter'),
                    'relative' => esc_html__('Relative (e.g., 2 days ago)', 'job-listings-search-filter'),
                    'custom' => esc_html__('Custom', 'job-listings-search-filter'),
                ],
                'default' => 'relative',
            ]
        );

        $this->add_control(
            'custom_format',
            [
                'label' => esc_html__('Custom Format', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => 'F j, Y',
                'condition' => [
                    'format' => 'custom',
                ],
            ]
        );
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $settings = $this->get_settings();
        $format = $settings['format'];

        if ('relative' === $format) {
            echo sprintf(
                esc_html__('Posted %s ago', 'job-listings-search-filter'),
                human_time_diff(get_post_time('U', false, $post_id), current_time('timestamp'))
            );
        } elseif ('custom' === $format) {
            echo get_the_date($settings['custom_format'], $post_id);
        } else {
            echo get_the_date('', $post_id);
        }
    }
}
