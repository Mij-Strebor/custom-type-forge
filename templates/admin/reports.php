<?php
/**
 * Reports Page Template
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$analyzer = new CTF_Analyzer_Core();
$has_cache = $analyzer->has_cache();
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

        <?php if (!$has_cache): ?>
            <div class="ctf-notice ctf-notice-warning">
                <p><strong>No scan data available.</strong> Please <a href="<?php echo admin_url('admin.php?page=custom-type-forge'); ?>">run a scan</a> before generating reports.</p>
            </div>
        <?php else: ?>
            <div class="ctf-panel">
                <h2>Select Report Format</h2>

                <div class="ctf-report-options">
                    <label class="ctf-report-format-option">
                        <input type="radio" name="report_format" value="html" checked>
                        <div class="ctf-format-card">
                            <span class="dashicons dashicons-media-code"></span>
                            <h3>HTML Report</h3>
                            <p>Interactive, styled report with tables and statistics</p>
                        </div>
                    </label>

                    <label class="ctf-report-format-option">
                        <input type="radio" name="report_format" value="json">
                        <div class="ctf-format-card">
                            <span class="dashicons dashicons-media-code"></span>
                            <h3>JSON Export</h3>
                            <p>Machine-readable data for programmatic use</p>
                        </div>
                    </label>

                    <label class="ctf-report-format-option">
                        <input type="radio" name="report_format" value="markdown">
                        <div class="ctf-format-card">
                            <span class="dashicons dashicons-media-text"></span>
                            <h3>Markdown Export</h3>
                            <p>Plain text documentation for GitHub, wikis, etc.</p>
                        </div>
                    </label>
                </div>

                <div class="ctf-btn-group" style="margin-top: 30px;">
                    <button type="button" class="ctf-btn" id="btn-generate-report">
                        <span class="dashicons dashicons-download"></span>
                        generate report
                    </button>
                </div>

                <div id="report-progress" class="ctf-progress" style="display: none;">
                    <div class="ctf-progress-bar">
                        <div class="ctf-progress-fill"></div>
                    </div>
                    <p class="ctf-progress-message">Generating report...</p>
                </div>

                <div id="report-result" class="ctf-result" style="display: none;"></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#btn-generate-report').on('click', function() {
        const $btn = $(this);
        const $progress = $('#report-progress');
        const $result = $('#report-result');
        const format = $('input[name="report_format"]:checked').val();

        $btn.prop('disabled', true);
        $progress.show();
        $result.hide();

        $.ajax({
            url: ctfAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ctf_generate_report',
                nonce: ctfAjax.nonce,
                format: format
            },
            success: function(response) {
                if (response.success) {
                    const downloadUrl = response.data.download_url;
                    $result.html(
                        '<div class="ctf-notice ctf-notice-success">' +
                        '<p><strong>Report generated successfully!</strong></p>' +
                        '<p><a href="' + downloadUrl + '" class="ctf-btn" download>download report</a></p>' +
                        '</div>'
                    );
                } else {
                    $result.html('<div class="ctf-notice ctf-notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="ctf-notice ctf-notice-error"><p>Report generation failed. Please try again.</p></div>');
            },
            complete: function() {
                $progress.hide();
                $result.show();
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
