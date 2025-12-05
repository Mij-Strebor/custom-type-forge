<?php
/**
 * Analyzer Core Class
 *
 * Central orchestration and coordination for site scanning
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core analyzer class
 */
class CTF_Analyzer_Core {

    /**
     * Cache key for scan data
     */
    const CACHE_KEY = 'ctf_scan_data';

    /**
     * Cache expiration (default 1 hour)
     */
    const CACHE_EXPIRATION = 3600;

    /**
     * CPT Detector instance
     *
     * @var CTF_CPT_Detector
     */
    private $cpt_detector;

    /**
     * ACF Detector instance
     *
     * @var CTF_ACF_Detector
     */
    private $acf_detector;

    /**
     * Relationship Mapper instance
     *
     * @var CTF_Relationship_Mapper
     */
    private $relationship_mapper;

    /**
     * Constructor
     */
    public function __construct() {
        $this->cpt_detector = new CTF_CPT_Detector();
        $this->acf_detector = new CTF_ACF_Detector();
        $this->relationship_mapper = new CTF_Relationship_Mapper();
    }

    /**
     * Scan the entire site for CPTs and fields
     *
     * @param bool $force_refresh Force fresh scan, ignoring cache
     * @return array Complete scan results
     */
    public function scan_site($force_refresh = false) {
        // Check cache first
        if (!$force_refresh) {
            $cached_data = get_transient(self::CACHE_KEY);
            if (false !== $cached_data) {
                return $cached_data;
            }
        }

        $scan_data = [
            'metadata' => $this->get_metadata(),
            'cpts' => $this->get_all_cpts(),
            'field_groups' => $this->get_all_field_groups(),
            'platforms_detected' => $this->detect_platforms(),
        ];

        // Add relationship analysis
        $scan_data['relationships'] = $this->analyze_relationships($scan_data);

        // Cache the results
        $this->cache_results($scan_data);

        return $scan_data;
    }

    /**
     * Get all custom post types
     *
     * @return array CPT data
     */
    public function get_all_cpts() {
        return $this->cpt_detector->detect_all_cpts();
    }

    /**
     * Get all field groups across all platforms
     *
     * @return array Field group data
     */
    public function get_all_field_groups() {
        return $this->acf_detector->detect_all_field_groups();
    }

    /**
     * Analyze relationships between entities
     *
     * @param array $scan_data Complete scan data
     * @return array Relationship data
     */
    public function analyze_relationships($scan_data) {
        return $this->relationship_mapper->map_all_relationships(
            $scan_data['cpts'],
            $scan_data['field_groups']
        );
    }

    /**
     * Detect which platforms are installed
     *
     * @return array Detected platforms with versions
     */
    public function detect_platforms() {
        $platforms = [];

        // ACF
        if (function_exists('acf_get_setting')) {
            $platforms['acf'] = [
                'name' => 'Advanced Custom Fields',
                'version' => acf_get_setting('version'),
                'pro' => (defined('ACF_PRO') && ACF_PRO) ? true : false,
                'active' => true,
            ];
        }

        // Pods
        if (class_exists('Pods')) {
            $platforms['pods'] = [
                'name' => 'Pods Framework',
                'version' => defined('PODS_VERSION') ? PODS_VERSION : 'Unknown',
                'active' => true,
            ];
        }

        // Toolset
        if (defined('TYPES_VERSION')) {
            $platforms['toolset'] = [
                'name' => 'Toolset Types',
                'version' => TYPES_VERSION,
                'active' => true,
            ];
        }

        // Meta Box
        if (function_exists('rwmb_meta')) {
            $platforms['metabox'] = [
                'name' => 'Meta Box',
                'version' => defined('RWMB_VER') ? RWMB_VER : 'Unknown',
                'active' => true,
            ];
        }

        // JetEngine
        if (class_exists('Jet_Engine')) {
            $platforms['jetengine'] = [
                'name' => 'JetEngine',
                'version' => defined('JET_ENGINE_VERSION') ? JET_ENGINE_VERSION : 'Unknown',
                'active' => true,
            ];
        }

        // Carbon Fields
        if (class_exists('Carbon_Fields\\Carbon_Fields')) {
            $platforms['carbon'] = [
                'name' => 'Carbon Fields',
                'version' => 'Unknown',
                'active' => true,
            ];
        }

        // Native WordPress
        $platforms['native'] = [
            'name' => 'WordPress Core',
            'version' => get_bloginfo('version'),
            'active' => true,
        ];

        return $platforms;
    }

    /**
     * Get scan metadata
     *
     * @return array Metadata about the scan
     */
    private function get_metadata() {
        return [
            'scan_date' => current_time('mysql'),
            'wp_version' => get_bloginfo('version'),
            'php_version' => phpversion(),
            'is_multisite' => is_multisite(),
            'site_url' => get_site_url(),
            'plugin_version' => CTF_VERSION,
        ];
    }

    /**
     * Cache scan results
     *
     * @param array $data Data to cache
     */
    public function cache_results($data) {
        $settings = get_option('ctf_settings', []);
        $expiration = isset($settings['cache_duration']) ? $settings['cache_duration'] : self::CACHE_EXPIRATION;

        set_transient(self::CACHE_KEY, $data, $expiration);
    }

    /**
     * Clear cached scan data
     */
    public function clear_cache() {
        delete_transient(self::CACHE_KEY);
    }

    /**
     * Get cached scan data
     *
     * @return array|false Cached data or false if not found
     */
    public function get_cached_data() {
        return get_transient(self::CACHE_KEY);
    }

    /**
     * Check if cache exists
     *
     * @return bool True if cache exists
     */
    public function has_cache() {
        return false !== get_transient(self::CACHE_KEY);
    }

    /**
     * Get cache expiration timestamp
     *
     * @return int|false Timestamp or false if no cache
     */
    public function get_cache_expiration() {
        global $wpdb;

        $transient_name = '_transient_timeout_' . self::CACHE_KEY;
        $timeout = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
                $transient_name
            )
        );

        return $timeout ? (int) $timeout : false;
    }
}
