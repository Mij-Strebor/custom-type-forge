<?php
/**
 * ACF Detector Class
 *
 * Discovers and analyzes all custom fields across multiple platforms
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Custom Fields Detector
 */
class CTF_ACF_Detector {

    /**
     * Detect all field groups across all platforms
     *
     * @return array Field group data
     */
    public function detect_all_field_groups() {
        $field_groups = [];

        // Detect ACF field groups
        $field_groups = array_merge($field_groups, $this->detect_acf_fields());

        // Detect Pods fields
        $field_groups = array_merge($field_groups, $this->detect_pods_fields());

        // Detect Toolset fields
        $field_groups = array_merge($field_groups, $this->detect_toolset_fields());

        // Detect Meta Box fields
        $field_groups = array_merge($field_groups, $this->detect_metabox_fields());

        // Detect JetEngine fields
        $field_groups = array_merge($field_groups, $this->detect_jetengine_fields());

        // Detect native WordPress custom fields
        $field_groups = array_merge($field_groups, $this->detect_native_fields());

        return $field_groups;
    }

    /**
     * Detect ACF field groups
     *
     * @return array ACF field groups
     */
    private function detect_acf_fields() {
        if (!function_exists('acf_get_field_groups')) {
            return [];
        }

        $field_groups = [];
        $acf_groups = acf_get_field_groups();

        foreach ($acf_groups as $group) {
            $fields = acf_get_fields($group['key']);

            $field_groups[$group['key']] = [
                'id' => $group['key'],
                'title' => $group['title'],
                'platform' => 'acf',
                'location_rules' => $group['location'],
                'fields' => $this->parse_acf_fields($fields),
                'active' => $group['active'],
                'style' => isset($group['style']) ? $group['style'] : 'default',
                'position' => isset($group['position']) ? $group['position'] : 'normal',
            ];
        }

        return $field_groups;
    }

    /**
     * Parse ACF fields into standardized format
     *
     * @param array $fields ACF fields array
     * @return array Parsed fields
     */
    private function parse_acf_fields($fields) {
        if (!is_array($fields)) {
            return [];
        }

        $parsed_fields = [];

        foreach ($fields as $field) {
            $parsed_fields[$field['key']] = [
                'key' => $field['key'],
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'instructions' => isset($field['instructions']) ? $field['instructions'] : '',
                'required' => isset($field['required']) ? $field['required'] : false,
                'default_value' => isset($field['default_value']) ? $field['default_value'] : '',
                'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : '',
                'conditional_logic' => isset($field['conditional_logic']) ? $field['conditional_logic'] : '',
                'wrapper' => isset($field['wrapper']) ? $field['wrapper'] : [],
                'config' => $this->get_field_type_config($field),
            ];

            // Parse sub-fields for repeater, group, flexible content
            if (in_array($field['type'], ['repeater', 'group', 'flexible_content']) && isset($field['sub_fields'])) {
                $parsed_fields[$field['key']]['sub_fields'] = $this->parse_acf_fields($field['sub_fields']);
            }
        }

        return $parsed_fields;
    }

    /**
     * Get field type specific configuration
     *
     * @param array $field Field array
     * @return array Type-specific config
     */
    private function get_field_type_config($field) {
        $config = [];

        switch ($field['type']) {
            case 'select':
            case 'checkbox':
            case 'radio':
                $config['choices'] = isset($field['choices']) ? $field['choices'] : [];
                $config['multiple'] = isset($field['multiple']) ? $field['multiple'] : false;
                break;

            case 'post_object':
            case 'relationship':
                $config['post_type'] = isset($field['post_type']) ? $field['post_type'] : [];
                $config['taxonomy'] = isset($field['taxonomy']) ? $field['taxonomy'] : [];
                $config['multiple'] = isset($field['multiple']) ? $field['multiple'] : false;
                break;

            case 'taxonomy':
                $config['taxonomy'] = isset($field['taxonomy']) ? $field['taxonomy'] : '';
                $config['field_type'] = isset($field['field_type']) ? $field['field_type'] : 'select';
                break;

            case 'image':
            case 'file':
                $config['return_format'] = isset($field['return_format']) ? $field['return_format'] : 'array';
                $config['mime_types'] = isset($field['mime_types']) ? $field['mime_types'] : '';
                break;

            case 'repeater':
                $config['min'] = isset($field['min']) ? $field['min'] : 0;
                $config['max'] = isset($field['max']) ? $field['max'] : 0;
                $config['layout'] = isset($field['layout']) ? $field['layout'] : 'table';
                break;
        }

        return $config;
    }

