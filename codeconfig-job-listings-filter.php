<?php
/**
 * Plugin Name: CodeConfig Job Listings Filter for Elementor
 * Plugin URI: https://wordpress.org/plugins/codeconfig-job-listings-filter/
 * Description: Seamlessly integrate WP Job Manager with Elementor Loop Grids and Dynamic Tags for powerful job listing displays with advanced filtering.
 * Version: 1.0.0
 * Author: CodeConfig
 * Author URI: 
 * Text Domain: codeconfig-job-listings-filter
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: elementor, wp-job-manager
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('JLSF_VERSION', '1.0.0');
define('JLSF_FILE', __FILE__);
define('JLSF_PATH', plugin_dir_path(__FILE__));
define('JLSF_URL', plugin_dir_url(__FILE__));

/**
 * Main Job Listings Search & Filter for Elementor Class
 */
final class Job_Listings_Search_Filter {

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
        
        // Register and enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'register_frontend_scripts']);
        add_action('elementor/frontend/after_register_styles', [$this, 'register_frontend_styles']);
        
        // AJAX handlers
        add_action('wp_ajax_wpjm_search_jobs', [$this, 'ajax_search_jobs']);
        add_action('wp_ajax_nopriv_wpjm_search_jobs', [$this, 'ajax_search_jobs']);
        
        // Early hook to modify job_listing post type before Elementor checks it
        add_action('init', [$this, 'modify_job_listing_post_type'], 999);
        
        // Add working hours field to WP Job Manager
        add_action('job_manager_job_listing_data_fields', [$this, 'add_working_hours_field']);
        add_filter('submit_job_form_fields', [$this, 'add_working_hours_frontend_field']);
        // add_action('job_manager_update_job_data', [$this, 'save_working_hours_field'], 10, 2);
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if Elementor and WP Job Manager are installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        if (!class_exists('WP_Job_Manager')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_wpjm']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Load plugin files
        $this->includes();

        // Register dynamic tags
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);
        
        // Register custom widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);

        // Add job_listing post type to Elementor
        add_filter('elementor/utils/get_public_post_types', [$this, 'add_job_listing_post_type']);
        
        // Add job_listing to query control source options
        add_filter('elementor_pro/query/get_source_options', [$this, 'add_job_listing_to_query_source']);
        
        // Ensure job_listing is treated as a public post type
        add_action('elementor/init', [$this, 'register_job_listing_support']);
        
