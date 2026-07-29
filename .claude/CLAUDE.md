# CLAUDE.md — Custom Type Forge (CTF)

## INHERITANCE — ADDS TO, NEVER REPLACES

**Parent chain** — every rule from every level above remains fully in effect here:
1. `C:\Users\Owner\.claude\CLAUDE.md` — Root: all Claude Code sessions
2. `E:\projects\.claude\CLAUDE.md` — All E:\projects\ work
3. `E:\projects\plugins\.claude\CLAUDE.md` — All plugin development rules

**This file adds:** CTF-specific rules only.

---

## Plugin Identity

- **Plugin name:** Custom Type Forge
- **Acronym / folder:** `ctf` → `E:\projects\plugins\ctf`
- **Version:** v1.0.0
- **GitHub:** (not yet published)
- **Text domain:** `custom-type-forge`
- **Admin page slug:** `custom-type-forge`
- **Required capability:** `manage_options`
- **Branding:** Always "Jim R Forge" — never "JimRWeb" or "JimRForge"
- **Author URI:** https://jimrforge.com

---

## What CTF Does

CTF is a WordPress admin tool that analyzes and documents Custom Post Types (CPTs) and Advanced Custom Fields (ACF). It:
- Detects CPTs registered by any method (native WP, ACF, Pods, Toolset, Meta Box, JetEngine)
- Detects ACF field groups and their associated fields
- Maps relationships between post types and field groups
- Generates documentation/reports about the content architecture

**CTF is read-only.** It analyzes and reports — it never creates, modifies, or deletes CPTs, field groups, or post data.

---

## CRITICAL: CTF is Read-Only

- No class, method, or function in CTF may register, modify, or delete CPTs, ACF field groups, or any WordPress content.
- The `class-cpt-detector.php`, `class-acf-detector.php`, and related analyzer classes may only read — never write.

---

## CSS / JS Prefix

| Layer | Prefix | Example |
|-------|--------|---------|
| CSS classes | `ctf-` | `ctf-btn`, `ctf-panel` |
| AJAX actions | `ctf_` | `ctf_run_analysis` |
| WP option keys | `ctf_` | `ctf_settings` |

---

## CTF-Specific Rules

- **No build process:** Pure PHP/JS/CSS. Hard-refresh (Ctrl+Shift+R) after JS/CSS edits.
- **ACF dependency:** CTF detects ACF if present but does not require it. All ACF-related code must be guarded with `class_exists('ACF')` or equivalent.
- **Multi-plugin support:** CTF must detect CPTs and fields from Pods, Toolset, Meta Box, and JetEngine in addition to ACF and native WP. When adding support for a new CPT/field plugin, check the detection logic in all `class-*-detector.php` files.

---

## JimRForge UI Standards — CTF Implementation

FSF v1.2.4 is the canonical UI prototype. Full reference: `E:\projects\JIMRFORGE-UI-STANDARDS.md`

### Brand

- Organization name: **Jim R Forge** — not "JimRWeb", not "JimRForge"

### Color System — Exact Values

```css
:root {
    --clr-primary:    #3d2f1f;    --clr-secondary:  #6d4c2f;    --clr-tertiary:   #86400e;
    --clr-accent:     #f4c542;    --clr-btn-hover:  #dda824;
    --clr-age-bg:     #faf6f0;    --clr-card-bg:    #ffffff;    --clr-light:      #faf9f6;    --clr-white: #fff;
    --clr-txt:        #6d4c2f;    --clr-txt-light:  #faf9f6;    --clr-txt-muted:  #64748b;
    --clr-link:       #ce6565;    --clr-link-hover: #b54545;
    --clr-border:     #c9b89a;
    --clr-shadow-sm:  0 1px 2px rgba(61, 47, 31, 0.08);
    --clr-shadow-md:  0 4px 6px rgba(61, 47, 31, 0.12);
    --clr-shadow-lg:  0 10px 20px rgba(61, 47, 31, 0.15);
    --clr-shadow-xl:  0 20px 30px rgba(61, 47, 31, 0.18);
    --clr-success: #059669; --clr-success-bg: #ecfdf5;
    --clr-error:   #dc2626; --clr-error-bg:   #fee2e2;
    --clr-warning: #f59e0b; --clr-warning-bg: #fef3c7;
    --clr-info:    #3b82f6; --clr-info-bg:    #dbeafe;
}
```

### Typography

Font: Inter (locally loaded); Base: 16px; Weights: 400/500/600/700

```css
--fs-xxs: 11px; --fs-xs: 13px; --fs-sm: 14px; --fs-md: 16px;
--fs-lg: 18px;  --fs-xl: 20px; --fs-xxl: 24px; --fs-xxxl: 32px;
```

### Spacing

```css
--sp-1: 4px; --sp-2: 8px; --sp-3: 12px; --sp-4: 16px;
--sp-5: 20px; --sp-6: 24px; --sp-8: 32px; --sp-9: 36px;
```

### Primary Button — `.ctf-btn`

Background: `--clr-accent`, Text: `--clr-primary`; 14px/600; sentence case; no border; radius 8px; 8px 16px padding; hover: `--clr-btn-hover`, lift transform. **No icons in modal buttons.**

### Secondary — `.ctf-btn.ctf-btn-secondary`

Background: `#64748b`, Text: white; Hover: `#475569`

### Danger — `.ctf-btn.ctf-btn-danger`

Background: `#dc2626`, Text: white; Hover: `#b91c1c`, lift transform

### Forge Header + Accessibility

Every admin page: `assets/images/forge-banner.png` + `assets/css/forge-header.css`. Focus: `2px solid var(--clr-accent)`. All icon-only buttons: `aria-label`. Full keyboard navigation.
