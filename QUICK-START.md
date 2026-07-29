# Custom Type Forge — Quick Start Guide

**Version 1.0.0**

Custom Type Forge scans your WordPress site, finds every custom post type and
custom field group — no matter which plugin created them — and gives you a
clear picture of your content architecture in minutes.

This guide gets you from install to your first usable report. For the full
feature reference, see `USER-MANUAL.md`.

---

## 1. Install and Activate

1. Upload the `custom-type-forge` folder to `/wp-content/plugins/`, or install
   the plugin zip via **Plugins → Add New → Upload Plugin**.
2. Go to **Plugins** and click **Activate** on Custom Type Forge.
3. A new **Custom Type Forge** menu item (analytics icon) appears in the
   WordPress admin sidebar.

No setup screen or configuration is required — the plugin is ready to scan as
soon as it's active.

**Requirements:** WordPress 5.8+, PHP 7.4+, `manage_options` capability
(Administrator).

---

## 2. Run Your First Scan

1. Click **Custom Type Forge** in the admin sidebar. This opens the
   **Dashboard**.
2. Click the **scan now** button in the Scan Control panel.
3. Wait for the scan to finish — typically a few seconds, longer on sites with
   many post types or field groups. The page reloads automatically when done.

The scan reads your site's actual post type and field group registrations
directly from WordPress and from any supported plugin (Advanced Custom
Fields, Pods, Toolset Types, Meta Box, JetEngine) that's active. It does not
change anything — Custom Type Forge is strictly read-only.

Results are cached for 1 hour so you don't have to re-scan on every page
load. Use **clear cache** on the Dashboard to force a fresh scan at any time
(useful right after you add or change a post type or field group).

---

## 3. Review What Was Found

After a scan completes, the Dashboard shows:

- Quick stats: number of Custom Post Types, Field Groups, Platforms
  Detected, and Relationships
- Which plugins Custom Type Forge found active on your site (Detected
  Platforms)
- Quick Actions linking to the detailed **Post Types**, **Field Groups**, and
  **Relationships** pages

Open **Custom Type Forge → Post Types** to see every custom post type in a
table (name, slug, registration source, post count, public/REST status,
taxonomy count). Open **Custom Type Forge → Field Groups** the same way for
field groups.

---

## 4. Generate Your First Report

1. Go to **Custom Type Forge → Reports**.
2. Choose a format: **HTML** (styled, shareable), **JSON** (machine-readable),
   or **Markdown** (drops straight into a GitHub repo or wiki).
3. Click **generate report**.
4. Click **download report** on the success message to save the file.

That's the core workflow: scan → review → export. For every other page,
setting, and platform-specific detail, see `USER-MANUAL.md`.
