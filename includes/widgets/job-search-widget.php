<?php
namespace Job_Listings_Search_Filter\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Job_Search_Widget extends Widget_Base {

    public function get_name() {
        return 'wpjm_job_search';
    }

    public function get_title() {
        return esc_html__('Job Search Filter', 'job-listings-search-filter');
    }

    public function get_icon() {
        return 'eicon-search';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_keywords() {
        return ['job', 'search', 'filter', 'wp job manager', 'jobs'];
    }
    
    public function get_style_depends() {
        return ['wpjm-job-search-widget'];
    }
    
    public function get_script_depends() {
        return ['wpjm-job-search-widget'];
    }
    
    /**
     * Get count of jobs for a specific filter value
     */
    private function get_job_count($filter_type, $filter_value, $current_filters = []) {
        $args = [
            'post_type' => 'job_listing',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];
        
        // Apply current filters except the one we're counting
        $meta_query = [];
        $tax_query = [];
        
        if (!empty($current_filters['search_location']) && $filter_type !== 'location') {
            $meta_query[] = [
                'key' => '_job_location',
                'value' => $current_filters['search_location'],
                'compare' => 'LIKE'
            ];
        }
        
        if (!empty($current_filters['job_type']) && $filter_type !== 'job_type') {
            $job_types = is_array($current_filters['job_type']) ? $current_filters['job_type'] : explode(',', $current_filters['job_type']);
            $tax_query[] = [
                'taxonomy' => 'job_listing_type',
                'field' => 'slug',
                'terms' => $job_types,
                'operator' => 'IN'
            ];
        }
        
        if (!empty($current_filters['category']) && $filter_type !== 'category') {
            $categories = is_array($current_filters['category']) ? $current_filters['category'] : explode(',', $current_filters['category']);
            $tax_query[] = [
                'taxonomy' => 'job_listing_category',
                'field' => 'slug',
                'terms' => $categories,
                'operator' => 'IN'
            ];
        }
        
        if (!empty($current_filters['working_hours']) && $filter_type !== 'working_hours') {
            $meta_query[] = [
                'key' => '_job_working_hours',
                'value' => $current_filters['working_hours'],
                'compare' => 'LIKE'
            ];
        }
        
        // Add the filter we're counting
        switch ($filter_type) {
            case 'location':
                $meta_query[] = [
                    'key' => '_job_location',
                    'value' => $filter_value,
                    'compare' => 'LIKE'
                ];
                break;
            case 'job_type':
                $tax_query[] = [
                    'taxonomy' => 'job_listing_type',
                    'field' => 'slug',
                    'terms' => $filter_value
                ];
                break;
            case 'category':
                $tax_query[] = [
                    'taxonomy' => 'job_listing_category',
                    'field' => 'slug',
                    'terms' => $filter_value
                ];
                break;
            case 'working_hours':
                $meta_query[] = [
                    'key' => '_job_working_hours',
                    'value' => $filter_value,
                    'compare' => 'LIKE'
                ];
                break;
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }
        
        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    protected function register_controls() {
        // Filter Settings Section
        $this->start_controls_section(
            'filter_settings_section',
            [
                'label' => esc_html__('Filter Settings', 'job-listings-search-filter'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_search',
            [
                'label' => esc_html__('Show Search Field', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'search_label',
            [
                'label' => esc_html__('Search Field Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Search by keyword', 'job-listings-search-filter'),
                'condition' => [
                    'show_search' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'search_placeholder',
            [
                'label' => esc_html__('Search Field Placeholder', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('What vacancy are you looking for?', 'job-listings-search-filter'),
                'condition' => [
                    'show_search' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_positions',
            [
                'label' => esc_html__('Show Positions Filter', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'positions_label',
            [
                'label' => esc_html__('Positions Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Function', 'job-listings-search-filter'),
                'condition' => [
                    'show_positions' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_location',
            [
                'label' => esc_html__('Show Location Filter', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'location_label',
            [
                'label' => esc_html__('Location Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Location', 'job-listings-search-filter'),
                'condition' => [
                    'show_location' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_types',
            [
                'label' => esc_html__('Show Contract Types Filter', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'types_label',
            [
                'label' => esc_html__('Contract Types Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Contract type', 'job-listings-search-filter'),
                'condition' => [
                    'show_types' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_category',
            [
                'label' => esc_html__('Show Category Filter', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'category_label',
            [
                'label' => esc_html__('Category Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Category', 'job-listings-search-filter'),
                'condition' => [
                    'show_category' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_date_posted',
            [
                'label' => esc_html__('Show Date Posted Filter', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'date_posted_label',
            [
                'label' => esc_html__('Date Posted Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Date Posted', 'job-listings-search-filter'),
                'condition' => [
                    'show_date_posted' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_job_status',
            [
                'label' => esc_html__('Show Job Status Filters', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'job_status_label',
            [
                'label' => esc_html__('Job Status Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Job Status', 'job-listings-search-filter'),
                'condition' => [
                    'show_job_status' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_working_hours',
            [
                'label' => esc_html__('Show Working Hours Filter', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'working_hours_label',
            [
                'label' => esc_html__('Working Hours Label', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Working Hours', 'job-listings-search-filter'),
                'condition' => [
                    'show_working_hours' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_job_counts',
            [
                'label' => esc_html__('Show Job Counts', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Display number of jobs for each filter option', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'show_active_filters',
            [
                'label' => esc_html__('Show Active Filters', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Display selected filters with remove option', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'show_no_results',
            [
                'label' => esc_html__('Show No Results Message', 'job-listings-search-filter'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'job-listings-search-filter'),
                'label_off' => esc_html__('Hide', 'job-listings-search-filter'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Display message when no jobs are found', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'no_results_message',
            [
                'label' => esc_html__('No Results Message', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => esc_html__('No jobs found matching your criteria. Please try adjusting your filters.', 'job-listings-search-filter'),
                'condition' => [
                    'show_no_results' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'target_query_id',
            [
                'label' => esc_html__('Target Loop Grid Query ID', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => 'job_listings',
                'description' => esc_html__('Enter the Query ID of the Loop Grid widget you want to filter (set in Loop Grid → Query → Query ID)', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'layout_style',
            [
                'label' => esc_html__('Layout Style', 'job-listings-search-filter'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'sidebar' => esc_html__('Sidebar (Vertical)', 'job-listings-search-filter'),
                    'inline' => esc_html__('Inline (Horizontal)', 'job-listings-search-filter'),
                ],
                'default' => 'sidebar',
            ]
        );
        
        $this->add_control(
            'search_type',
            [
                'label' => esc_html__('Search Type', 'job-listings-search-filter'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'ajax' => esc_html__('AJAX (Instant Results)', 'job-listings-search-filter'),
                    'page_reload' => esc_html__('Page Reload', 'job-listings-search-filter'),
                ],
                'default' => 'ajax',
            ]
        );

        $this->end_controls_section();
        
        // Text Labels Section
        $this->start_controls_section(
            'text_labels_section',
            [
                'label' => esc_html__('Text Labels', 'job-listings-search-filter'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'text_all_categories',
            [
                'label' => esc_html__('All Categories Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('All Categories', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'text_all_locations',
            [
                'label' => esc_html__('All Locations Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('All Locations', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'text_all_types',
            [
                'label' => esc_html__('All Types Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('All Types', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'text_clear',
            [
                'label' => esc_html__('Clear Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Clear', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'text_clear_filters',
            [
                'label' => esc_html__('Clear Filters Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Clear filters', 'job-listings-search-filter'),
            ]
        );
        
        $this->add_control(
            'text_search_button',
            [
                'label' => esc_html__('Search Button Text', 'job-listings-search-filter'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Search Vacancies', 'job-listings-search-filter'),
            ]
        );

        $this->end_controls_section();

        // Style Section - Form
        $this->start_controls_section(
            'style_form_section',
            [
                'label' => esc_html__('Form Container', 'job-listings-search-filter'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'form_padding',
            [
                'label' => esc_html__('Padding', 'job-listings-search-filter'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_background',
            [
                'label' => esc_html__('Background Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-form' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'form_border',
                'selector' => '{{WRAPPER}} .wpjm-search-form',
            ]
        );

        $this->add_responsive_control(
            'form_border_radius',
            [
                'label' => esc_html__('Border Radius', 'job-listings-search-filter'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Input Fields
        $this->start_controls_section(
            'style_fields_section',
            [
                'label' => esc_html__('Input Fields', 'job-listings-search-filter'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'field_typography',
                'selector' => '{{WRAPPER}} .wpjm-search-field input, {{WRAPPER}} .wpjm-search-field select',
            ]
        );

        $this->add_responsive_control(
            'field_padding',
            [
                'label' => esc_html__('Padding', 'job-listings-search-filter'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-field input, {{WRAPPER}} .wpjm-search-field select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'field_background',
            [
                'label' => esc_html__('Background Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-field input, {{WRAPPER}} .wpjm-search-field select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'field_text_color',
            [
                'label' => esc_html__('Text Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-field input, {{WRAPPER}} .wpjm-search-field select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'field_border',
                'selector' => '{{WRAPPER}} .wpjm-search-field input, {{WRAPPER}} .wpjm-search-field select',
            ]
        );

        $this->add_responsive_control(
            'field_border_radius',
            [
                'label' => esc_html__('Border Radius', 'job-listings-search-filter'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpjm-search-field input, {{WRAPPER}} .wpjm-search-field select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Button
        $this->start_controls_section(
            'style_button_section',
            [
                'label' => esc_html__('Search Button', 'job-listings-search-filter'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .wpjm-btn-primary',
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab(
            'button_normal',
            [
                'label' => esc_html__('Normal', 'job-listings-search-filter'),
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label' => esc_html__('Background Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-btn-primary' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Text Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-btn-primary' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover',
            [
                'label' => esc_html__('Hover', 'job-listings-search-filter'),
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label' => esc_html__('Background Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-btn-primary:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => esc_html__('Text Color', 'job-listings-search-filter'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpjm-btn-primary:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__('Padding', 'job-listings-search-filter'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpjm-btn-primary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .wpjm-btn-primary',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'job-listings-search-filter'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpjm-btn-primary' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $current_url = strtok($_SERVER['REQUEST_URI'], '?');
        $search_keywords = isset($_GET['search_keywords']) ? sanitize_text_field($_GET['search_keywords']) : '';
        $search_location = isset($_GET['search_location']) ? sanitize_text_field($_GET['search_location']) : '';
        $search_job_type = isset($_GET['job_type']) ? sanitize_text_field($_GET['job_type']) : '';
        $search_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $search_date_posted = isset($_GET['date_posted']) ? sanitize_text_field($_GET['date_posted']) : '';
        
        $widget_id = $this->get_id();
        $layout_style = $settings['layout_style'] ?? 'inline';
        
        if ($layout_style === 'inline') {
            $this->render_inline_layout($settings, $widget_id, $search_keywords, $search_location, $search_job_type, $search_category, $search_date_posted, $current_url);
        } else {
            $this->render_sidebar_layout($settings, $widget_id, $search_keywords, $search_location, $search_job_type, $search_category, $search_date_posted, $current_url);
        }
    }
    
    protected function render_inline_layout($settings, $widget_id, $search_keywords, $search_location, $search_job_type, $search_category, $search_date_posted, $current_url) {
        $search_label = !empty($settings['search_label']) ? $settings['search_label'] : esc_html__('Search by keyword', 'job-listings-search-filter');
        $search_placeholder = !empty($settings['search_placeholder']) ? $settings['search_placeholder'] : esc_html__('What vacancy are you looking for?', 'job-listings-search-filter');
        ?>
        <div class="wpjm-filter-widget wpjm-inline-layout" data-widget-id="<?php echo esc_attr($widget_id); ?>" data-search-type="<?php echo esc_attr($settings['search_type']); ?>">
            <form method="get" action="" class="wpjm-filter-form wpjm-ajax-search-form wpjm-inline-form" data-widget-id="<?php echo esc_attr($widget_id); ?>" data-search-type="<?php echo esc_attr($settings['search_type']); ?>">
                
                <div class="wpjm-inline-search-fields">
                    <?php if ($settings['show_search'] === 'yes') : ?>
                    <div class="wpjm-inline-field">
                        <label class="wpjm-inline-label"><?php echo esc_html($search_label); ?></label>
                        <div class="wpjm-inline-input-wrapper">
                            <input 
                                type="text" 
                                name="search_keywords"
                                class="wpjm-inline-input"
                                placeholder="<?php echo esc_attr($search_placeholder); ?>"
                                value="<?php echo esc_attr($search_keywords); ?>"
                            />
                            <button type="submit" class="wpjm-inline-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($settings['show_location'] === 'yes') : ?>
                    <div class="wpjm-inline-field">
                        <label class="wpjm-inline-label"><?php echo esc_html__('Search by location', 'job-listings-search-filter'); ?></label>
                        <div class="wpjm-inline-input-wrapper">
                            <input 
                                type="text" 
                                name="search_location"
                                class="wpjm-inline-input wpjm-location-input"
                                placeholder="<?php echo esc_attr__('What is your address?', 'job-listings-search-filter'); ?>"
                                value="<?php echo esc_attr($search_location); ?>"
                            />
                            <span class="wpjm-location-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </span>
                            <button type="submit" class="wpjm-inline-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($settings['show_job_type'] === 'yes' || $settings['show_category'] === 'yes' || $settings['show_date_posted'] === 'yes') : ?>
                <div class="wpjm-inline-filters-toggle">
                    <a href="#" class="wpjm-toggle-filters">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <?php echo esc_html__('More Filters', 'job-listings-search-filter'); ?>
                    </a>
                </div>
                
                <div class="wpjm-inline-additional-filters" style="display: none;">
                    <?php if ($settings['show_job_type'] === 'yes') : ?>
                    <div class="wpjm-filter-field">
                        <label class="wpjm-filter-label"><?php echo esc_html__('Job Type', 'job-listings-search-filter'); ?></label>
                        <select name="job_type" class="wpjm-filter-select">
                            <option value=""><?php echo esc_html__('All Job Types', 'job-listings-search-filter'); ?></option>
                            <?php
                            $job_types = get_terms([
                                'taxonomy' => 'job_listing_type',
                                'hide_empty' => false,
                            ]);
                            if (!is_wp_error($job_types) && !empty($job_types)) {
                                foreach ($job_types as $type) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($type->slug),
                                        selected($search_job_type, $type->slug, false),
                                        esc_html($type->name)
                                    );
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($settings['show_category'] === 'yes') : ?>
                    <div class="wpjm-filter-field">
                        <label class="wpjm-filter-label"><?php echo esc_html__('Category', 'job-listings-search-filter'); ?></label>
                        <select name="category" class="wpjm-filter-select">
                            <option value=""><?php echo esc_html__('All Categories', 'job-listings-search-filter'); ?></option>
                            <?php
                            $categories = get_terms([
                                'taxonomy' => 'job_listing_category',
                                'hide_empty' => false,
                            ]);
                            if (!is_wp_error($categories) && !empty($categories)) {
                                foreach ($categories as $cat) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($cat->slug),
                                        selected($search_category, $cat->slug, false),
                                        esc_html($cat->name)
                                    );
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($settings['show_date_posted'] === 'yes') : ?>
                    <div class="wpjm-filter-field">
                        <label class="wpjm-filter-label"><?php echo esc_html__('Date Posted', 'job-listings-search-filter'); ?></label>
                        <select name="date_posted" class="wpjm-filter-select">
                            <option value=""><?php echo esc_html__('Anytime', 'job-listings-search-filter'); ?></option>
                            <option value="today" <?php selected($search_date_posted, 'today'); ?>><?php echo esc_html__('Today', 'job-listings-search-filter'); ?></option>
                            <option value="7days" <?php selected($search_date_posted, '7days'); ?>><?php echo esc_html__('Last 7 days', 'job-listings-search-filter'); ?></option>
                            <option value="14days" <?php selected($search_date_posted, '14days'); ?>><?php echo esc_html__('Last 14 days', 'job-listings-search-filter'); ?></option>
                            <option value="30days" <?php selected($search_date_posted, '30days'); ?>><?php echo esc_html__('Last 30 days', 'job-listings-search-filter'); ?></option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="wpjm-filter-actions">
                        <button type="submit" class="wpjm-filter-btn wpjm-filter-btn-primary">
                            <?php echo esc_html__('Apply Filters', 'job-listings-search-filter'); ?>
                        </button>
                        <a href="<?php echo esc_url($current_url); ?>" class="wpjm-filter-btn wpjm-filter-btn-secondary">
                            <?php echo esc_html__('Clear All', 'job-listings-search-filter'); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
        // Add inline script data using WordPress proper method
        $inline_script = sprintf(
            "var wpjmSearchData = wpjmSearchData || {ajaxUrl: '%s', nonce: '%s', targetQueryId: '%s', showNoResults: %s, noResultsMessage: '%s'};",
            esc_js(admin_url('admin-ajax.php')),
            esc_js(wp_create_nonce('wpjm_search_nonce')),
            esc_js($settings['target_query_id'] ?? 'job_listings'),
            ($settings['show_no_results'] === 'yes') ? 'true' : 'false',
            esc_js($settings['no_results_message'] ?? esc_html__('No jobs found matching your criteria.', 'job-listings-search-filter'))
        );
        wp_add_inline_script('wpjm-job-search-widget', $inline_script, 'before');
        ?>
        <?php
    }
    
    protected function render_sidebar_layout($settings, $widget_id, $search_keywords, $search_location, $search_job_type, $search_category, $search_date_posted, $current_url) {
        $search_label = !empty($settings['search_label']) ? $settings['search_label'] : esc_html__('Search by keyword', 'job-listings-search-filter');
        $search_placeholder = !empty($settings['search_placeholder']) ? $settings['search_placeholder'] : esc_html__('What vacancy are you looking for?', 'job-listings-search-filter');
        $positions_label = !empty($settings['positions_label']) ? $settings['positions_label'] : esc_html__('Function', 'job-listings-search-filter');
        $location_label = !empty($settings['location_label']) ? $settings['location_label'] : esc_html__('Location', 'job-listings-search-filter');
        $types_label = !empty($settings['types_label']) ? $settings['types_label'] : esc_html__('Contract type', 'job-listings-search-filter');
        $category_label = !empty($settings['category_label']) ? $settings['category_label'] : esc_html__('Category', 'job-listings-search-filter');
        $date_posted_label = !empty($settings['date_posted_label']) ? $settings['date_posted_label'] : esc_html__('Date Posted', 'job-listings-search-filter');
        $job_status_label = !empty($settings['job_status_label']) ? $settings['job_status_label'] : esc_html__('Job Status', 'job-listings-search-filter');
        $working_hours_label = !empty($settings['working_hours_label']) ? $settings['working_hours_label'] : esc_html__('Working Hours', 'job-listings-search-filter');
        
        // Get text labels
        $text_all_categories = !empty($settings['text_all_categories']) ? $settings['text_all_categories'] : esc_html__('All Categories', 'job-listings-search-filter');
        $text_all_locations = !empty($settings['text_all_locations']) ? $settings['text_all_locations'] : esc_html__('All Locations', 'job-listings-search-filter');
        $text_all_types = !empty($settings['text_all_types']) ? $settings['text_all_types'] : esc_html__('All Types', 'job-listings-search-filter');
        $text_clear = !empty($settings['text_clear']) ? $settings['text_clear'] : esc_html__('Clear', 'job-listings-search-filter');
        $text_clear_filters = !empty($settings['text_clear_filters']) ? $settings['text_clear_filters'] : esc_html__('Clear filters', 'job-listings-search-filter');
        $text_search_button = !empty($settings['text_search_button']) ? $settings['text_search_button'] : esc_html__('Search Vacancies', 'job-listings-search-filter');
        
        $show_counts = ($settings['show_job_counts'] === 'yes');
        $show_active_filters = ($settings['show_active_filters'] === 'yes');
        
        // Get current filters
        $selected_job_types = isset($_GET['job_type']) ? (is_array($_GET['job_type']) ? $_GET['job_type'] : explode(',', $_GET['job_type'])) : [];
        $selected_locations = isset($_GET['search_location']) ? (is_array($_GET['search_location']) ? $_GET['search_location'] : [$_GET['search_location']]) : [];
        $selected_categories = isset($_GET['category']) ? (is_array($_GET['category']) ? $_GET['category'] : explode(',', $_GET['category'])) : [];
        $selected_working_hours = isset($_GET['working_hours']) ? (is_array($_GET['working_hours']) ? $_GET['working_hours'] : [$_GET['working_hours']]) : [];
        
        $current_filters = [
            'search_location' => implode(',', $selected_locations),
            'job_type' => implode(',', $selected_job_types),
            'category' => implode(',', $selected_categories),
            'working_hours' => implode(',', $selected_working_hours),
        ];
        
        ?>
        <div class="wpjm-filter-widget wpjm-sidebar-layout" data-widget-id="<?php echo esc_attr($widget_id); ?>" data-search-type="<?php echo esc_attr($settings['search_type']); ?>">
            
            <?php 
            $has_hour_range = isset($_GET['min_hours']) || isset($_GET['max_hours']);
            if ($show_active_filters && (count($selected_job_types) > 0 || count($selected_locations) > 0 || count($selected_categories) > 0 || count($selected_working_hours) > 0 || $has_hour_range || !empty($search_keywords))) : 
            ?>
            <div class="wpjm-active-filters">
                <div class="wpjm-active-filters-header">
                    <span class="wpjm-active-filters-title"><?php echo esc_html__('Active Filters', 'job-listings-search-filter'); ?></span>
                    <a href="<?php echo esc_url($current_url); ?>" class="wpjm-clear-all"><?php echo esc_html($text_clear_filters); ?></a>
                </div>
                <div class="wpjm-active-filters-list">
                    <?php if (!empty($search_keywords)) : ?>
                    <span class="wpjm-active-filter-tag" data-filter="search_keywords">
                        <?php echo esc_html($search_keywords); ?>
                        <button type="button" class="wpjm-remove-filter" data-param="search_keywords">×</button>
                    </span>
                    <?php endif; ?>
                    
                    <?php 
                    // Job Types
                    if (count($selected_job_types) > 0) {
                        $job_types = get_terms(['taxonomy' => 'job_listing_type', 'hide_empty' => false]);
                        foreach ($job_types as $type) {
                            if (in_array($type->slug, $selected_job_types)) {
                                printf(
                                    '<span class="wpjm-active-filter-tag" data-filter="job_type" data-value="%s">%s<button type="button" class="wpjm-remove-filter" data-param="job_type" data-value="%s">×</button></span>',
                                    esc_attr($type->slug),
                                    esc_html($type->name),
                                    esc_attr($type->slug)
                                );
                            }
                        }
                    }
                    
                    // Locations
                    foreach ($selected_locations as $location) {
                        if (!empty($location)) {
                            printf(
                                '<span class="wpjm-active-filter-tag" data-filter="search_location" data-value="%s">%s<button type="button" class="wpjm-remove-filter" data-param="search_location" data-value="%s">×</button></span>',
                                esc_attr($location),
                                esc_html($location),
                                esc_attr($location)
                            );
                        }
                    }
                    
                    // Categories
                    if (count($selected_categories) > 0) {
                        $categories = get_terms(['taxonomy' => 'job_listing_category', 'hide_empty' => false]);
                        foreach ($categories as $cat) {
                            if (in_array($cat->slug, $selected_categories)) {
                                printf(
                                    '<span class="wpjm-active-filter-tag" data-filter="category" data-value="%s">%s<button type="button" class="wpjm-remove-filter" data-param="category" data-value="%s">×</button></span>',
                                    esc_attr($cat->slug),
                                    esc_html($cat->name),
                                    esc_attr($cat->slug)
                                );
                            }
                        }
                    }
                    
                    // Working Hours - show range if set
                    $min_hours_filter = isset($_GET['min_hours']) ? intval($_GET['min_hours']) : null;
                    $max_hours_filter = isset($_GET['max_hours']) ? intval($_GET['max_hours']) : null;
                    
                    if ($min_hours_filter !== null || $max_hours_filter !== null) {
                        $hours_text = '';
                        if ($min_hours_filter !== null && $max_hours_filter !== null) {
                            $hours_text = $min_hours_filter . '-' . $max_hours_filter . ' uren';
                        } elseif ($min_hours_filter !== null) {
                            $hours_text = $min_hours_filter . '+ uren';
                        } elseif ($max_hours_filter !== null) {
                            $hours_text = 'Tot ' . $max_hours_filter . ' uren';
                        }
                        
                        if (!empty($hours_text)) {
                            echo '<span class="wpjm-active-filter-tag" data-filter="working_hours_range">';
                            echo esc_html($hours_text);
                            echo '<button type="button" class="wpjm-remove-filter" data-param="min_hours,max_hours">×</button>';
                            echo '</span>';
                        }
                    } else {
                        // Legacy: show old format working hours
                        foreach ($selected_working_hours as $hours) {
                            if (!empty($hours)) {
                                printf(
                                    '<span class="wpjm-active-filter-tag" data-filter="working_hours" data-value="%s">%s<button type="button" class="wpjm-remove-filter" data-param="working_hours" data-value="%s">×</button></span>',
                                    esc_attr($hours),
                                    esc_html($hours),
                                    esc_attr($hours)
                                );
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="wpjm-filter-header">
                <a href="<?php echo esc_url($current_url); ?>" class="wpjm-filter-erase"><?php echo esc_html($text_clear); ?></a>
            </div>
            
            <form method="get" action="" class="wpjm-filter-form wpjm-ajax-search-form" data-widget-id="<?php echo esc_attr($widget_id); ?>" data-search-type="<?php echo esc_attr($settings['search_type']); ?>">
                
                <?php if ($settings['show_search'] === 'yes') : ?>
                <div class="wpjm-filter-field">
                    <label class="wpjm-filter-label"><?php echo esc_html($search_label); ?></label>
                    <input 
                        type="text" 
                        name="search_keywords"
                        class="wpjm-filter-input"
                        placeholder="<?php echo esc_attr($search_placeholder); ?>"
                        value="<?php echo esc_attr($search_keywords); ?>"
                    />
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_positions'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-checkbox-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($positions_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                    <div class="wpjm-checkbox-options">
                        <?php
                        $job_types = get_terms([
                            'taxonomy' => 'job_listing_type',
                            'hide_empty' => false,
                        ]);
                        if (!is_wp_error($job_types) && !empty($job_types)) {
                            foreach ($job_types as $type) {
                                $count_html = '';
                                if ($show_counts) {
                                    $count = $this->get_job_count('job_type', $type->slug, $current_filters);
                                    $count_html = ' <span class="wpjm-filter-count">(' . $count . ')</span>';
                                }
                                printf(
                                    '<label class="wpjm-checkbox-option"><input type="checkbox" name="job_type[]" value="%s" %s><span>%s%s</span></label>',
                                    esc_attr($type->slug),
                                    in_array($type->slug, $selected_job_types) ? 'checked' : '',
                                    esc_html($type->name),
                                    $count_html
                                );
                            }
                        }
                        ?>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_location'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-checkbox-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($location_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                    <div class="wpjm-checkbox-options">
                        <?php
                        // Get unique locations from job posts
                        global $wpdb;
                        $locations = $wpdb->get_col("
                            SELECT DISTINCT meta_value 
                            FROM {$wpdb->postmeta} 
                            WHERE meta_key = '_job_location' 
                            AND meta_value != '' 
                            ORDER BY meta_value ASC 
                            LIMIT 20
                        ");
                        if (!empty($locations)) {
                            foreach ($locations as $location) {
                                $count_html = '';
                                if ($show_counts) {
                                    $count = $this->get_job_count('location', $location, $current_filters);
                                    $count_html = ' <span class="wpjm-filter-count">(' . $count . ')</span>';
                                }
                                printf(
                                    '<label class="wpjm-checkbox-option"><input type="checkbox" name="search_location[]" value="%s" %s><span>%s%s</span></label>',
                                    esc_attr($location),
                                    in_array($location, $selected_locations) ? 'checked' : '',
                                    esc_html($location),
                                    $count_html
                                );
                            }
                        }
                        ?>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_types'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-checkbox-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($types_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                    <div class="wpjm-checkbox-options">
                        <?php
                        $search_remote = isset($_GET['remote_position']) ? sanitize_text_field($_GET['remote_position']) : '';
                        ?>
                        <label class="wpjm-checkbox-option">
                            <input type="checkbox" name="remote_position" value="1" <?php checked($search_remote, '1'); ?>>
                            <span><?php echo esc_html__('Remote Position', 'job-listings-search-filter'); ?></span>
                        </label>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_category'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-checkbox-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($category_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                    <div class="wpjm-checkbox-options">
                        <?php
                        $categories = get_terms([
                            'taxonomy' => 'job_listing_category',
                            'hide_empty' => false,
                        ]);
                        if (!is_wp_error($categories) && !empty($categories)) {
                            foreach ($categories as $cat) {
                                $count_html = '';
                                if ($show_counts) {
                                    $count = $this->get_job_count('category', $cat->slug, $current_filters);
                                    $count_html = ' <span class="wpjm-filter-count">(' . $count . ')</span>';
                                }
                                printf(
                                    '<label class="wpjm-checkbox-option"><input type="checkbox" name="category[]" value="%s" %s><span>%s%s</span></label>',
                                    esc_attr($cat->slug),
                                    in_array($cat->slug, $selected_categories) ? 'checked' : '',
                                    esc_html($cat->name),
                                    $count_html
                                );
                            }
                        }
                        ?>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_working_hours'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-hour-range-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($working_hours_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                        <?php
                        // Get min and max working hours from database
                        global $wpdb;
                        $max_hours = $wpdb->get_var("
                            SELECT MAX(CAST(meta_value AS UNSIGNED))
                            FROM {$wpdb->postmeta} 
                            WHERE meta_key = '_job_working_hours' 
                            AND meta_value != ''
                            AND meta_value REGEXP '^[0-9]+$'
                        ");
                        
                        if (empty($max_hours) || $max_hours < 40) {
                            $max_hours = 40; // Default max hours
                        }
                        
                        // Get current values from URL
                        $min_hours = isset($_GET['min_hours']) ? intval($_GET['min_hours']) : 0;
                        $max_hours_selected = isset($_GET['max_hours']) ? intval($_GET['max_hours']) : $max_hours;
                        ?>
                        <div class="wpjm-hour-range-container">
                            <div class="wpjm-hour-inputs">
                                <input type="number" 
                                       name="min_hours" 
                                       class="wpjm-hour-input wpjm-hour-min" 
                                       value="<?php echo esc_attr($min_hours); ?>" 
                                       min="0" 
                                       max="<?php echo esc_attr($max_hours); ?>"
                                       readonly />
                                <span class="wpjm-hour-separator">t/m</span>
                                <input type="number" 
                                       name="max_hours" 
                                       class="wpjm-hour-input wpjm-hour-max" 
                                       value="<?php echo esc_attr($max_hours_selected); ?>" 
                                       min="0" 
                                       max="<?php echo esc_attr($max_hours); ?>"
                                       readonly />
                                <span class="wpjm-hour-label">uren</span>
                            </div>
                            <div class="wpjm-hour-slider-wrapper" 
                                 data-min="0" 
                                 data-max="<?php echo esc_attr($max_hours); ?>" 
                                 data-value-min="<?php echo esc_attr($min_hours); ?>" 
                                 data-value-max="<?php echo esc_attr($max_hours_selected); ?>">
                                <div class="wpjm-hour-slider-track"></div>
                                <div class="wpjm-hour-slider-range"></div>
                                <div class="wpjm-hour-slider-handle wpjm-hour-slider-handle-min" data-handle="min"></div>
                                <div class="wpjm-hour-slider-handle wpjm-hour-slider-handle-max" data-handle="max"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_date_posted'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-radio-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($date_posted_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                    <div class="wpjm-radio-options">
                        <label class="wpjm-radio-option">
                            <input type="radio" name="date_posted" value="" <?php checked(empty($search_date_posted)); ?>>
                            <span><?php echo esc_html__('Anytime', 'job-listings-search-filter'); ?></span>
                        </label>
                        <label class="wpjm-radio-option">
                            <input type="radio" name="date_posted" value="today" <?php checked($search_date_posted, 'today'); ?>>
                            <span><?php echo esc_html__('Today', 'job-listings-search-filter'); ?></span>
                        </label>
                        <label class="wpjm-radio-option">
                            <input type="radio" name="date_posted" value="7days" <?php checked($search_date_posted, '7days'); ?>>
                            <span><?php echo esc_html__('Last 7 days', 'job-listings-search-filter'); ?></span>
                        </label>
                        <label class="wpjm-radio-option">
                            <input type="radio" name="date_posted" value="14days" <?php checked($search_date_posted, '14days'); ?>>
                            <span><?php echo esc_html__('Last 14 days', 'job-listings-search-filter'); ?></span>
                        </label>
                        <label class="wpjm-radio-option">
                            <input type="radio" name="date_posted" value="30days" <?php checked($search_date_posted, '30days'); ?>>
                            <span><?php echo esc_html__('Last 30 days', 'job-listings-search-filter'); ?></span>
                        </label>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($settings['show_job_status'] === 'yes') : ?>
                <div class="wpjm-filter-field wpjm-checkbox-group wpjm-collapsible-section">
                    <div class="wpjm-collapsible-header">
                        <label class="wpjm-filter-label"><?php echo esc_html($job_status_label); ?></label>
                        <span class="wpjm-collapse-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="wpjm-collapsible-content" style="display: block;">
                    <div class="wpjm-checkbox-options">
                        <?php
                        $show_featured = isset($_GET['featured']) ? sanitize_text_field($_GET['featured']) : '';
                        $hide_filled = isset($_GET['hide_filled']) ? sanitize_text_field($_GET['hide_filled']) : '';
                        ?>
                        <label class="wpjm-checkbox-option">
                            <input type="checkbox" name="featured" value="1" <?php checked($show_featured, '1'); ?>>
                            <span><?php echo esc_html__('Featured Jobs Only', 'job-listings-search-filter'); ?></span>
                        </label>
                        <label class="wpjm-checkbox-option">
                            <input type="checkbox" name="hide_filled" value="1" <?php checked($hide_filled, '1'); ?>>
                            <span><?php echo esc_html__('Hide Filled Positions', 'job-listings-search-filter'); ?></span>
                        </label>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="wpjm-filter-actions">
                    <button type="submit" class="wpjm-filter-btn wpjm-filter-btn-primary">
                        <?php echo esc_html($text_search_button); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
        // Add inline script data using WordPress proper method
        $inline_script = sprintf(
            "var wpjmSearchData = wpjmSearchData || {ajaxUrl: '%s', nonce: '%s', targetQueryId: '%s', showNoResults: %s, noResultsMessage: '%s'};",
            esc_js(admin_url('admin-ajax.php')),
            esc_js(wp_create_nonce('wpjm_search_nonce')),
            esc_js($settings['target_query_id'] ?? 'job_listings'),
            ($settings['show_no_results'] === 'yes') ? 'true' : 'false',
            esc_js($settings['no_results_message'] ?? esc_html__('No jobs found matching your criteria.', 'job-listings-search-filter'))
        );
        wp_add_inline_script('wpjm-job-search-widget', $inline_script, 'before');
        ?>
        <?php
    }
}
