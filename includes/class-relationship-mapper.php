<?php
/**
 * Relationship Mapper Class
 *
 * Maps connections between CPTs, fields, and taxonomies
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Relationship Mapper
 */
class CTF_Relationship_Mapper {

    /**
     * Map all relationships between entities
     *
     * @param array $cpts All CPT data
     * @param array $field_groups All field group data
     * @return array Relationship map
     */
    public function map_all_relationships($cpts, $field_groups) {
        $relationships = [
            'cpt_to_cpt' => $this->map_cpt_to_cpt($field_groups),
            'cpt_to_taxonomy' => $this->map_cpt_to_taxonomy($cpts),
            'field_to_cpt' => $this->map_field_to_cpt($field_groups),
            'summary' => [],
        ];

        // Generate summary statistics
        $relationships['summary'] = $this->generate_summary($relationships, $cpts, $field_groups);

        return $relationships;
    }

    /**
     * Map CPT to CPT relationships (via relational fields)
     *
     * @param array $field_groups Field groups
     * @return array CPT relationships
     */
    private function map_cpt_to_cpt($field_groups) {
        $relationships = [];

        foreach ($field_groups as $group) {
            if (!isset($group['fields'])) {
                continue;
            }

            $source_location = $this->extract_post_type_from_location($group['location_rules']);

            foreach ($group['fields'] as $field) {
                if ($this->is_relational_field($field)) {
                    $target_post_types = $this->get_target_post_types($field);

                    foreach ($target_post_types as $target_pt) {
                        $relationships[] = [
                            'source' => $source_location,
                            'target' => $target_pt,
                            'field_name' => $field['name'],
                            'field_label' => $field['label'],
                            'field_type' => $field['type'],
                            'bidirectional' => isset($field['config']['bidirectional']) ? $field['config']['bidirectional'] : false,
                            'platform' => $group['platform'],
                        ];
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * Map CPT to Taxonomy relationships
     *
     * @param array $cpts CPT data
     * @return array Taxonomy relationships
     */
    private function map_cpt_to_taxonomy($cpts) {
        $relationships = [];

        foreach ($cpts as $cpt_slug => $cpt_data) {
            if (isset($cpt_data['taxonomies']) && is_array($cpt_data['taxonomies'])) {
                foreach ($cpt_data['taxonomies'] as $tax_slug => $tax_data) {
                    $relationships[] = [
                        'post_type' => $cpt_slug,
                        'post_type_label' => $cpt_data['label'],
                        'taxonomy' => $tax_slug,
                        'taxonomy_label' => $tax_data['label'],
                        'hierarchical' => $tax_data['hierarchical'],
                    ];
                }
            }
        }

        return $relationships;
    }

    /**
     * Map field groups to CPTs
     *
     * @param array $field_groups Field groups
     * @return array Field to CPT mappings
     */
    private function map_field_to_cpt($field_groups) {
        $mappings = [];

        foreach ($field_groups as $group) {
            $post_types = $this->extract_post_type_from_location($group['location_rules']);

            $mappings[] = [
                'field_group_id' => $group['id'],
                'field_group_title' => $group['title'],
                'platform' => $group['platform'],
                'post_types' => is_array($post_types) ? $post_types : [$post_types],
                'field_count' => isset($group['fields']) ? count($group['fields']) : 0,
            ];
        }

        return $mappings;
    }

    /**
     * Extract post type from location rules
     *
     * @param mixed $location_rules Location rules
     * @return string|array Post type(s)
     */
    private function extract_post_type_from_location($location_rules) {
        if (!is_array($location_rules)) {
            return 'unknown';
        }

        // Handle ACF location rules format
        if (isset($location_rules[0]) && is_array($location_rules[0])) {
            foreach ($location_rules[0] as $rule) {
                if (isset($rule['param']) && $rule['param'] === 'post_type') {
                    return $rule['value'];
                }
            }
        }

        // Handle simple post_type key
        if (isset($location_rules['post_type'])) {
            return $location_rules['post_type'];
        }

        return 'unknown';
    }

    /**
     * Check if field type is relational
     *
     * @param array $field Field data
     * @return bool True if relational field
     */
    private function is_relational_field($field) {
        $relational_types = [
            'post_object',
            'relationship',
            'page_link',
            'taxonomy',
            'user',
        ];

        return isset($field['type']) && in_array($field['type'], $relational_types);
    }

    /**
     * Get target post types from a relational field
     *
     * @param array $field Field data
     * @return array Target post types
     */
    private function get_target_post_types($field) {
        if (!isset($field['config'])) {
            return [];
        }

        $config = $field['config'];
        $targets = [];

        if (isset($config['post_type'])) {
            $targets = is_array($config['post_type']) ? $config['post_type'] : [$config['post_type']];
        }

        return $targets;
    }

    /**
     * Generate summary statistics
     *
     * @param array $relationships All relationships
     * @param array $cpts CPT data
     * @param array $field_groups Field group data
     * @return array Summary statistics
     */
    private function generate_summary($relationships, $cpts, $field_groups) {
        return [
            'total_cpts' => count($cpts),
            'total_field_groups' => count($field_groups),
            'total_cpt_relationships' => count($relationships['cpt_to_cpt']),
            'total_taxonomy_relationships' => count($relationships['cpt_to_taxonomy']),
            'total_field_mappings' => count($relationships['field_to_cpt']),
            'most_connected_cpt' => $this->find_most_connected_cpt($relationships['cpt_to_cpt']),
            'orphaned_cpts' => $this->find_orphaned_cpts($cpts, $relationships),
        ];
    }

    /**
     * Find the most connected CPT
     *
     * @param array $cpt_relationships CPT relationships
     * @return array Most connected CPT data
     */
    private function find_most_connected_cpt($cpt_relationships) {
        $connection_counts = [];

        foreach ($cpt_relationships as $rel) {
            $source = $rel['source'];

            if (!isset($connection_counts[$source])) {
                $connection_counts[$source] = 0;
            }

            $connection_counts[$source]++;
        }

        if (empty($connection_counts)) {
            return [
                'post_type' => 'none',
                'connection_count' => 0,
            ];
        }

        arsort($connection_counts);
        $most_connected = array_key_first($connection_counts);

        return [
            'post_type' => $most_connected,
            'connection_count' => $connection_counts[$most_connected],
        ];
    }

    /**
     * Find CPTs with no relationships
     *
     * @param array $cpts All CPTs
     * @param array $relationships All relationships
     * @return array Orphaned CPT slugs
     */
    private function find_orphaned_cpts($cpts, $relationships) {
        $connected_cpts = [];

        // Collect all CPTs mentioned in relationships
        foreach ($relationships['cpt_to_cpt'] as $rel) {
            $connected_cpts[$rel['source']] = true;
            $connected_cpts[$rel['target']] = true;
        }

        foreach ($relationships['cpt_to_taxonomy'] as $rel) {
            $connected_cpts[$rel['post_type']] = true;
        }

        // Find CPTs not in the connected list
        $orphaned = [];
        foreach ($cpts as $cpt_slug => $cpt_data) {
            if (!isset($connected_cpts[$cpt_slug])) {
                $orphaned[] = $cpt_slug;
            }
        }

        return $orphaned;
    }

    /**
     * Detect circular references (CPT A -> CPT B -> CPT A)
     *
     * @param array $cpt_relationships CPT relationships
     * @return array Circular reference chains
     */
    public function detect_circular_references($cpt_relationships) {
        $circular = [];
        $graph = [];

        // Build adjacency list
        foreach ($cpt_relationships as $rel) {
            $source = $rel['source'];
            $target = $rel['target'];

            if (!isset($graph[$source])) {
                $graph[$source] = [];
            }

            $graph[$source][] = $target;
        }

        // Detect cycles using DFS
        foreach (array_keys($graph) as $node) {
            $visited = [];
            $path = [];

            if ($this->has_cycle($node, $graph, $visited, $path)) {
                $circular[] = $path;
            }
        }

        return $circular;
    }

    /**
     * Check for cycles using Depth-First Search
     *
     * @param string $node Current node
     * @param array $graph Adjacency list
     * @param array $visited Visited nodes
     * @param array $path Current path
     * @return bool True if cycle detected
     */
    private function has_cycle($node, $graph, &$visited, &$path) {
        if (in_array($node, $path)) {
            return true; // Cycle detected
        }

        if (isset($visited[$node])) {
            return false; // Already checked
        }

        $visited[$node] = true;
        $path[] = $node;

        if (isset($graph[$node])) {
            foreach ($graph[$node] as $neighbor) {
                if ($this->has_cycle($neighbor, $graph, $visited, $path)) {
                    return true;
                }
            }
        }

        array_pop($path);

        return false;
    }
}
