<?php
/**
 * CPT Detector Class
 *
 * Discovers and analyzes all Custom Post Types
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Post Type Detector
 */
class CTF_CPT_Detector {

    /**
     * Detect all registered custom post types
     *
     * @return array Array of CPT data
     */
    public function detect_all_cpts() {
        $settings = get_option('ctf_settings', []);
        $include_builtin = isset($settings['include_builtin']) ? $settings['include_builtin'] : false;

        $args = $include_builtin ? [] : ['_builtin' => false];
        $cpts = get_post_types($args, 'objects');

        $result = [];

        foreach ($cpts as $cpt_slug => $cpt_object) {
            $result[$cpt_slug] = $this->get_cpt_details($cpt_slug, $cpt_object);
        }

        return $result;
    }

    /**
     * Get detailed information about a specific CPT
     *
     * @param string $post_type Post type slug
     * @param WP_Post_Type $cpt_object Post type object
     * @return array CPT details
     */
    public function get_cpt_details($post_type, $cpt_object = null) {
        if (null === $cpt_object) {
            $cpt_object = get_post_type_object($post_type);
        }

        if (!$cpt_object) {
            return [];
        }

        return [
            'name' => $post_type,
            'label' => $cpt_object->label,
            'labels' => (array) $cpt_object->labels,
            'description' => $cpt_object->description,
            'public' => $cpt_object->public,
            'hierarchical' => $cpt_object->hierarchical,
            'exclude_from_search' => $cpt_object->exclude_from_search,
            'publicly_queryable' => $cpt_object->publicly_queryable,
            'show_ui' => $cpt_object->show_ui,
            'show_in_menu' => $cpt_object->show_in_menu,
            'show_in_nav_menus' => $cpt_object->show_in_nav_menus,
            'show_in_admin_bar' => $cpt_object->show_in_admin_bar,
            'show_in_rest' => $cpt_object->show_in_rest,
            'rest_base' => $cpt_object->rest_base,
            'rest_controller_class' => $cpt_object->rest_controller_class,
            'menu_position' => $cpt_object->menu_position,
            'menu_icon' => $cpt_object->menu_icon,
            'capabilities' => $this->get_cpt_capabilities($cpt_object),
            'supports' => $this->get_supported_features($post_type),
            'taxonomies' => $this->get_taxonomies($post_type),
            'has_archive' => $cpt_object->has_archive,
            'rewrite' => $cpt_object->rewrite,
            'query_var' => $cpt_object->query_var,
            'can_export' => $cpt_object->can_export,
            'delete_with_user' => $cpt_object->delete_with_user,
            'source' => $this->detect_registration_source($post_type),
            'post_count' => $this->get_post_count($post_type),
        ];
    }

    /**
     * Get CPT capabilities
     *
     * @param WP_Post_Type $cpt_object Post type object
     * @return array Capabilities
     */
    public function get_cpt_capabilities($cpt_object) {
        return (array) $cpt_object->cap;
    }

    /**
     * Get supported features for a post type
     *
     * @param string $post_type Post type slug
     * @return array Supported features
     */
    public function get_supported_features($post_type) {
        $all_supports = get_all_post_type_supports($post_type);

        if (!is_array($all_supports)) {
            return [];
        }

        return array_keys($all_supports);
    }

    /**
     * Get taxonomies associated with a post type
     *
     * @param string $post_type Post type slug
     * @return array Taxonomy data
     */
    public function get_taxonomies($post_type) {
        $taxonomy_names = get_object_taxonomies($post_type);
        $taxonomies = [];

        foreach ($taxonomy_names as $taxonomy_name) {
            $taxonomy_object = get_taxonomy($taxonomy_name);
            if ($taxonomy_object) {
                $taxonomies[$taxonomy_name] = [
                    'name' => $taxonomy_name,
                    'label' => $taxonomy_object->label,
                    'labels' => (array) $taxonomy_object->labels,
                    'public' => $taxonomy_object->public,
                    'hierarchical' => $taxonomy_object->hierarchical,
                    'show_ui' => $taxonomy_object->show_ui,
                    'show_in_rest' => $taxonomy_object->show_in_rest,
                    'rewrite' => $taxonomy_object->rewrite,
                ];
            }
        }

        return $taxonomies;
    }

    /**
     * Get archive settings for a post type
     *
     * @param string $post_type Post type slug
     * @return array Archive settings
     */
    public function get_archive_settings($post_type) {
        $cpt_object = get_post_type_object($post_type);

        if (!$cpt_object) {
            return [];
        }

        return [
            'has_archive' => $cpt_object->has_archive,
            'archive_slug' => is_string($cpt_object->has_archive) ? $cpt_object->has_archive : $post_type,
            'archive_url' => $cpt_object->has_archive ? get_post_type_archive_link($post_type) : '',
        ];
    }

    /**
     * Detect the source of CPT registration
     *
     * @param string $post_type Post type slug
     * @return string Source identifier
     */
    private function detect_registration_source($post_type) {
        // Check ACF
        if (function_exists('acf_get_post_types')) {
            $acf_types = acf_get_post_types();
            if (is_array($acf_types) && in_array($post_type, $acf_types)) {
                return 'acf';
            }
        }

        // Check Pods
        if (class_exists('Pods')) {
            $pod = pods($post_type, null, true);
            if ($pod && $pod->valid()) {
                return 'pods';
            }
        }

        // Check Toolset
        if (function_exists('wpcf_admin_fields_get_groups')) {
            $toolset_types = get_option('wpcf-custom-types', []);
            if (isset($toolset_types[$post_type])) {
                return 'toolset';
            }
        }

        // Check JetEngine
        if (class_exists('Jet_Engine')) {
            $jet_types = get_option('jet_engine_post_types', []);
            if (is_array($jet_types)) {
                foreach ($jet_types as $jet_type) {
                    if (isset($jet_type['slug']) && $jet_type['slug'] === $post_type) {
                        return 'jetengine';
                    }
                }
            }
        }

        // Check if registered in theme
        $theme = wp_get_theme();
        $theme_dir = $theme->get_stylesheet_directory();

        // This is a simple heuristic - could be improved
        if (strpos($post_type, get_stylesheet()) !== false) {
            return 'theme';
        }

        // Check if registered by a plugin (fallback)
        global $wp_post_types;
        if (isset($wp_post_types[$post_type])) {
            return 'code'; // Registered via code (could be plugin or theme)
        }

        return 'unknown';
    }

    /**
     * Get post count for a post type
     *
     * @param string $post_type Post type slug
     * @return int Post count
     */
    private function get_post_count($post_type) {
        $count = wp_count_posts($post_type);

        if (!$count) {
            return 0;
        }

        // Sum all post statuses
        $total = 0;
        foreach (get_object_vars($count) as $status_count) {
            $total += $status_count;
        }

        return $total;
    }

    /**
     * Get template files associated with a post type
     *
     * @param string $post_type Post type slug
     * @return array Template file paths
     */
    public function get_templates($post_type) {
        $templates = [];
        $theme_dir = get_stylesheet_directory();

        // Common template patterns
        $patterns = [
            "single-{$post_type}.php",
            "archive-{$post_type}.php",
            "taxonomy-{$post_type}.php",
            "content-{$post_type}.php",
        ];

        foreach ($patterns as $pattern) {
            $file_path = $theme_dir . '/' . $pattern;
            if (file_exists($file_path)) {
                $templates[] = $pattern;
            }
        }

        return $templates;
    }
}