    /**
     * Detect Pods fields
     *
     * @return array Pods field groups
     */
    private function detect_pods_fields() {
        if (!class_exists('Pods')) {
            return [];
        }

        $field_groups = [];
        $pods_api = pods_api();
        $all_pods = $pods_api->load_pods();

        if (!is_array($all_pods)) {
            return [];
        }

        foreach ($all_pods as $pod) {
            $pod_name = $pod['name'];
            $pod_object = pods($pod_name, null, true);

            if (!$pod_object || !$pod_object->valid()) {
                continue;
            }

            $fields = $pod['fields'];
            $parsed_fields = [];

            foreach ($fields as $field_name => $field_data) {
                $parsed_fields[$field_name] = [
                    'key' => $field_name,
                    'name' => $field_name,
                    'label' => isset($field_data['label']) ? $field_data['label'] : $field_name,
                    'type' => isset($field_data['type']) ? $field_data['type'] : 'text',
                    'required' => isset($field_data['required']) ? $field_data['required'] : false,
                    'config' => $field_data,
                ];
            }

            $field_groups[$pod_name] = [
                'id' => $pod_name,
                'title' => $pod['label'],
                'platform' => 'pods',
                'location_rules' => ['post_type' => $pod_name],
                'fields' => $parsed_fields,
                'active' => true,
            ];
        }

        return $field_groups;
    }

    /**
     * Detect Toolset fields
     *
     * @return array Toolset field groups
     */
    private function detect_toolset_fields() {
        if (!function_exists('wpcf_admin_fields_get_groups')) {
            return [];
        }

        $field_groups = [];
        $toolset_groups = wpcf_admin_fields_get_groups();

        if (!is_array($toolset_groups)) {
            return [];
        }

        foreach ($toolset_groups as $group) {
            $fields = wpcf_admin_fields_get_fields_by_group($group['id']);
            $parsed_fields = [];

            if (is_array($fields)) {
                foreach ($fields as $field_key => $field_data) {
                    $parsed_fields[$field_key] = [
                        'key' => $field_key,
                        'name' => $field_data['slug'],
                        'label' => $field_data['name'],
                        'type' => $field_data['type'],
                        'config' => $field_data,
                    ];
                }
            }

            $field_groups[$group['id']] = [
                'id' => $group['id'],
                'title' => $group['name'],
                'platform' => 'toolset',
                'location_rules' => isset($group['post_types']) ? ['post_type' => $group['post_types']] : [],
                'fields' => $parsed_fields,
                'active' => true,
            ];
        }

        return $field_groups;
    }

