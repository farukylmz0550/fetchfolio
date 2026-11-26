# FetchFolio

FetchFolio is a terminal-inspired portfolio theme for WordPress. The project
now ships the WordPress theme only; the previous static-site edition has been
removed.

## Install

Download `fetchfolio-wordpress-theme-3.1.0.zip` from the GitHub Releases page.
In WordPress, open **Appearance → Themes → Add New → Upload Theme**, select the
ZIP, and install and activate **FetchFolio**. For a manual install, copy
`wordpress-theme/fetchfolio` to `wp-content/themes/fetchfolio`.

On first activation, the theme adds editable placeholder pages and two example
posts. Edit pages and posts in the WordPress dashboard. It does not overwrite
those edits on later theme activations.

## Configure

- **Appearance → Customize → FetchFolio terminal:** set the terminal user and
  host, accent color, ASCII art, and profile links. Defaults are `fetch@folio`
  and the included FetchFolio ASCII art.
- **Appearance → Homepage Settings:** edit the short descriptions shown beside
  homepage sections and change their order. The same controls are also in the
  Customizer's **Homepage sections** panel.
- **Pages** and **Posts:** edit the actual homepage-linked content and publish
  blog entries. Posts use `/blogs/<post-slug>` URLs.

Dark mode is the default; visitors can switch to light mode. The privacy panel
appears until a visitor selects an option, then remembers that choice in their
browser. Statistics consent can integrate with the optional WP Consent API and
WP Statistics plugins.

## Markdown and crawlers

Published pages and posts have a Markdown companion at the same URL with
`.md` appended, such as `/about.md` or `/blogs/example-post.md`. The HTML page
links to its Markdown version, which is marked `noindex` to avoid duplicate
search listings.

The theme serves `/robots.txt` and `/ai.txt` policies. Search crawlers and
visitor-requested AI fetchers are allowed to read public content; automated
training and bulk-collection crawlers are asked not to. These files express
crawler preferences and cannot technically stop a bot that ignores them.
WordPress separately protects its dashboard with login and capability checks.

## Development

The local WordPress preview uses Docker Compose and is documented in
[CONTRIBUTING.md](CONTRIBUTING.md). Theme source is in `wordpress-theme/fetchfolio`;
starter pages, posts, and default terminal settings are in
`wordpress-theme/fetchfolio/assets/starter-content.json`.

Releases are tagged `vX.Y.Z`. GitHub Actions packages the theme folder as
`fetchfolio-wordpress-theme-X.Y.Z.zip` and attaches it to the GitHub Release.

## License

FetchFolio is licensed under GPL-3.0-or-later. Third-party icon and platform
notices are included in the **Licenses & Credits** page and the repository's
license documentation.
