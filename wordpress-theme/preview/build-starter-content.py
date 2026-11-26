#!/usr/bin/env python3
"""Build editable WordPress starter pages and posts from the local static site."""

from __future__ import annotations

import importlib.util
import json
import re
import sys
from datetime import date
from html import unescape
from pathlib import Path

sys.dont_write_bytecode = True
ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / "wordpress-theme/fetchfolio/assets/starter-content.json"

spec = importlib.util.spec_from_file_location("fetchfolio_prerender", ROOT / "scripts/prerender.py")
if spec is None or spec.loader is None:
    raise RuntimeError("Could not load the static Markdown renderer")
renderer = importlib.util.module_from_spec(spec)
spec.loader.exec_module(renderer)

page_map = (
    ("about", "About", ROOT / "content/about.md"),
    ("education", "Education", ROOT / "content/education.md"),
    ("achievements", "Achievements", ROOT / "content/achievements.md"),
    ("cv", "CV", ROOT / "content/cv.md"),
    ("contact", "Contact", ROOT / "content/contact.md"),
)
home_descriptions = {
    "about": "Developer, Linux & Systems Enthusiast",
    "education": "School History & Studies",
    "achievements": "Projects, Modding & 3D Designs",
    "cv": "Resume & Skills",
    "contact": "Email, GitHub & Social Media",
}
pages = []
for slug, default_title, path in page_map:
    markdown = path.read_text(encoding="utf-8")
    heading = re.search(r"^#\s+(.+)$", markdown, re.MULTILINE)
    title = heading.group(1).strip() if heading else default_title
    content = renderer.md_to_html(markdown)
    paragraph = re.search(r"<p>(.*?)</p>", content, re.DOTALL)
    description = home_descriptions.get(
        slug, re.sub(r"<[^>]+>", "", paragraph.group(1)) if paragraph else title
    )
    pages.append({"slug": slug, "title": title, "description": unescape(description), "content": content})

license_md = Path(__file__).with_name("licenses-wordpress.md").read_text(encoding="utf-8")
pages.append({
    "slug": "licenses",
    "title": "Licenses & Credits",
    "description": "Software and icon licenses, with required attribution.",
    "content": renderer.md_to_html(license_md),
})

posts = []
catalogue = json.loads((ROOT / "content/blog/index.json").read_text(encoding="utf-8"))
for item in catalogue:
    markdown = (ROOT / "content/blog" / item["file"]).read_text(encoding="utf-8")
    content = renderer.md_to_html(markdown)
    content = re.sub(r"^<h1>.*?</h1>\n?", "", content, count=1)
    posts.append({
        "slug": item["slug"], "title": item["title"], "date": item["date"],
        "excerpt": item["excerpt"], "content": content,
    })

if "--include-preview-post" in sys.argv[1:]:
    test_markdown = Path(__file__).with_name("theme-preview-test.md").read_text(encoding="utf-8")
    test_heading = re.search(r"^#\s+(.+)$", test_markdown, re.MULTILINE)
    test_content = renderer.md_to_html(test_markdown)
    test_content = re.sub(r"^<h1>.*?</h1>\n?", "", test_content, count=1)
    posts.append({
        "slug": "wordpress-theme-preview-test",
        "title": test_heading.group(1) if test_heading else "Theme Preview Test",
        "date": date.today().isoformat(),
        "excerpt": "A development-only post for checking the WordPress theme's editing and blog templates.",
        "content": test_content,
    })

config = (ROOT / "fetchfolio.config.yaml").read_text(encoding="utf-8")

def scalar(pattern: str, default: str) -> str:
    match = re.search(pattern, config, re.MULTILINE)
    return match.group(1).strip() if match else default

settings = {
    "user": scalar(r'^  user:\s*"?([^"\n]*)"?$', "user"),
    "host": scalar(r'^  host:\s*"?([^"\n]*)"?$', "hostname"),
    "accent": scalar(r'^  accent_color:\s*"?([^"\n]*)"?$', "#38bdf8"),
    "ascii": "",
}
for key in ("github", "youtube", "makerworld", "linkedin", "discord"):
    settings[key] = scalar(r'^  ' + key + r':\s*"?([^"\n]*)"?$', "")

OUTPUT.write_text(json.dumps({"pages": pages, "posts": posts, "settings": settings}, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print(f"Wrote {len(pages)} pages and {len(posts)} posts to {OUTPUT.relative_to(ROOT)}")
