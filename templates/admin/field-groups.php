<?php
/**
 * Field Groups Page Template
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$analyzer = new CTF_Analyzer_Core();
$cached_data = $analyzer->get_cached_data();
$field_groups = $cached_data !== false ? $cached_data['field_groups'] : [];
?>

<div class="wrap ctf-admin-page">
    <div class="ctf-container">
        <h1 class="ctf-page-title">Field Groups</h1>
        <p class="ctf-page-subtitle">All custom field groups detected across all platforms</p>

        <?php if (empty($field_groups)): ?>
            <div class="ctf-notice ctf-notice-warning">
                <p><strong>No data available.</strong> Please <a href="<?php echo admin_url('admin.php?page=custom-type-forge'); ?>">run a scan</a> first.</p>
            </div>
        <?php else: ?>
            <div class="ctf-panel">
                <table class="ctf-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Platform</th>
                            <th>Fields</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($field_groups as $group_id => $group): ?>
                        <tr>
                            <td><strong><?php echo esc_html($group['title']); ?></strong></td>
                            <td><span class="ctf-badge ctf-badge-platform-<?php echo esc_attr($group['platform']); ?>"><?php echo esc_html($group['platform']); ?></span></td>
                            <td><?php echo count($group['fields']); ?> fields</td>
                            <td><?php echo isset($group['active']) && $group['active'] ? '<span class="ctf-status-active">Active</span>' : '<span class="ctf-status-inactive">Inactive</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
