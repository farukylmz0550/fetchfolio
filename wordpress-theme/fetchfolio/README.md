# FetchFolio WordPress theme

FetchFolio is a classic PHP theme that brings the terminal-style portfolio to
WordPress. WordPress is the supported edition.

## Install

Copy the `fetchfolio` directory into `wp-content/themes/`, then activate
**FetchFolio** under **Appearance → Themes**. For a quick install, zip this
directory and upload it from the same screen.

The short `/admin` URL redirects to the WordPress login or dashboard.

## Set up the site

On first activation, the theme imports editable starter pages and posts from
`assets/starter-content.json`, then sets up the homepage, blog page, and section
menu. After that, edit content normally from **Pages** and **Posts** in the
WordPress dashboard. The import runs only once, so it will not overwrite later
edits when the theme is switched again.

Set terminal user, host, accent color, ASCII art, and profile links in
**Appearance → Customize → FetchFolio terminal**. Social links stay visible
with their network homepage when a profile URL is left blank.

Posts use WordPress's regular editor and automatically appear in the blog list.
The `wordpress-theme/preview` directory includes a Docker Compose setup for a
local development preview.

Published pages and posts also have a Markdown companion at the same URL with
`.md` appended (for example, `/about.md`). HTML pages advertise this format
with an alternate link. Markdown responses are generated from the current
WordPress content and are marked `noindex` to avoid duplicate search results.

WordPress's CMS runtime is PHP, so the theme uses the native WordPress PHP API;
it does not introduce a parallel backend service.

## Statistics consent

The privacy panel provides three choices: **Accept all**, **Reject optional**,
and **Accept necessary only**. Statistics consent is sent as the `statistics`
category through WP Consent API. For WP Statistics to honor the choice, install
and activate WP Consent API, then select that integration in **Statistics →
Settings → Privacy & Data Protection → User Preferences**. WP Consent API and
WP Statistics are optional plugins and are not included in the theme ZIP.

## Release package

GitHub Releases provide a WordPress-installable archive named
`fetchfolio-wordpress-theme-<version>.zip`. The archive contains the theme
folder at its root and excludes the local preview stack and its test post.

The theme is GPL-3.0-or-later. `preview/licenses-wordpress.md` documents the
WordPress and icon licenses, including required LinkedIn icon attribution.
