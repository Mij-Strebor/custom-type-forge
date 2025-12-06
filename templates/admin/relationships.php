<?php
/**
 * Relationships Page Template
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$analyzer = new CTF_Analyzer_Core();
$cached_data = $analyzer->get_cached_data();
$relationships = $cached_data !== false && isset($cached_data['relationships']) ? $cached_data['relationships'] : [];
?>

<div class="wrap ctf-admin-page">
    <!-- Forge Header Banner -->
    <div class="ctf-forge-header-section">
        <div class="ctf-forge-title-container">
            <h1 class="ctf-forge-title">Custom Type Forge</h1>
            <p class="ctf-forge-version">Version <?php echo CTF_VERSION; ?></p>
        </div>
    </div>

    <div class="ctf-container">

        <?php if (empty($relationships)): ?>
            <div class="ctf-notice ctf-notice-warning">
                <p><strong>No data available.</strong> Please <a href="<?php echo admin_url('admin.php?page=custom-type-forge'); ?>">run a scan</a> first.</p>
            </div>
        <?php else: ?>
            <!-- Summary Stats -->
            <?php if (isset($relationships['summary'])): ?>
            <div class="ctf-stats-grid">
                <div class="ctf-stat-card">
                    <div class="ctf-stat-number"><?php echo esc_html($relationships['summary']['total_cpt_relationships'] ?? 0); ?></div>
                    <div class="ctf-stat-label">CPT Relationships</div>
                </div>
                <div class="ctf-stat-card">
                    <div class="ctf-stat-number"><?php echo esc_html($relationships['summary']['total_taxonomy_relationships'] ?? 0); ?></div>
                    <div class="ctf-stat-label">Taxonomy Relationships</div>
                </div>
                <div class="ctf-stat-card">
                    <div class="ctf-stat-number"><?php echo esc_html($relationships['summary']['total_field_mappings'] ?? 0); ?></div>
                    <div class="ctf-stat-label">Field Mappings</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CPT to CPT Relationships -->
            <?php if (!empty($relationships['cpt_to_cpt'])): ?>
            <div class="ctf-panel">
                <h2>Post Type Relationships</h2>
                <table class="ctf-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Target</th>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Platform</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relationships['cpt_to_cpt'] as $rel): ?>
                        <tr>
                            <td><code><?php echo esc_html($rel['source']); ?></code></td>
                            <td><code><?php echo esc_html($rel['target']); ?></code></td>
                            <td><?php echo esc_html($rel['field_label']); ?></td>
                            <td><span class="ctf-badge"><?php echo esc_html($rel['field_type']); ?></span></td>
                            <td><span class="ctf-badge ctf-badge-platform-<?php echo esc_attr($rel['platform']); ?>"><?php echo esc_html($rel['platform']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Taxonomy Relationships -->
            <?php if (!empty($relationships['cpt_to_taxonomy'])): ?>
            <div class="ctf-panel">
                <h2>Taxonomy Relationships</h2>
                <table class="ctf-table">
                    <thead>
                        <tr>
                            <th>Post Type</th>
                            <th>Taxonomy</th>
                            <th>Hierarchical</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relationships['cpt_to_taxonomy'] as $rel): ?>
                        <tr>
                            <td><?php echo esc_html($rel['post_type_label']); ?> <code><?php echo esc_html($rel['post_type']); ?></code></td>
                            <td><?php echo esc_html($rel['taxonomy_label']); ?> <code><?php echo esc_html($rel['taxonomy']); ?></code></td>
                            <td><?php echo $rel['hierarchical'] ? '✓' : '—'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