    /**
     * Detect Meta Box fields
     *
     * @return array Meta Box field groups
     */
    private function detect_metabox_fields() {
        if (!function_exists('rwmb_get_registry')) {
            return [];
        }

        $field_groups = [];
        $registry = rwmb_get_registry('meta_box');
        $meta_boxes = $registry->all();

        if (!is_array($meta_boxes)) {
            return [];
        }

        foreach ($meta_boxes as $meta_box_id => $meta_box) {
            $parsed_fields = [];

            if (isset($meta_box->meta_box['fields'])) {
                foreach ($meta_box->meta_box['fields'] as $field) {
                    $parsed_fields[$field['id']] = [
                        'key' => $field['id'],
                        'name' => $field['id'],
                        'label' => isset($field['name']) ? $field['name'] : $field['id'],
                        'type' => $field['type'],
                        'config' => $field,
                    ];
                }
            }

            $field_groups[$meta_box_id] = [
                'id' => $meta_box_id,
                'title' => $meta_box->meta_box['title'],
                'platform' => 'metabox',
                'location_rules' => isset($meta_box->meta_box['post_types']) ? ['post_type' => $meta_box->meta_box['post_types']] : [],
                'fields' => $parsed_fields,
                'active' => true,
            ];
        }

        return $field_groups;
    }

    /**
     * Detect JetEngine fields
     *
     * @return array JetEngine field groups
     */
    private function detect_jetengine_fields() {
        if (!class_exists('Jet_Engine')) {
            return [];
        }

        $field_groups = [];
        $meta_boxes = get_option('jet_engine_meta_boxes', []);

        if (!is_array($meta_boxes)) {
            return [];
        }

        foreach ($meta_boxes as $meta_box) {
            $parsed_fields = [];

            if (isset($meta_box['meta_fields'])) {
                foreach ($meta_box['meta_fields'] as $field) {
                    $parsed_fields[$field['name']] = [
                        'key' => $field['name'],
                        'name' => $field['name'],
                        'label' => isset($field['title']) ? $field['title'] : $field['name'],
                        'type' => $field['type'],
                        'config' => $field,
                    ];
                }
            }

            $field_groups[$meta_box['id']] = [
                'id' => $meta_box['id'],
                'title' => $meta_box['args']['name'],
                'platform' => 'jetengine',
                'location_rules' => isset($meta_box['args']['post_type']) ? ['post_type' => $meta_box['args']['post_type']] : [],
                'fields' => $parsed_fields,
                'active' => true,
            ];
        }

        return $field_groups;
    }

    /**
     * Detect native WordPress custom fields
     *
     * @return array Native custom fields (basic detection)
     */
    private function detect_native_fields() {
        // This is a basic implementation
        // Native custom fields don't have "groups" - they're just meta keys
        // We'll return an empty array for now as this would require
        // scanning all post meta keys which could be expensive

        return [];
    }

    /**
     * Get field group details by ID
     *
     * @param string $group_id Field group ID
     * @return array Field group details
     */
    public function get_field_group_details($group_id) {
        $all_groups = $this->detect_all_field_groups();

        return isset($all_groups[$group_id]) ? $all_groups[$group_id] : [];
    }

    /**
     * Get location rules for a field group
     *
     * @param string $group_id Field group ID
     * @return array Location rules
     */
    public function get_location_rules($group_id) {
        $group = $this->get_field_group_details($group_id);

        return isset($group['location_rules']) ? $group['location_rules'] : [];
    }

    /**
     * Analyze conditional logic for a field
     *
     * @param array $field Field data
     * @return array Conditional logic analysis
     */
    public function analyze_conditional_logic($field) {
        if (!isset($field['conditional_logic']) || empty($field['conditional_logic'])) {
            return [
                'has_logic' => false,
            ];
        }

        $logic = $field['conditional_logic'];

        return [
            'has_logic' => true,
            'rules' => $logic,
            'complexity' => $this->calculate_logic_complexity($logic),
        ];
    }

    /**
     * Calculate complexity of conditional logic
     *
     * @param array $logic Conditional logic rules
     * @return string Complexity level (simple, moderate, complex)
     */
    private function calculate_logic_complexity($logic) {
        if (!is_array($logic)) {
            return 'none';
        }

        $rule_count = 0;
        foreach ($logic as $group) {
            $rule_count += count($group);
        }

        if ($rule_count <= 2) {
            return 'simple';
        } elseif ($rule_count <= 5) {
            return 'moderate';
        } else {
            return 'complex';
        }
    }
}
