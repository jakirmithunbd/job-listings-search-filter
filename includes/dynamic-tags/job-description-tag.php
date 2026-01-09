<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Description_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-description';
    }

    public function get_title() {
        return esc_html__('Job Description', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'type',
            [
                'label' => esc_html__('Type', 'job-listings-search-filter'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'full' => esc_html__('Full Description', 'job-listings-search-filter'),
                    'excerpt' => esc_html__('Excerpt', 'job-listings-search-filter'),
                ],
                'default' => 'excerpt',
            ]
        );

        $this->add_control(
            'length',
            [
                'label' => esc_html__('Excerpt Length (words)', 'job-listings-search-filter'),
                'type' => Controls_Manager::NUMBER,
                'default' => 30,
                'condition' => [
                    'type' => 'excerpt',
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
        
        if ('full' === $settings['type']) {
            echo wpautop(get_the_content(null, false, $post_id));
        } else {
            $content = get_the_content(null, false, $post_id);
            $content = wp_strip_all_tags($content);
            $words = explode(' ', $content, $settings['length'] + 1);
            
            if (count($words) > $settings['length']) {
                array_pop($words);
                $content = implode(' ', $words) . '...';
            } else {
                $content = implode(' ', $words);
            }
            
            echo esc_html($content);
        }
    }
}
