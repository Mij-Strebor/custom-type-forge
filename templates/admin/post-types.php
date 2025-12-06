<?php
/**
 * Post Types Page Template
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$analyzer = new CTF_Analyzer_Core();
$cached_data = $analyzer->get_cached_data();
$cpts = $cached_data !== false ? $cached_data['cpts'] : [];
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

        <?php if (empty($cpts)): ?>
            <div class="ctf-notice ctf-notice-warning">
                <p><strong>No data available.</strong> Please <a href="<?php echo admin_url('admin.php?page=custom-type-forge'); ?>">run a scan</a> first.</p>
            </div>
        <?php else: ?>
            <div class="ctf-panel">
                <table class="ctf-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Source</th>
                            <th>Posts</th>
                            <th>Public</th>
                            <th>REST</th>
                            <th>Taxonomies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cpts as $slug => $cpt): ?>
                        <tr>
                            <td><strong><?php echo esc_html($cpt['label']); ?></strong></td>
                            <td><code><?php echo esc_html($slug); ?></code></td>
                            <td><span class="ctf-badge"><?php echo esc_html($cpt['source']); ?></span></td>
                            <td><?php echo esc_html($cpt['post_count']); ?></td>
                            <td><?php echo $cpt['public'] ? '✓' : '—'; ?></td>
                            <td><?php echo $cpt['show_in_rest'] ? '✓' : '—'; ?></td>
                            <td><?php echo !empty($cpt['taxonomies']) ? count($cpt['taxonomies']) : '—'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