        // Register dynamic query hooks for job search filtering
        add_action('elementor/query/job_listings', [$this, 'modify_job_query'], 10, 1);
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once JLSF_PATH . 'includes/dynamic-tags/job-title-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-type-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-category-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-location-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-company-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-date-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-description-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-application-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/company-tagline-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-working-hours-tag.php';
    }

    /**
     * Register custom widgets
     */
    public function register_widgets($widgets_manager) {
        // Load widget file only when registering
        require_once JLSF_PATH . 'includes/widgets/job-search-widget.php';
        $widgets_manager->register(new \Job_Listings_Search_Filter\Widgets\Job_Search_Widget());
    }
    
    /**
     * Register frontend scripts
     */
    public function register_frontend_scripts() {
        wp_register_script(
            'wpjm-job-search-widget',
            JLSF_URL . 'assets/js/job-search-widget.js',
            ['jquery'],
            JLSF_VERSION,
            true
        );
        
        // Localize script immediately after registering
        wp_localize_script('wpjm-job-search-widget', 'wpjmSearchData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpjm_search_nonce')
        ]);
    }
    
    /**
     * Register frontend styles
     */
    public function register_frontend_styles() {
        wp_register_style(
            'wpjm-job-search-widget',
            JLSF_URL . 'assets/css/job-search-widget.css',
            [],
            JLSF_VERSION
        );
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Enqueue scripts and styles globally so they're available
        wp_enqueue_script('wpjm-job-search-widget');
        wp_enqueue_style('wpjm-job-search-widget');
        
        // Ensure localization is always available
        if (!wp_script_is('wpjm-job-search-widget', 'enqueued')) {
            wp_enqueue_script('wpjm-job-search-widget');
        }
    }

    /**
     * Register dynamic tags
     */
    public function register_dynamic_tags($dynamic_tags_manager) {
        // Create a group for WP Job Manager tags
        $dynamic_tags_manager->register_group(
            'wpjm-tags',
            [
                'title' => esc_html__('WP Job Manager', 'job-listings-search-filter')
            ]
        );

        // Register individual tags
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Title_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Type_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Category_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Location_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Company_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Date_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Description_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Application_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Company_Tagline_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Working_Hours_Tag());
    }

    /**
     * Add job_listing to public post types
     */
    public function add_job_listing_post_type($post_types) {
        $post_types['job_listing'] = esc_html__('Job Listing', 'job-listings-search-filter');
        return $post_types;
    }

    /**
     * Add job_listing to query source dropdown
     */
    public function add_job_listing_to_query_source($options) {
        $options['job_listing'] = esc_html__('Job Listing', 'job-listings-search-filter');
        return $options;
    }

    /**
     * Register job_listing support with Elementor
     */
    public function register_job_listing_support() {
        // Make sure job_listing post type is recognized by Elementor
        $post_type_object = get_post_type_object('job_listing');
        
        if ($post_type_object) {
            // Force the post type to be public and show in Elementor
            $post_type_object->public = true;
            $post_type_object->show_ui = true;
            $post_type_object->show_in_nav_menus = true;
        }
    }

    /**
     * Modify job_listing post type to make it compatible with Elementor
     */
    public function modify_job_listing_post_type() {
        global $wp_post_types;
        
        if (isset($wp_post_types['job_listing'])) {
            // Make sure it's marked as public so Elementor can see it
            $wp_post_types['job_listing']->public = true;
            $wp_post_types['job_listing']->publicly_queryable = true;
            $wp_post_types['job_listing']->show_ui = true;
            $wp_post_types['job_listing']->show_in_nav_menus = true;
            $wp_post_types['job_listing']->show_in_rest = true;
        }
    }

    /**
     * Add working hours field to WP Job Manager admin
     */
    public function add_working_hours_field($fields) {
        $fields['_job_working_hours'] = [
            'label'       => __('Working Hours', 'job-listings-search-filter'),
            'type'        => 'text',
            'placeholder' => __('e.g., Full-time (40 hours/week)', 'job-listings-search-filter'),
            'description' => __('Enter the working hours for this position', 'job-listings-search-filter'),
            'priority'    => 5,
        ];
        return $fields;
    }

    /**
     * Add working hours field to frontend job submission form
     */
    public function add_working_hours_frontend_field($fields) {
        $fields['job']['job_working_hours'] = [
            'label'       => __('Working Hours', 'job-listings-search-filter'),
            'type'        => 'text',
            'placeholder' => __('e.g., Full-time (40 hours/week)', 'job-listings-search-filter'),
            'required'    => false,
            'priority'    => 5,
        ];
        return $fields;
    }

    /**
     * Save working hours field
     */
    public function save_working_hours_field($job_id, $values) {
        if (isset($values['job']['job_working_hours'])) {
            update_post_meta($job_id, '_job_working_hours', sanitize_text_field($values['job']['job_working_hours']));
        }
    }



    /**
     * Modify job listings query based on search parameters
     * Uses elementor/query/{query_id} hook for targeted filtering
     */
    public function modify_job_query($query) {
        // Get search parameters from GET/POST
        $request = array_merge($_GET, $_POST);
        
        // 1. SEARCH KEYWORDS - Search in title and content
        if (!empty($request['search_keywords'])) {
            $query->set('s', sanitize_text_field($request['search_keywords']));
        }

        // Initialize meta query
        $meta_query = $query->get('meta_query') ?: [];
        
        // Only add relation if we're going to add new conditions
        $original_meta_query_count = count($meta_query);

        // 2. LOCATION - Search in _job_location meta field (supports multiple)
        if (!empty($request['search_location'])) {
            $locations = is_array($request['search_location']) ? $request['search_location'] : [$request['search_location']];
            $locations = array_filter(array_map('sanitize_text_field', $locations));
            
            if (count($locations) > 0) {
                if (count($locations) === 1) {
                    $meta_query[] = [
                        'key' => '_job_location',
                        'value' => $locations[0],
                        'compare' => 'LIKE'
                    ];
                } else {
                    $location_query = ['relation' => 'OR'];
                    foreach ($locations as $location) {
                        $location_query[] = [
                            'key' => '_job_location',
                            'value' => $location,
                            'compare' => 'LIKE'
                        ];
                    }
                    $meta_query[] = $location_query;
                }
            }
        }
        
        // 2.5 REMOTE POSITION - Filter by _remote_position meta field
        if (!empty($request['remote_position'])) {
            $meta_query[] = [
                'key' => '_remote_position',
                'value' => '1',
                'compare' => '='
            ];
        }
        
        // 2.6 FEATURED - Filter by _featured meta field
        if (!empty($request['featured'])) {
            $meta_query[] = [
                'key' => '_featured',
                'value' => '1',
                'compare' => '='
            ];
        }
        
        // 2.7 HIDE FILLED - Exclude filled positions
        if (!empty($request['hide_filled'])) {
            $meta_query[] = [
                'key' => '_filled',
                'value' => '1',
                'compare' => '!='
            ];
        }
        
        // 2.8 WORKING HOURS - Filter by _job_working_hours meta field (range-based)
        if (isset($request['min_hours']) || isset($request['max_hours'])) {
            $min_hours = isset($request['min_hours']) ? intval($request['min_hours']) : 0;
            $max_hours = isset($request['max_hours']) ? intval($request['max_hours']) : 9999;
            
            $meta_query[] = [
                'key' => '_job_working_hours',
                'value' => [$min_hours, $max_hours],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
        }
        // Legacy support for old working_hours array format
        elseif (!empty($request['working_hours'])) {
            $hours = is_array($request['working_hours']) ? $request['working_hours'] : explode(',', $request['working_hours']);
            $hours = array_filter(array_map('sanitize_text_field', $hours));
            
            if (count($hours) > 0) {
                if (count($hours) === 1) {
                    $meta_query[] = [
                        'key' => '_job_working_hours',
                        'value' => $hours[0],
                        'compare' => 'LIKE'
                    ];
                } else {
                    $hours_query = ['relation' => 'OR'];
                    foreach ($hours as $hour) {
                        $hours_query[] = [
                            'key' => '_job_working_hours',
                            'value' => $hour,
                            'compare' => 'LIKE'
                        ];
                    }
                    $meta_query[] = $hours_query;
                }
            }
        }
        
        // Set meta query if we added any conditions
        if (count($meta_query) > $original_meta_query_count) {
            // Add relation if we have multiple meta queries and no relation is set
            if (count($meta_query) > 1 && !isset($meta_query['relation'])) {
                $meta_query['relation'] = 'AND';
            }
            $query->set('meta_query', $meta_query);
        }

        // Get existing tax query
        $tax_query = $query->get('tax_query') ?: [];
        
        // 3. JOB TYPE - Filter by job_listing_type taxonomy (supports multiple)
        if (!empty($request['job_type'])) {
            $job_types = is_array($request['job_type']) ? $request['job_type'] : explode(',', $request['job_type']);
            $job_types = array_filter(array_map('sanitize_title', $job_types));
            
            if (count($job_types) > 0) {
                $tax_query[] = [
                    'taxonomy' => 'job_listing_type',
                    'field' => 'slug',
                    'terms' => $job_types,
                    'operator' => 'IN'
                ];
            }
        }

        // 4. CATEGORY - Filter by job_listing_category taxonomy (supports multiple)
        if (!empty($request['category'])) {
            $categories = is_array($request['category']) ? $request['category'] : explode(',', $request['category']);
            $categories = array_filter(array_map('sanitize_title', $categories));
            
            if (count($categories) > 0) {
                $tax_query[] = [
                    'taxonomy' => 'job_listing_category',
                    'field' => 'slug',
                    'terms' => $categories,
                    'operator' => 'IN'
                ];
            }
        }
        
        // Set tax query if we added any conditions
        if (!empty($tax_query)) {
            // Add relation if we have multiple taxonomy queries
            if (count($tax_query) > 1 && !isset($tax_query['relation'])) {
                $tax_query['relation'] = 'AND';
            }
            $query->set('tax_query', $tax_query);
        }

        // 5. DATE POSTED - Filter by post date
        if (!empty($request['date_posted'])) {
            $date_posted = sanitize_text_field($request['date_posted']);
            $date_query = [];
            
            switch ($date_posted) {
                case 'today':
                    $date_query = [
                        'after' => '1 day ago',
                        'inclusive' => true
                    ];
                    break;
                case '7days':
                    $date_query = [
                        'after' => '7 days ago',
                        'inclusive' => true
                    ];
                    break;
                case '14days':
                    $date_query = [
                        'after' => '14 days ago',
                        'inclusive' => true
                    ];
                    break;
                case '30days':
                    $date_query = [
                        'after' => '30 days ago',
                        'inclusive' => true
                    ];
                    break;
            }
            
            if (!empty($date_query)) {
                $query->set('date_query', [$date_query]);
            }
        }
    }

    /**
     * Admin notice for missing Elementor
     */
    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'job-listings-search-filter'),
            '<strong>' . esc_html__('Job Listings Search & Filter for Elementor', 'job-listings-search-filter') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'job-listings-search-filter') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for missing WP Job Manager
     */
    public function admin_notice_missing_wpjm() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'job-listings-search-filter'),
            '<strong>' . esc_html__('Job Listings Search & Filter for Elementor', 'job-listings-search-filter') . '</strong>',
            '<strong>' . esc_html__('WP Job Manager', 'job-listings-search-filter') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    /**
     * AJAX handler for job search
     * Returns filtered job results HTML for smooth AJAX filtering
     */
    public function ajax_search_jobs() {
        check_ajax_referer('wpjm_search_nonce', 'nonce');
        
        $search_keywords = isset($_POST['search_keywords']) ? sanitize_text_field($_POST['search_keywords']) : '';
        $search_location = isset($_POST['search_location']) ? sanitize_text_field($_POST['search_location']) : '';
        $job_type = isset($_POST['job_type']) ? sanitize_text_field($_POST['job_type']) : '';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $working_hours = isset($_POST['working_hours']) ? sanitize_text_field($_POST['working_hours']) : '';
        $date_posted = isset($_POST['date_posted']) ? sanitize_text_field($_POST['date_posted']) : '';
        $remote_position = isset($_POST['remote_position']) ? sanitize_text_field($_POST['remote_position']) : '';
        $featured = isset($_POST['featured']) ? sanitize_text_field($_POST['featured']) : '';
        $hide_filled = isset($_POST['hide_filled']) ? sanitize_text_field($_POST['hide_filled']) : '';
        $query_id = isset($_POST['query_id']) ? sanitize_text_field($_POST['query_id']) : 'job_listings';
        
        // Build query args
        $args = [
            'post_type' => 'job_listing',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'paged' => isset($_POST['paged']) ? absint($_POST['paged']) : 1,
        ];
        
        // Add search keywords
        if (!empty($search_keywords)) {
            $args['s'] = $search_keywords;
        }
        
        // Build meta query
        $meta_query = [];
        
        if (!empty($search_location)) {
            $locations = is_array($search_location) ? $search_location : explode(',', $search_location);
            $locations = array_filter($locations);
            
            if (count($locations) === 1) {
                $meta_query[] = [
                    'key' => '_job_location',
                    'value' => $locations[0],
                    'compare' => 'LIKE'
                ];
            } elseif (count($locations) > 1) {
                $location_query = ['relation' => 'OR'];
                foreach ($locations as $location) {
                    $location_query[] = [
                        'key' => '_job_location',
                        'value' => $location,
                        'compare' => 'LIKE'
                    ];
                }
                $meta_query[] = $location_query;
            }
        }
        
        if (!empty($working_hours)) {
            $hours = is_array($working_hours) ? $working_hours : explode(',', $working_hours);
            $hours = array_filter($hours);
            
            if (count($hours) === 1) {
                $meta_query[] = [
                    'key' => '_job_working_hours',
                    'value' => $hours[0],
                    'compare' => 'LIKE'
                ];
            } elseif (count($hours) > 1) {
                $hours_query = ['relation' => 'OR'];
                foreach ($hours as $hour) {
                    $hours_query[] = [
                        'key' => '_job_working_hours',
                        'value' => $hour,
                        'compare' => 'LIKE'
                    ];
                }
                $meta_query[] = $hours_query;
            }
        }
        
        if (!empty($remote_position)) {
            $meta_query[] = [
                'key' => '_remote_position',
                'value' => '1',
                'compare' => '='
            ];
        }
        
        if (!empty($featured)) {
            $meta_query[] = [
                'key' => '_featured',
                'value' => '1',
                'compare' => '='
            ];
        }
        
        if (!empty($hide_filled)) {
            $meta_query[] = [
                'key' => '_filled',
                'value' => '1',
                'compare' => '!='
            ];
        }
        
        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $args['meta_query'] = $meta_query;
        }
        
        // Build tax query
        $tax_query = [];
        
        if (!empty($job_type)) {
            $job_types = is_array($job_type) ? $job_type : explode(',', $job_type);
            $job_types = array_filter($job_types);
            
            if (!empty($job_types)) {
                $tax_query[] = [
                    'taxonomy' => 'job_listing_type',
                    'field' => 'slug',
                    'terms' => $job_types,
                    'operator' => 'IN'
                ];
            }
        }
        
        if (!empty($category)) {
            $categories = is_array($category) ? $category : explode(',', $category);
            $categories = array_filter($categories);
            
            if (!empty($categories)) {
                $tax_query[] = [
                    'taxonomy' => 'job_listing_category',
                    'field' => 'slug',
                    'terms' => $categories,
                    'operator' => 'IN'
                ];
            }
        }
        
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }
        
        // Date query
        if (!empty($date_posted)) {
            $date_query = [];
            
            switch ($date_posted) {
                case 'today':
                    $date_query = ['after' => '1 day ago', 'inclusive' => true];
                    break;
                case '7days':
                    $date_query = ['after' => '7 days ago', 'inclusive' => true];
                    break;
                case '14days':
                    $date_query = ['after' => '14 days ago', 'inclusive' => true];
                    break;
                case '30days':
                    $date_query = ['after' => '30 days ago', 'inclusive' => true];
                    break;
            }
            
            if (!empty($date_query)) {
                $args['date_query'] = [$date_query];
            }
        }
        
        // Execute query
        $query = new \WP_Query($args);
        
        // Build response
        $html = '';
        $found_posts = $query->found_posts;
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                // Get the post ID to output - Elementor will render it
                $html .= '<div class="wpjm-job-result" data-post-id="' . get_the_ID() . '">';
                $html .= '<h3>' . get_the_title() . '</h3>';
                $html .= '<div class="wpjm-job-meta">';
                
                // Job type
                $types = wp_get_post_terms(get_the_ID(), 'job_listing_type');
                if (!empty($types)) {
                    $html .= '<span class="job-type">' . esc_html($types[0]->name) . '</span>';
                }
                
                // Location
                $location = get_post_meta(get_the_ID(), '_job_location', true);
                if ($location) {
                    $html .= '<span class="job-location">' . esc_html($location) . '</span>';
                }
                
                // Company
                $company = get_post_meta(get_the_ID(), '_company_name', true);
                if ($company) {
                    $html .= '<span class="job-company">' . esc_html($company) . '</span>';
                }
                
                $html .= '</div>';
                $html .= '<div class="wpjm-job-excerpt">' . get_the_excerpt() . '</div>';
                $html .= '<a href="' . get_permalink() . '" class="wpjm-job-link">' . esc_html__('View Details', 'job-listings-search-filter') . '</a>';
                $html .= '</div>';
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success([
            'html' => $html,
            'found_posts' => $found_posts,
            'max_pages' => $query->max_num_pages,
            'current_page' => $args['paged']
        ]);
    }

    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'job-listings-search-filter'),
            '<strong>' . esc_html__('Job Listings Search & Filter for Elementor', 'job-listings-search-filter') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'job-listings-search-filter') . '</strong>',
            '3.5.0'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

// Initialize the plugin
Job_Listings_Search_Filter::instance();
