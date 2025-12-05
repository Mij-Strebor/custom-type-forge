# Custom Type Forge

**Comprehensive analyzer and documentation generator for WordPress Custom Post Types and Advanced Custom Fields.**

## Description

Custom Type Forge is a powerful WordPress plugin designed to provide comprehensive documentation and analysis of all Custom Post Types (CPTs) and Advanced Custom Fields (ACF) configurations within a WordPress installation.

Perfect for:
- Website developers taking over existing projects
- Development teams documenting their work
- Site audits and maintenance planning
- Migration planning and execution
- Training and onboarding new team members

## Features

### Core Capabilities

- **Multi-Platform Support**: Detects and analyzes custom post types and fields from:
  - Advanced Custom Fields (Free & Pro)
  - Pods Framework
  - Toolset Types
  - Meta Box
  - JetEngine
  - Native WordPress

- **Comprehensive Analysis**:
  - Complete CPT configuration details
  - Field group mappings and relationships
  - Taxonomy associations
  - Post type capabilities
  - REST API settings
  - Template dependencies

- **Relationship Mapping**:
  - CPT-to-CPT relationships
  - Taxonomy relationships
  - Field group assignments
  - Dependency analysis

- **Multiple Export Formats**:
  - HTML reports (styled, interactive)
  - JSON exports (machine-readable)
  - Markdown documentation (GitHub-ready)

- **Smart Caching**:
  - Configurable cache duration
  - Manual cache refresh
  - Optimized performance

## Installation

1. Upload the `custom-type-forge` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Custom Type Forge** in the WordPress admin menu
4. Click "Scan Now" to analyze your site

## Usage

### Running a Scan

1. Go to **Custom Type Forge → Dashboard**
2. Click the "scan now" button
3. Wait for the scan to complete (typically 5-30 seconds)
4. View results in the dashboard or detailed pages

### Viewing Results

- **Dashboard**: Overview with quick stats and platform detection
- **Post Types**: Detailed list of all custom post types
- **Field Groups**: All field groups across all platforms
- **Relationships**: Connections and dependencies
- **Reports**: Generate downloadable documentation

### Generating Reports

1. Navigate to **Custom Type Forge → Reports**
2. Select your desired format (HTML, JSON, or Markdown)
3. Click "generate report"
4. Download the generated file

## Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher (8.0+ recommended)

## Screenshots

(Screenshots will be added)

## Frequently Asked Questions

### Does this plugin modify my data?

No. Custom Type Forge is **read-only**. It analyzes your site configuration but never modifies post types, fields, or content.

### Which platforms are supported?

- Advanced Custom Fields (Free & Pro)
- Pods Framework
- Toolset Types
- Meta Box
- JetEngine
- Native WordPress custom post types

### Can I export the data?

Yes! You can export comprehensive reports in HTML, JSON, or Markdown format.

### How often should I run a scan?

Custom Type Forge caches results for 1 hour by default. Run a new scan when:
- You add/modify custom post types
- You install new field management plugins
- You need updated documentation

## Changelog

### 1.0.0 (2025-01-XX)
- Initial release
- Multi-platform CPT detection
- Field group analysis
- Relationship mapping
- HTML/JSON/Markdown export
- Smart caching system

## Credits

**Author**: Jim R Forge
**Website**: [jimrforge.com](https://jimrforge.com)
**Email**: jim@jimrforge.com

## License

GPL v2 or later

## Support

For support, please visit:
- Plugin support forum
- [GitHub Issues](https://github.com/Mij-Strebor/custom-type-forge)
- Email: jim@jimrforge.com

---

*Part of the JimRForge WordPress plugin suite*
