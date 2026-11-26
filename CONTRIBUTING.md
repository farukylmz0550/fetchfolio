# Contributing to FetchFolio

Thanks for helping improve FetchFolio. The maintained product is the WordPress
theme in `wordpress-theme/fetchfolio`; changes to the removed static-site
edition are out of scope.

## Development preview

The preview requires Docker Compose and OpenSSL. It runs WordPress at
`http://127.0.0.1:2048` and mounts the local theme read-only. From the repository
root, create local-only database and WordPress secrets, then start the preview:

```sh
cd wordpress-theme/preview
for key in DB_PASSWORD DB_ROOT_PASSWORD WP_AUTH_KEY WP_SECURE_AUTH_KEY \
  WP_LOGGED_IN_KEY WP_NONCE_KEY WP_AUTH_SALT WP_SECURE_AUTH_SALT \
  WP_LOGGED_IN_SALT WP_NONCE_SALT; do
  printf '%s=%s\n' "$key" "$(openssl rand -hex 32)"
done > .env
docker compose up -d
```

Open the local address and complete WordPress's setup screen. The `.env` file
is ignored by Git; never commit secrets, database dumps, or real personal
content. When finished, stop the preview with `docker compose down` from
`wordpress-theme/preview`.

## Making changes

- Theme templates and behavior belong in `wordpress-theme/fetchfolio`.
- The initial editable pages, two sample posts, and default terminal settings
  are defined in `assets/starter-content.json`. Keep starter copy generic and
  use placeholders rather than personal information.
- Keep the `fetchfolio/` directory as the root folder in installable ZIPs.
- Update the `Version` field in `wordpress-theme/fetchfolio/style.css` when
  preparing a release. Release tags use the matching `vX.Y.Z` format.
- Preserve the GPL-3.0-or-later license and required third-party icon notices.

## Review before submitting

There is no automated test suite at present. For a theme change, review the
affected page in the local WordPress preview, including mobile layout when the
change affects styling. For content or starter-data edits, confirm the JSON is
valid:

```sh
python3 -m json.tool wordpress-theme/fetchfolio/assets/starter-content.json >/dev/null
```

Run PHP syntax checks on changed PHP files with `php -l` and check the patch
with `git diff --check`. For changes to consent, Markdown, or crawler behavior,
review the relevant public routes (`.md`, `/ai.txt`, and `/robots.txt`) in the
preview. Crawler policy files are requests to bots, not access controls.

## Releases

The release workflow runs when a `v*` tag is pushed. It packages only
`wordpress-theme/fetchfolio` as
`fetchfolio-wordpress-theme-X.Y.Z.zip` and attaches the archive to the matching
GitHub Release. Before tagging, make sure the tag version matches the theme's
`style.css` version and the theme installs from the generated ZIP.
