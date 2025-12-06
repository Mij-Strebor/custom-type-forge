<?php
/**
 * Plugin Name:       Custom Type Forge
 * Plugin URI:        https://jimrforge.com/custom-type-forge
 * Description:       Comprehensive analyzer and documentation generator for WordPress Custom Post Types and Advanced Custom Fields. Supports ACF, Pods, Toolset, Meta Box, JetEngine, and native WordPress implementations.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Jim R Forge
 * Author URI:        https://jimrforge.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-type-forge
 * Domain Path:       /languages
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('CTF_VERSION', '1.0.0');
define('CTF_PLUGIN_FILE', __FILE__);
define('CTF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CTF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CTF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Custom_Type_Forge {

    /**
     * Nonce action for AJAX requests
     */
    const NONCE_ACTION = 'ctf_ajax_nonce';

    /**
     * Singleton instance
     *
     * @var Custom_Type_Forge
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return Custom_Type_Forge
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize the plugin
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once CTF_PLUGIN_DIR . 'includes/class-analyzer-core.php';
        require_once CTF_PLUGIN_DIR . 'includes/class-cpt-detector.php';
        require_once CTF_PLUGIN_DIR . 'includes/class-acf-detector.php';
        require_once CTF_PLUGIN_DIR . 'includes/class-relationship-mapper.php';
        require_once CTF_PLUGIN_DIR . 'includes/class-report-generator.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // AJAX handlers
        add_action('wp_ajax_ctf_scan_site', [$this, 'ajax_scan_site']);
        add_action('wp_ajax_ctf_generate_report', [$this, 'ajax_generate_report']);
        add_action('wp_ajax_ctf_clear_cache', [$this, 'ajax_clear_cache']);
    }

    /**
     * Register admin menu pages
     */
    public function register_admin_menu() {
        add_menu_page(
            __('Custom Type Forge', 'custom-type-forge'),
            __('Custom Type Forge', 'custom-type-forge'),
            'manage_options',
            'custom-type-forge',
            [$this, 'render_dashboard_page'],
            'dashicons-analytics',
            81
        );

        add_submenu_page(
            'custom-type-forge',
            __('Dashboard', 'custom-type-forge'),
            __('Dashboard', 'custom-type-forge'),
            'manage_options',
            'custom-type-forge',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'custom-type-forge',
            __('Custom Post Types', 'custom-type-forge'),
            __('Post Types', 'custom-type-forge'),
            'manage_options',
            'ctf-post-types',
            [$this, 'render_post_types_page']
        );

        add_submenu_page(
            'custom-type-forge',
            __('Field Groups', 'custom-type-forge'),
            __('Field Groups', 'custom-type-forge'),
            'manage_options',
            'ctf-field-groups',
            [$this, 'render_field_groups_page']
        );

        add_submenu_page(
            'custom-type-forge',
            __('Relationships', 'custom-type-forge'),
            __('Relationships', 'custom-type-forge'),
            'manage_options',
            'ctf-relationships',
            [$this, 'render_relationships_page']
        );

        add_submenu_page(
            'custom-type-forge',
            __('Reports', 'custom-type-forge'),
            __('Reports', 'custom-type-forge'),
            'manage_options',
            'ctf-reports',
            [$this, 'render_reports_page']
        );
    }

    /**
     * Enqueue admin CSS and JavaScript
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'custom-type-forge') === false && strpos($hook, 'ctf-') === false) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'ctf-forge-header',
            CTF_PLUGIN_URL . 'assets/css/forge-header.css',
            [],
            CTF_VERSION
        );

        wp_enqueue_style(
            'ctf-admin-styles',
            CTF_PLUGIN_URL . 'assets/css/admin-styles.css',
            ['ctf-forge-header'],
            CTF_VERSION
        );

        // Enqueue scripts
        wp_enqueue_script(
            'ctf-admin-script',
            CTF_PLUGIN_URL . 'assets/js/admin-script.js',
            ['jquery'],
            CTF_VERSION,
            true
        );

        // Localize script with AJAX configuration
        wp_localize_script('ctf-admin-script', 'ctfAjax', [
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'strings' => [
                'scanning' => __('Scanning site...', 'custom-type-forge'),
                'scanComplete' => __('Scan complete!', 'custom-type-forge'),
                'scanError' => __('Scan failed. Please try again.', 'custom-type-forge'),
                'generating' => __('Generating report...', 'custom-type-forge'),
                'reportReady' => __('Report ready for download!', 'custom-type-forge'),
            ]
        ]);
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'custom-type-forge'));
        }

        include CTF_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Render post types page
     */
    public function render_post_types_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'custom-type-forge'));
        }

        include CTF_PLUGIN_DIR . 'templates/admin/post-types.php';
    }

    /**
     * Render field groups page
     */
    public function render_field_groups_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'custom-type-forge'));
        }

        include CTF_PLUGIN_DIR . 'templates/admin/field-groups.php';
    }

    /**
     * Render relationships page
     */
    public function render_relationships_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'custom-type-forge'));
        }

        include CTF_PLUGIN_DIR . 'templates/admin/relationships.php';
    }

    /**
     * Render reports page
     */
    public function render_reports_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'custom-type-forge'));
        }

        include CTF_PLUGIN_DIR . 'templates/admin/reports.php';
    }

    /**
     * AJAX handler: Scan site for CPTs and fields
     */
    public function ajax_scan_site() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], self::NONCE_ACTION)) {
            wp_send_json_error(['message' => __('Security check failed', 'custom-type-forge')]);
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'custom-type-forge')]);
        }

        try {
            $analyzer = new CTF_Analyzer_Core();
            $results = $analyzer->scan_site();

            wp_send_json_success([
                'message' => __('Scan completed successfully', 'custom-type-forge'),
                'data' => $results
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Scan failed: ', 'custom-type-forge') . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX handler: Generate report
     */
    public function ajax_generate_report() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], self::NONCE_ACTION)) {
            wp_send_json_error(['message' => __('Security check failed', 'custom-type-forge')]);
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'custom-type-forge')]);
        }

        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'html';

        try {
            $generator = new CTF_Report_Generator();
            $report = $generator->generate($format);

            wp_send_json_success([
                'message' => __('Report generated successfully', 'custom-type-forge'),
                'download_url' => $report['url']
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Report generation failed: ', 'custom-type-forge') . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX handler: Clear cache
     */
    public function ajax_clear_cache() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], self::NONCE_ACTION)) {
            wp_send_json_error(['message' => __('Security check failed', 'custom-type-forge')]);
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'custom-type-forge')]);
        }

        try {
            $analyzer = new CTF_Analyzer_Core();
            $analyzer->clear_cache();

            wp_send_json_success([
                'message' => __('Cache cleared successfully', 'custom-type-forge')
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Cache clear failed: ', 'custom-type-forge') . $e->getMessage()
            ]);
        }
    }
}

/**
 * Initialize the plugin
 */
function ctf_init() {
    return Custom_Type_Forge::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'ctf_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Set default options on activation
    if (false === get_option('ctf_settings')) {
        add_option('ctf_settings', [
            'cache_duration' => 3600,
            'platforms' => ['acf', 'pods', 'toolset', 'metabox', 'jetengine', 'native'],
            'include_builtin' => false,
        ]);
    }
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Clear transients on deactivation
    delete_transient('ctf_scan_data');
});
