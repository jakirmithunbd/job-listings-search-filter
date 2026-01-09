<?php
namespace Job_Listings_Search_Filter\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Company_Tag extends Tag {

    public function get_name() {
        return 'wpjm-job-company';
    }

    public function get_title() {
        return esc_html__('Company Name', 'job-listings-search-filter');
    }

    public function get_group() {
        return 'wpjm-tags';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY, Module::URL_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'link_to_website',
            [
                'label' => esc_html__('Link to Company Website', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );
    }

    public function get_value(array $options = []) {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return '';
        }

        $company = get_post_meta($post_id, '_company_name', true);
        
        return $company ? $company : '';
    }

    public function render() {
        $post_id = get_the_ID();
        
        if (!$post_id || get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $company = get_post_meta($post_id, '_company_name', true);
        
        if (empty($company)) {
            return;
        }

        $settings = $this->get_settings();

        if ('yes' === $settings['link_to_website']) {
            $website = get_post_meta($post_id, '_company_website', true);
            if (!empty($website)) {
                echo '<a href="' . esc_url($website) . '" target="_blank" rel="nofollow">' . esc_html($company) . '</a>';
                return;
            }
        }
        
        echo esc_html($company);
    }
}
