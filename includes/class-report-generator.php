<?php
/**
 * Report Generator Class
 *
 * Compiles data into structured reports (HTML, JSON, Markdown)
 *
 * @package CustomTypeForge
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Report Generator
 */
class CTF_Report_Generator {

    /**
     * Scan data
     *
     * @var array
     */
    private $data;

    /**
     * Report upload directory
     *
     * @var string
     */
    private $upload_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $upload = wp_upload_dir();
        $this->upload_dir = trailingslashit($upload['basedir']) . 'ctf-reports';

        // Ensure directory exists
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }
    }

    /**
     * Generate report in specified format
     *
     * @param string $format Report format (html, json, markdown)
     * @param array $options Optional report options
     * @return array Report data with download URL
     */
    public function generate($format = 'html', $options = []) {
        // Get scan data
        $analyzer = new CTF_Analyzer_Core();
        $this->data = $analyzer->get_cached_data();

        if (false === $this->data) {
            // No cached data, run scan
            $this->data = $analyzer->scan_site();
        }

        $filename = $this->generate_filename($format);
        $filepath = $this->upload_dir . '/' . $filename;

        switch ($format) {
            case 'json':
                $content = $this->generate_json($options);
                break;

            case 'markdown':
                $content = $this->generate_markdown($options);
                break;

            case 'html':
            default:
                $content = $this->generate_html($options);
                break;
        }

        // Write file
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $wp_filesystem->put_contents($filepath, $content, FS_CHMOD_FILE);

        $upload = wp_upload_dir();
        $url = trailingslashit($upload['baseurl']) . 'ctf-reports/' . $filename;

        return [
            'filepath' => $filepath,
            'url' => $url,
            'filename' => $filename,
            'format' => $format,
        ];
    }

    /**
     * Generate unique filename
     *
     * @param string $format File format
     * @return string Filename
     */
    private function generate_filename($format) {
        $site_name = sanitize_title(get_bloginfo('name'));
        $timestamp = current_time('Y-m-d-His');
        $extension = $format === 'markdown' ? 'md' : $format;

        return "ctf-report-{$site_name}-{$timestamp}.{$extension}";
    }

    /**
     * Generate HTML report
     *
     * @param array $options Report options
     * @return string HTML content
     */
    private function generate_html($options = []) {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPT & Field Analysis Report - <?php echo esc_html(get_bloginfo('name')); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #6d4c2f;
            background: #faf6f0;
            padding: 40px 20px;
        }
        .container { max-width: 1280px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(61, 47, 31, 0.12); }
        h1 { color: #3d2f1f; font-size: 32px; font-weight: 700; margin-bottom: 20px; }
        h2 { color: #3d2f1f; font-size: 24px; font-weight: 700; margin: 30px 0 15px; border-bottom: 2px solid #f4c542; padding-bottom: 10px; }
        h3 { color: #3d2f1f; font-size: 20px; font-weight: 600; margin: 20px 0 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #c9b89a; }
        th { background: #f4c542; color: #3d2f1f; font-weight: 600; }
        tr:nth-child(even) { background: #faf9f6; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #faf9f6; padding: 20px; border-radius: 6px; border-left: 4px solid #f4c542; }
        .stat-number { font-size: 32px; font-weight: 700; color: #3d2f1f; }
        .stat-label { font-size: 14px; color: #6d4c2f; margin-top: 5px; }
        .field-type { display: inline-block; padding: 4px 8px; background: #e7e3df; border-radius: 4px; font-size: 12px; font-weight: 600; margin-right: 5px; }
        .platform-badge { display: inline-block; padding: 4px 12px; background: #3d2f1f; color: #fff; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .cpt-section { margin: 30px 0; padding: 20px; background: #faf9f6; border-radius: 6px; }
        code { background: #e7e3df; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; font-size: 14px; }
        ul { margin: 10px 0 10px 30px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #c9b89a; font-size: 14px; color: #6d4c2f; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Custom Post Type & Field Analysis Report</h1>
        <p><strong>Site:</strong> <?php echo esc_html(get_bloginfo('name')); ?></p>
        <p><strong>URL:</strong> <?php echo esc_url(get_site_url()); ?></p>
        <p><strong>Generated:</strong> <?php echo esc_html($this->data['metadata']['scan_date']); ?></p>
        <p><strong>WordPress Version:</strong> <?php echo esc_html($this->data['metadata']['wp_version']); ?></p>

        <h2>Executive Summary</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($this->data['cpts']); ?></div>
                <div class="stat-label">Custom Post Types</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($this->data['field_groups']); ?></div>
                <div class="stat-label">Field Groups</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($this->data['platforms_detected']); ?></div>
                <div class="stat-label">Platforms Detected</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($this->data['relationships']['summary']['total_cpt_relationships']) ? $this->data['relationships']['summary']['total_cpt_relationships'] : 0; ?></div>
                <div class="stat-label">Relationships</div>
            </div>
        </div>

        <h2>Detected Platforms</h2>
        <?php foreach ($this->data['platforms_detected'] as $platform_key => $platform): ?>
            <span class="platform-badge"><?php echo esc_html($platform['name']); ?> <?php echo isset($platform['version']) ? esc_html($platform['version']) : ''; ?></span>
        <?php endforeach; ?>

        <h2>Custom Post Types</h2>
        <?php foreach ($this->data['cpts'] as $cpt_slug => $cpt): ?>
            <div class="cpt-section">
                <h3><?php echo esc_html($cpt['label']); ?> <code><?php echo esc_html($cpt_slug); ?></code></h3>
                <p><strong>Source:</strong> <?php echo esc_html($cpt['source']); ?></p>
                <p><strong>Post Count:</strong> <?php echo esc_html($cpt['post_count']); ?></p>

                <table>
                    <tr><th>Property</th><th>Value</th></tr>
                    <tr><td>Public</td><td><?php echo $cpt['public'] ? 'Yes' : 'No'; ?></td></tr>
                    <tr><td>Hierarchical</td><td><?php echo $cpt['hierarchical'] ? 'Yes' : 'No'; ?></td></tr>
                    <tr><td>REST Enabled</td><td><?php echo $cpt['show_in_rest'] ? 'Yes' : 'No'; ?></td></tr>
                    <tr><td>Has Archive</td><td><?php echo $cpt['has_archive'] ? 'Yes' : 'No'; ?></td></tr>
                </table>

                <?php if (!empty($cpt['supports'])): ?>
                <p><strong>Supports:</strong> <?php echo implode(', ', $cpt['supports']); ?></p>
                <?php endif; ?>

                <?php if (!empty($cpt['taxonomies'])): ?>
                <p><strong>Taxonomies:</strong></p>
                <ul>
                    <?php foreach ($cpt['taxonomies'] as $tax_slug => $tax): ?>
                        <li><?php echo esc_html($tax['label']); ?> <code><?php echo esc_html($tax_slug); ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <h2>Field Groups</h2>
        <?php foreach ($this->data['field_groups'] as $group_id => $group): ?>
            <div class="cpt-section">
                <h3><?php echo esc_html($group['title']); ?></h3>
                <p><span class="platform-badge"><?php echo esc_html($group['platform']); ?></span></p>
                <p><strong>Field Count:</strong> <?php echo count($group['fields']); ?></p>

                <?php if (!empty($group['fields'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Label</th>
                            <th>Type</th>
                            <th>Required</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['fields'] as $field): ?>
                        <tr>
                            <td><code><?php echo esc_html($field['name']); ?></code></td>
                            <td><?php echo esc_html($field['label']); ?></td>
                            <td><span class="field-type"><?php echo esc_html($field['type']); ?></span></td>
                            <td><?php echo isset($field['required']) && $field['required'] ? 'Yes' : 'No'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="footer">
            <p>Generated by Custom Type Forge v<?php echo CTF_VERSION; ?> | Jim R Forge | <a href="https://jimrforge.com">jimrforge.com</a></p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate JSON report
     *
     * @param array $options Report options
     * @return string JSON content
     */
    private function generate_json($options = []) {
        return wp_json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate Markdown report
     *
     * @param array $options Report options
     * @return string Markdown content
     */
    private function generate_markdown($options = []) {
        $md = "# Custom Post Type & Field Analysis Report\n\n";
        $md .= "**Site:** " . get_bloginfo('name') . "\n";
        $md .= "**URL:** " . get_site_url() . "\n";
        $md .= "**Generated:** " . $this->data['metadata']['scan_date'] . "\n";
        $md .= "**WordPress Version:** " . $this->data['metadata']['wp_version'] . "\n\n";

        $md .= "## Executive Summary\n\n";
        $md .= "- **Custom Post Types:** " . count($this->data['cpts']) . "\n";
        $md .= "- **Field Groups:** " . count($this->data['field_groups']) . "\n";
        $md .= "- **Platforms Detected:** " . count($this->data['platforms_detected']) . "\n";
        $md .= "- **Relationships:** " . (isset($this->data['relationships']['summary']['total_cpt_relationships']) ? $this->data['relationships']['summary']['total_cpt_relationships'] : 0) . "\n\n";

        $md .= "## Detected Platforms\n\n";
        foreach ($this->data['platforms_detected'] as $platform_key => $platform) {
            $md .= "- **" . $platform['name'] . "**";
            if (isset($platform['version'])) {
                $md .= " (v" . $platform['version'] . ")";
            }
            $md .= "\n";
        }
        $md .= "\n";

        $md .= "## Custom Post Types\n\n";
        foreach ($this->data['cpts'] as $cpt_slug => $cpt) {
            $md .= "### " . $cpt['label'] . " (`" . $cpt_slug . "`)\n\n";
            $md .= "- **Source:** " . $cpt['source'] . "\n";
            $md .= "- **Post Count:** " . $cpt['post_count'] . "\n";
            $md .= "- **Public:** " . ($cpt['public'] ? 'Yes' : 'No') . "\n";
            $md .= "- **Hierarchical:** " . ($cpt['hierarchical'] ? 'Yes' : 'No') . "\n";
            $md .= "- **REST Enabled:** " . ($cpt['show_in_rest'] ? 'Yes' : 'No') . "\n";
            $md .= "- **Has Archive:** " . ($cpt['has_archive'] ? 'Yes' : 'No') . "\n";

            if (!empty($cpt['supports'])) {
                $md .= "- **Supports:** " . implode(', ', $cpt['supports']) . "\n";
            }

            if (!empty($cpt['taxonomies'])) {
                $md .= "\n**Taxonomies:**\n\n";
                foreach ($cpt['taxonomies'] as $tax_slug => $tax) {
                    $md .= "- " . $tax['label'] . " (`" . $tax_slug . "`)\n";
                }
            }

            $md .= "\n";
        }

        $md .= "## Field Groups\n\n";
        foreach ($this->data['field_groups'] as $group_id => $group) {
            $md .= "### " . $group['title'] . "\n\n";
            $md .= "- **Platform:** " . $group['platform'] . "\n";
            $md .= "- **Field Count:** " . count($group['fields']) . "\n\n";

            if (!empty($group['fields'])) {
                $md .= "**Fields:**\n\n";
                $md .= "| Field Name | Label | Type | Required |\n";
                $md .= "|------------|-------|------|----------|\n";

                foreach ($group['fields'] as $field) {
                    $md .= "| `" . $field['name'] . "` | " . $field['label'] . " | " . $field['type'] . " | ";
                    $md .= (isset($field['required']) && $field['required'] ? 'Yes' : 'No') . " |\n";
                }

                $md .= "\n";
            }
        }

        $md .= "---\n\n";
        $md .= "*Generated by Custom Type Forge v" . CTF_VERSION . " | Jim R Forge | [jimrforge.com](https://jimrforge.com)*\n";

        return $md;
    }
}
