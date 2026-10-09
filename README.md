# fetchfolio

A personal portfolio website. Terminal-themed, neofetch-style single-language (English).

<img width="1920" height="962" alt="Screenshot From 2026-09-09 12-32-44" src="https://github.com/user-attachments/assets/b7811ce4-c817-4bc5-add6-80221a14b9af" />

## Features

- Terminal simulation interface (neofetch style)
- ASCII art logo (editable via `fetchfolio.config.yaml`)
- Responsive design
- Easy customization with `fetchfolio.config.yaml` (title, user@host, accent color, social links)
- Docker + GitHub Pages deploy support

## Setup

```bash
git clone https://github.com/farukylmz0550/fetchfolio.git
cd fetchfolio
python3 -m http.server 2222
```

Open in your browser: `http://localhost:2222`

## Customization

Edit the `fetchfolio.config.yaml` file to customize the site:

```yaml
site:
  version: "1.0.0"
  user: "fetch"
  host: "folio"

theme:
  accent_color: "#38bdf8"

# Social links — an empty network falls back to the site's homepage,
# entering a URL points the box at your own profile page instead
social:
  github: "https://github.com/farukylmz0550"
  youtube: ""
  makerworld: ""
  linkedin: ""
  discord: ""
```

Content pages are read from the Markdown files under `content/`
(`about.md`, `education.md`, `achievements.md`, `cv.md`, `contact.md`,
`licenses.md`).

## Running with Docker

```bash
docker compose up --build
# → http://localhost:3000
```

Prebuilt image (from ghcr.io):

```bash
docker run --rm -p 3000:80 ghcr.io/farukylmz0550/fetchfolio:latest
```

Every push to the `main` branch builds the image automatically and publishes
it to `ghcr.io`. Git tags in the `v*` format (e.g. `v1.0.0`) produce a
version-tagged image.

## Versioning

[Semver](https://semver.org/) is used: breaking changes bump major, features
minor, fixes patch.

- `1.0.0` — first official release: unified CSS, social link configuration,
  accessibility improvements, Docker + GH Pages support

### Developer note

When you change `assets/css/site.css`, also bump the `site.css?v=3`
version parameter in the HTML files — otherwise browsers keep using the
old CSS from cache (GH Pages + nginx cache).

## License

This project is licensed under the GNU General Public License v3.0.
See the [LICENSE](LICENSE) file for details.

## Technologies Used

- HTML5
- CSS3
- JavaScript
- marked.js (Markdown parser)
- js-yaml (YAML parser)
