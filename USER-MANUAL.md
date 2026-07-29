# Custom Type Forge — User Manual

**Version 1.0.0**

Comprehensive analyzer and documentation generator for WordPress Custom Post
Types (CPTs) and custom field groups.

---

## Table of Contents

1. [What Custom Type Forge Does](#what-custom-type-forge-does)
2. [Installation & Requirements](#installation--requirements)
3. [Supported Platforms](#supported-platforms)
4. [The Admin Menu](#the-admin-menu)
5. [Dashboard](#dashboard)
6. [Post Types](#post-types)
7. [Field Groups](#field-groups)
8. [Relationships](#relationships)
9. [Reports](#reports)
10. [Caching Behavior](#caching-behavior)
11. [Read-Only Guarantee](#read-only-guarantee)
12. [Security](#security)
13. [Troubleshooting](#troubleshooting)

---

## What Custom Type Forge Does

Custom Type Forge scans a WordPress installation and builds a complete
picture of its content architecture: every custom post type, every custom
field group, the taxonomies attached to each post type, and the
relationships between them — regardless of which plugin (or none) was used
to register them.

It's built for:

- Developers taking over an existing site and needing to understand its
  structure quickly
- Documenting a site's architecture for a team or client
- Site audits and migration planning
- Onboarding new team members to an unfamiliar codebase

Custom Type Forge never creates, edits, or deletes post types, fields, or
content. It only reads and reports.

---

## Installation & Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **Capability required:** `manage_options` (Administrator) for every screen
  and action

Install by uploading the `custom-type-forge` plugin folder to
`/wp-content/plugins/` and activating it from the **Plugins** screen. There is
no setup wizard and no settings screen — the plugin is ready to use
immediately after activation.

---

## Supported Platforms

Custom Type Forge detects post types and field groups from:

| Platform | Post type detection | Field group detection |
|---|---|---|
| Native WordPress | Yes | — (see note below) |
| Advanced Custom Fields (Free & Pro) | Yes | Yes |
| Pods Framework | Yes | Yes |
| Toolset Types | Yes | Yes |
| Meta Box | Yes | Yes |
| JetEngine | Yes | Yes |
| Carbon Fields | Detected as installed (shown on Dashboard) | Not parsed into field groups |

**Note on native custom fields:** WordPress core doesn't organize custom
fields into named "groups" the way ACF and similar plugins do — they're
individual meta keys attached directly to posts. Custom Type Forge's field
group scanner does not enumerate raw post meta, so native/unstructured custom
fields will not appear on the Field Groups page. Native custom **post types**
are fully detected and appear on the Post Types page.

**Note on Carbon Fields:** if Carbon Fields is active, it's listed on the
Dashboard's Detected Platforms panel, but its field groups are not currently
parsed by the Field Groups scanner.

By default, built-in WordPress post types (posts, pages, media, etc.) are
excluded from scan results so you only see custom post types. This is fixed
plugin behavior — there is no admin setting to change it in this version.

---

## The Admin Menu

After activation, **Custom Type Forge** appears in the WordPress admin
sidebar (analytics icon) with five pages:

- **Dashboard** — overview, scan controls, quick stats
- **Post Types** — table of all detected custom post types
- **Field Groups** — table of all detected field groups
- **Relationships** — connections between post types, taxonomies, and fields
- **Reports** — generate and download HTML, JSON, or Markdown reports

Every page requires the `manage_options` capability. Users without it will
see a permissions-denied message.

---

## Dashboard

**Custom Type Forge → Dashboard** (also the top-level menu link)

### Quick Stats

Four stat cards summarize the most recent scan: Custom Post Types, Field
Groups, Platforms Detected, and Relationships. These are blank/collapsed
until you run at least one scan.

### Scan Control

- **scan now** — runs a full site scan (post types, field groups,
  relationships) and caches the results. A progress indicator shows while the
  scan runs; the page reloads automatically when it finishes.
- **clear cache** — deletes the cached scan data (with a confirmation
  prompt). The next page load or scan will show no data until you scan
  again.

If a previous scan is cached, the panel shows the last scan date/time and
when the cache expires.

### Detected Platforms

After a scan, this panel lists every supported CPT/field plugin found active
on the site (with version number, and a **PRO** badge for ACF Pro), plus
WordPress core itself.

### Quick Actions

Four cards link directly to the Post Types, Field Groups, Relationships, and
Reports pages.

---

## Post Types

**Custom Type Forge → Post Types**

A table of every detected custom post type, with columns:

| Column | Meaning |
|---|---|
| Name | The post type's display label |
| Slug | The post type's registered key (e.g. `product`) |
| Source | Where it was registered — `acf`, `pods`, `toolset`, `jetengine`, `theme`, `code`, or `unknown` |
| Posts | Total post count across all statuses |
| Public | Whether the post type is publicly queryable (✓ / —) |
| REST | Whether it's exposed to the REST API (✓ / —) |
| Taxonomies | Number of taxonomies attached to it |

If no scan has been run yet, the page prompts you to run one from the
Dashboard.

**On the "Source" column:** Meta Box is not currently one of the sources this
column can identify — post types registered with Meta Box's post type tool
will show as `code` or `unknown` rather than `metabox`. Meta Box **fields**
are correctly attributed on the Field Groups page.

---

## Field Groups

**Custom Type Forge → Field Groups**

A table of every detected field group across all supported platforms, with
columns:

| Column | Meaning |
|---|---|
| Title | The field group's name |
| Platform | Which plugin owns it — `acf`, `pods`, `toolset`, `metabox`, or `jetengine` |
| Fields | Number of fields in the group |
| Status | Active or Inactive |

Click through from here for the plugin-specific details each platform
exposes (field types, required flags, choices, relationship targets, and —
for ACF — repeater/group/flexible-content sub-fields) via the generated
reports (see [Reports](#reports)); the on-screen table itself lists the
summary columns above.

---

## Relationships

**Custom Type Forge → Relationships**

Shows how post types, fields, and taxonomies connect to each other, based on
the most recent scan.

### Summary Stats

Three counters: CPT Relationships, Taxonomy Relationships, Field Mappings.

### Post Type Relationships

Lists relational fields (post object, relationship, or similar field types)
that link one post type to another — source post type, target post type, the
field that creates the link, its field type, and the owning platform.

### Taxonomy Relationships

Lists every post type paired with each taxonomy attached to it, and whether
that taxonomy is hierarchical.

If no scan has been run, this page prompts you to run one first.

---

## Reports

**Custom Type Forge → Reports**

Generates a downloadable snapshot of the most recent scan in your choice of
format:

- **HTML Report** — a styled, standalone HTML document with an executive
  summary, platform badges, per-post-type detail sections, and per-field-group
  tables. Opens in any browser.
- **JSON Export** — the complete scan data as machine-readable JSON, useful
  for feeding into other tooling.
- **Markdown Export** — the same content as the HTML report, formatted as
  plain Markdown for GitHub, wikis, or documentation repos.

To generate a report:

1. Select a format (HTML is selected by default).
2. Click **generate report**.
3. Click **download report** on the success message.

Reports are written to `wp-content/uploads/ctf-reports/` and named
`ctf-report-{site-name}-{timestamp}.{ext}`. If no scan has been cached yet,
generating a report triggers a fresh scan automatically.

---

## Caching Behavior

Scan results are cached as a WordPress transient (`ctf_scan_data`) for 1 hour
by default. This means:

- Repeat visits to any Custom Type Forge page within that hour show the same
  cached data instantly, without re-scanning.
- Generating a report reuses the cached scan if one exists, rather than
  re-scanning.
- **clear cache** on the Dashboard deletes the cached data immediately.

Deactivating the plugin also clears the cache.

There is currently no admin screen to change the 1-hour cache duration — it's
set internally when the plugin activates and is not user-configurable in
this version.

**Run a fresh scan (via clear cache, then scan now) whenever you:**
- Add, edit, or remove a custom post type or field group
- Install or activate a new CPT/field management plugin
- Need up-to-date documentation for a report

---

## Read-Only Guarantee

Custom Type Forge **never** modifies your site. It does not register, edit,
or delete post types, taxonomies, field groups, or content of any kind. Every
operation — scanning, viewing results, generating reports — only reads
existing WordPress and plugin data. The only things Custom Type Forge writes
are its own cached scan transient and the report files it generates into
`wp-content/uploads/ctf-reports/`.

---

## Security

- All admin pages require the `manage_options` capability.
- All AJAX actions (scan, generate report, clear cache) verify a WordPress
  nonce before executing.
- User-supplied input (such as the selected report format) is sanitized
  before use.

---

## Troubleshooting

**"No scan data available" on every page.** You haven't run a scan yet, or
the cache has expired/been cleared. Go to the Dashboard and click **scan
now**.

**A post type or field group I just created isn't showing up.** The scan is
cached for up to an hour. Click **clear cache** on the Dashboard, then **scan
now** to force a fresh read.

**A plugin I use isn't listed as detected.** Custom Type Forge detects
Advanced Custom Fields, Pods, Toolset Types, Meta Box, JetEngine, and Carbon
Fields (platform detection only for Carbon Fields — see
[Supported Platforms](#supported-platforms)), plus native WordPress. Field
management plugins outside this list are not currently supported.

**Report download link doesn't work.** Reports are saved under
`wp-content/uploads/ctf-reports/`. Confirm that directory is writable by
WordPress and that your uploads directory is publicly accessible per your
site's normal media configuration.
