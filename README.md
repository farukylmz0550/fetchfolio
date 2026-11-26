# FetchFolio

FetchFolio is a terminal-inspired portfolio theme for WordPress. The former
standalone static site has been removed; WordPress is the supported edition.

## Install

Copy `wordpress-theme/fetchfolio` into `wp-content/themes/` and activate
**FetchFolio** under **Appearance → Themes**. On first activation, the theme
imports editable starter pages and posts from its bundled starter content.
Afterward, manage pages and posts in the WordPress dashboard.

Configure the terminal identity, accent color, ASCII art, and profile links in
**Appearance → Customize → FetchFolio terminal**. The theme includes a local
WordPress development environment in `wordpress-theme/preview/`; see its
`README`/Compose configuration for setup.

## Crawler preferences

The repository-root `robots.txt` allows search engines and user-initiated AI
page fetches, while disallowing selected model-training crawlers and admin
paths. `ai.txt` summarizes this preference. These are crawler requests, not
access controls: WordPress authentication protects administrative pages.

Replace the example sitemap host in `robots.txt` with the production domain.

## License

FetchFolio is distributed under GPL-3.0-or-later. See `LICENSE` and
`wordpress-theme/preview/licenses-wordpress.md` for attribution details.
