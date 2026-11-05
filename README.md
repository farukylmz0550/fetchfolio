# fetchfolio

A personal portfolio website that looks like a terminal. **neofetch**-style home
page, `$ cat about.md` content pages, and a blinking cursor — all vanilla
HTML/CSS/JS, no build step, no frameworks.

## Screenshots

![Home — terminal / neofetch view](docs/screenshots/home.png)

| Content pages | Licenses (hard-coded, with signature) |
|---|---|
| ![About page](docs/screenshots/about.png) | ![Licenses page](docs/screenshots/licenses.png) |

## Features

- Terminal simulation interface (neofetch style) with an ASCII art logo
- ASCII art, user@host, accent color and social links configurable in a
  single file: `fetchfolio.config.yaml`
- Markdown content pages (`about.md`, `education.md`, `achievements.md`,
  `cv.md`, `contact.md`) loaded at runtime
- File-based blog with a Markdown post catalogue and terminal-style post pages
- Optional password-protected blog admin panel for creating, editing and deleting posts
- Terminal-themed 404 page (`cat missing-file.md`)
- `Made by` signature with a GitHub profile link on the licenses page
- Docker deployment (nginx:1.27-alpine frontend + a small Python backend)
- GitHub Actions workflow that publishes images to ghcr.io on every push
  and on version tags

## Quickstart

```bash
git clone https://github.com/farukylmz0550/fetchfolio.git
cd fetchfolio
python3 -m http.server 2222
```

Open in your browser: `http://localhost:2222`

This starts the static site only. The blog remains fully readable, but the
admin API is not available through `http.server`. To run the complete stack
with blog management, use Docker Compose as described below.

## Configuration

Everything lives in ONE file: **`fetchfolio.config.yaml`** (always at the
site root; **`config.js` locates it from any page under any subdirectory**).

```yaml
site:
  version: "2.3.0"
  user: "user"       # user@host in the header; also prefixes page titles
  host: "hostname"

theme:
  accent_color: "#38bdf8"   # any CSS color; drives --ff-accent

# Social links — every box is always visible. Leave a network empty and
# the box stays at the site's homepage; put your own URL to point it there.
social:
  github: ""
  youtube: ""
  makerworld: ""
  linkedin: ""
  discord: ""

# Optional ASCII art override — replaces the home page art when set.
# Keep the |- block form and the leading spaces of the art lines:
# ascii_art: |
#   =-%%%%%%%%%==
#   -%%%-%%%%%==
```

Notes:
- Duplicate YAML keys are NOT silently accepted — `config.js` parses with
  `{ json: true }` and falls back to defaults with a console warning.
- Config edits show up after a plain reload — config and markdown fetches
  use `{ cache: 'no-cache' }`.

## Content pages

Markdown files under `content/` are rendered into the terminal by
`marked.js` (`src/pages/*.html`):

| Page | File |
|---|---|
| About | `content/about.md` |
| Education | `content/education.md` |
| Achievements | `content/achievements.md` |
| CV | `content/cv.md` |
| Contact | `content/contact.md` |

## Blog

Blog posts are Markdown files under `content/blog/`. Add a post by creating a
file and adding its metadata to `content/blog/index.json`:

```json
{
  "slug": "my-post",
  "title": "My post",
  "date": "2026-11-05",
  "excerpt": "A short description.",
  "file": "my-post.md"
}
```

The blog index is available from the home page at `src/pages/blog.html`.

### Blog admin

The optional admin panel is available at `/admin.html` when the Docker Compose
stack is running. On first use, enter a username and a password of at least 12
characters and choose **First-time setup**. After that, use **Login** to manage
posts from the browser.

The admin service stores its account and session data in the `fetchfolio-data`
Docker volume. Blog Markdown files and `content/blog/index.json` are written
to the mounted project directory, so changes remain in the working tree and
can be committed normally. The backend uses only Python's standard library.

For production use, put the site behind HTTPS and restrict access to
`/admin.html` as appropriate for your deployment. The admin API is intended
for a trusted self-hosted deployment, not an open public endpoint.

The **licenses page is hard-coded** in `src/pages/licenses.html` — no
markdown, one less network request.

## Project structure

```
├── index.html              # neofetch-style home
├── 404.html                # terminal-themed 404 (nginx error_page target)
├── fetchfolio.config.yaml  # THE single configuration file
├── assets/css/site.css     # all styles (bump ?v=N when you change it)
├── content/*.md            # markdown source of the content pages
├── content/blog/            # blog catalogue and Markdown posts
└── src/
    ├── js/config.js        # loads + applies the yaml config
    ├── js/load-md.js       # fetches + renders markdown
    ├── js/marked.min.js    # markdown parser (v15.0.12)
    ├── js/js-yaml.min.js   # yaml parser (v4.1.0)
    ├── pages/*.html        # inner pages
    └── icons/*.svg         # social box brand icons (white)
```

## Docker

Self-host the site on any machine:

```bash
docker build -t fetchfolio:latest .
docker run --rm -p 3000:80 fetchfolio:latest
# → http://localhost:3000
```

Or with compose (bind mount keeps `docker-compose.yml` watching the live
files, no rebuild needed):

```bash
docker compose up --build
# → http://localhost:3000
```

## Updating config/assets without touching index.html

When you change `assets/css/site.css` or `src/js/{config,load-md}.js`,
bump the `?v=N` query parameter in the HTML files — browsers keep using
the old cached copy otherwise (browser + nginx cache).

## Releasing

- `main` push and `v*` tags trigger **.github/workflows/docker.yml**,
  which builds and pushes multi-architecture (`linux/amd64`, `linux/arm64`)
  images to `ghcr.io/<owner>/fetchfolio:{latest,<version>}`
- [Semver](https://semver.org/) is used: breaking changes bump major,
  features minor, fixes patch

### Changelog

- **2.3.0** — latest release

- **2.1.0** — add a file-based terminal blog with Markdown posts and
  multi-architecture Docker publishing for amd64 and arm64

- **2.0.0** — breaking: config renamed to `fetchfolio.config.yaml`,
  GitHub Pages support dropped, English-only (TR removed). Features:
  social boxes with brand icons and homepage fallbacks (new LinkedIn),
  `ascii_art` config override, duplicate-key config validation,
  cache-free config/markdown reloads, hard-coded licenses page with a
  "Made by" signature, static ambient background, terminal-themed 404
- **1.0.0** — first official release: unified CSS, social link
  configuration, accessibility improvements, Docker support

## License

This project is licensed under the GNU General Public License v3.0.
See the [LICENSE](LICENSE) file for details. Third-party libraries and
icons are listed on the licenses page (`src/pages/licenses.html`).

## Made by

[farukylmz0550](https://github.com/farukylmz0550)
