<?php
/**
 * Dashboard Page Template
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$analyzer = new CTF_Analyzer_Core();
$cached_data = $analyzer->get_cached_data();
$has_cache = $cached_data !== false;
$cache_expiration = $analyzer->get_cache_expiration();
?>

<div class="wrap ctf-admin-page">
    <div class="ctf-container">
        <h1 class="ctf-page-title">Custom Type Forge</h1>
        <p class="ctf-page-subtitle">Comprehensive analyzer for Custom Post Types and Advanced Custom Fields</p>

        <!-- Quick Stats -->
        <div class="ctf-stats-grid">
            <div class="ctf-stat-card">
                <div class="ctf-stat-number" id="stat-cpts">
                    <?php echo $has_cache ? count($cached_data['cpts']) : '—'; ?>
                </div>
                <div class="ctf-stat-label">Custom Post Types</div>
            </div>
            <div class="ctf-stat-card">
                <div class="ctf-stat-number" id="stat-field-groups">
                    <?php echo $has_cache ? count($cached_data['field_groups']) : '—'; ?>
                </div>
                <div class="ctf-stat-label">Field Groups</div>
            </div>
            <div class="ctf-stat-card">
                <div class="ctf-stat-number" id="stat-platforms">
                    <?php echo $has_cache ? count($cached_data['platforms_detected']) : '—'; ?>
                </div>
                <div class="ctf-stat-label">Platforms Detected</div>
            </div>
            <div class="ctf-stat-card">
                <div class="ctf-stat-number" id="stat-relationships">
                    <?php echo ($has_cache && isset($cached_data['relationships']['summary']['total_cpt_relationships'])) ? $cached_data['relationships']['summary']['total_cpt_relationships'] : '—'; ?>
                </div>
                <div class="ctf-stat-label">Relationships</div>
            </div>
        </div>

        <!-- Scan Control Panel -->
        <div class="ctf-panel">
            <h2>Scan Control</h2>

            <?php if ($has_cache): ?>
                <div class="ctf-notice ctf-notice-info">
                    <p>
                        <strong>Last scan:</strong> <?php echo esc_html($cached_data['metadata']['scan_date']); ?>
                        <?php if ($cache_expiration): ?>
                            <br><strong>Cache expires:</strong> <?php echo esc_html(date('Y-m-d H:i:s', $cache_expiration)); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="ctf-notice ctf-notice-warning">
                    <p><strong>No scan data available.</strong> Click "scan now" to analyze your site.</p>
                </div>
            <?php endif; ?>

            <div class="ctf-btn-group">
                <button type="button" class="ctf-btn" id="btn-scan-now">
                    <span class="dashicons dashicons-search"></span>
                    scan now
                </button>
                <button type="button" class="ctf-btn ctf-btn-secondary" id="btn-clear-cache">
                    <span class="dashicons dashicons-trash"></span>
                    clear cache
                </button>
            </div>

            <div id="scan-progress" class="ctf-progress" style="display: none;">
                <div class="ctf-progress-bar">
                    <div class="ctf-progress-fill"></div>
                </div>
                <p class="ctf-progress-message">Scanning site...</p>
            </div>

            <div id="scan-result" class="ctf-result" style="display: none;"></div>
        </div>

        <!-- Detected Platforms -->
        <?php if ($has_cache && !empty($cached_data['platforms_detected'])): ?>
        <div class="ctf-panel">
            <h2>Detected Platforms</h2>
            <div class="ctf-platform-grid">
                <?php foreach ($cached_data['platforms_detected'] as $platform_key => $platform): ?>
                    <div class="ctf-platform-card">
                        <div class="ctf-platform-icon">
                            <?php
                            $icon_class = 'dashicons-admin-plugins';
                            if ($platform_key === 'acf') $icon_class = 'dashicons-editor-table';
                            if ($platform_key === 'native') $icon_class = 'dashicons-wordpress';
                            ?>
                            <span class="dashicons <?php echo esc_attr($icon_class); ?>"></span>
                        </div>
                        <div class="ctf-platform-info">
                            <h3><?php echo esc_html($platform['name']); ?></h3>
                            <?php if (isset($platform['version'])): ?>
                                <p class="ctf-platform-version">v<?php echo esc_html($platform['version']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($platform['pro']) && $platform['pro']): ?>
                                <span class="ctf-badge ctf-badge-pro">PRO</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="ctf-panel">
            <h2>Quick Actions</h2>
            <div class="ctf-action-grid">
                <a href="<?php echo admin_url('admin.php?page=ctf-post-types'); ?>" class="ctf-action-card">
                    <span class="dashicons dashicons-admin-post"></span>
                    <h3>View Post Types</h3>
                    <p>See all custom post types detected on your site</p>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ctf-field-groups'); ?>" class="ctf-action-card">
                    <span class="dashicons dashicons-list-view"></span>
                    <h3>View Field Groups</h3>
                    <p>Explore all custom field groups and their configurations</p>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ctf-relationships'); ?>" class="ctf-action-card">
                    <span class="dashicons dashicons-networking"></span>
                    <h3>View Relationships</h3>
                    <p>Analyze connections between post types and taxonomies</p>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ctf-reports'); ?>" class="ctf-action-card">
                    <span class="dashicons dashicons-media-document"></span>
                    <h3>Generate Reports</h3>
                    <p>Export comprehensive reports in multiple formats</p>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Scan Now button
    $('#btn-scan-now').on('click', function() {
        const $btn = $(this);
        const $progress = $('#scan-progress');
        const $result = $('#scan-result');

        $btn.prop('disabled', true);
        $progress.show();
        $result.hide();

        $.ajax({
            url: ctfAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ctf_scan_site',
                nonce: ctfAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="ctf-notice ctf-notice-success"><p>' + response.data.message + '</p></div>');
                    // Update stats
                    if (response.data.data) {
                        $('#stat-cpts').text(Object.keys(response.data.data.cpts || {}).length);
                        $('#stat-field-groups').text(Object.keys(response.data.data.field_groups || {}).length);
                        $('#stat-platforms').text(Object.keys(response.data.data.platforms_detected || {}).length);
                        $('#stat-relationships').text(response.data.data.relationships?.summary?.total_cpt_relationships || 0);
                    }
                    // Reload page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $result.html('<div class="ctf-notice ctf-notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="ctf-notice ctf-notice-error"><p>Scan failed. Please try again.</p></div>');
            },
            complete: function() {
                $progress.hide();
                $result.show();
                $btn.prop('disabled', false);
            }
        });
    });

    // Clear Cache button
    $('#btn-clear-cache').on('click', function() {
        const $btn = $(this);
        const $result = $('#scan-result');

        if (!confirm('Are you sure you want to clear the cached scan data?')) {
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: ctfAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ctf_clear_cache',
                nonce: ctfAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="ctf-notice ctf-notice-success"><p>' + response.data.message + '</p></div>').show();
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $result.html('<div class="ctf-notice ctf-notice-error"><p>' + response.data.message + '</p></div>').show();
                }
            },
            error: function() {
                $result.html('<div class="ctf-notice ctf-notice-error"><p>Cache clear failed. Please try again.</p></div>').show();
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
