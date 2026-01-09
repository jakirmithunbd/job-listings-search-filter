<?php
/**
 * Plugin Name: Job Listings Search & Filter for Elementor
 * Plugin URI: https://wordpress.org/plugins/job-listings-search-filter/
 * Description: Seamlessly integrate WP Job Manager with Elementor Loop Grids and Dynamic Tags for powerful job listing displays with advanced filtering.
 * Version: 1.0.0
 * Author: Jakir Mithun
 * Author URI: https://jakirmithun.com
 * Text Domain: job-listings-search-filter
 * Domain Path: /languages
 * Requires at least: 5.8
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
        require_once JLSF_PATH . 'includes/dynamic-tags/job-location-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-company-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-date-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-description-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/job-application-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/company-logo-tag.php';
        require_once JLSF_PATH . 'includes/dynamic-tags/company-tagline-tag.php';
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
        // Assets are enqueued automatically by Elementor when widget is used
        // via get_style_depends() and get_script_depends() methods
        // Script is already localized in register_frontend_scripts()
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
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Location_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Company_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Date_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Description_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Job_Application_Tag());
        $dynamic_tags_manager->register(new \Job_Listings_Search_Filter\Dynamic_Tags\Company_Tagline_Tag());
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

        // 2. LOCATION - Search in _job_location meta field
        if (!empty($request['search_location'])) {
            $meta_query = $query->get('meta_query') ?: [];
            $meta_query[] = [
                'key' => '_job_location',
                'value' => sanitize_text_field($request['search_location']),
                'compare' => 'LIKE'
            ];
            $query->set('meta_query', $meta_query);
        }
        
        // 2.5 REMOTE POSITION - Filter by _remote_position meta field
        if (!empty($request['remote_position'])) {
            $meta_query = $query->get('meta_query') ?: [];
            $meta_query[] = [
                'key' => '_remote_position',
                'value' => '1',
                'compare' => '='
            ];
            $query->set('meta_query', $meta_query);
        }
        
        // 2.6 FEATURED - Filter by _featured meta field
        if (!empty($request['featured'])) {
            $meta_query = $query->get('meta_query') ?: [];
            $meta_query[] = [
                'key' => '_featured',
                'value' => '1',
                'compare' => '='
            ];
            $query->set('meta_query', $meta_query);
        }
        
        // 2.7 HIDE FILLED - Exclude filled positions
        if (!empty($request['hide_filled'])) {
            $meta_query = $query->get('meta_query') ?: [];
            $meta_query[] = [
                'key' => '_filled',
                'value' => '1',
                'compare' => '!='
            ];
            $query->set('meta_query', $meta_query);
        }

        // Get existing tax query
        $tax_query = $query->get('tax_query') ?: [];
        
        // 3. JOB TYPE - Filter by job_listing_type taxonomy
        if (!empty($request['job_type'])) {
            $tax_query[] = [
                'taxonomy' => 'job_listing_type',
                'field' => 'slug',
                'terms' => sanitize_title($request['job_type'])
            ];
        }

        // 4. CATEGORY - Filter by job_listing_category taxonomy
        if (!empty($request['category'])) {
            $tax_query[] = [
                'taxonomy' => 'job_listing_category',
                'field' => 'slug',
                'terms' => sanitize_title($request['category'])
            ];
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
     * AJAX handler for job search - using Elementor query hook
     * The actual filtering is done by modify_loop_query_for_job_search() 
     * via elementor/query/query_results hook
     */
    public function ajax_search_jobs() {
        check_ajax_referer('wpjm_search_nonce', 'nonce');
        
        $search_keywords = isset($_POST['search_keywords']) ? sanitize_text_field($_POST['search_keywords']) : '';
        $search_location = isset($_POST['search_location']) ? sanitize_text_field($_POST['search_location']) : '';
        $job_type = isset($_POST['job_type']) ? sanitize_text_field($_POST['job_type']) : '';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $date_posted = isset($_POST['date_posted']) ? sanitize_text_field($_POST['date_posted']) : '';
        $remote_position = isset($_POST['remote_position']) ? sanitize_text_field($_POST['remote_position']) : '';
        $featured = isset($_POST['featured']) ? sanitize_text_field($_POST['featured']) : '';
        $hide_filled = isset($_POST['hide_filled']) ? sanitize_text_field($_POST['hide_filled']) : '';
        $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
        
        if (empty($page_url)) {
            wp_send_json_error(['message' => 'Page URL is required']);
        }
        
        // Build URL with search parameters
        $query_params = [];
        if (!empty($search_keywords)) $query_params['search_keywords'] = $search_keywords;
        if (!empty($search_location)) $query_params['search_location'] = $search_location;
        if (!empty($job_type)) $query_params['job_type'] = $job_type;
        if (!empty($category)) $query_params['category'] = $category;
        if (!empty($date_posted)) $query_params['date_posted'] = $date_posted;
        if (!empty($remote_position)) $query_params['remote_position'] = $remote_position;
        if (!empty($featured)) $query_params['featured'] = $featured;
        if (!empty($hide_filled)) $query_params['hide_filled'] = $hide_filled;
        
        // Just return the URL with parameters - let JS redirect
        $url = add_query_arg($query_params, $page_url);
        
        wp_send_json_success([
            'redirect' => $url
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
