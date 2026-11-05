#!/usr/bin/env python3
"""fetchfolio — prerender content/*.md into the matching HTML pages.

The content pages render their body at runtime with marked.js, so the raw HTML
a crawler (or a visitor without JS) receives is empty. This script converts
each markdown file to HTML once and embeds it between marker comments in the
page; the browser still re-renders the same markup with marked.js on load, so
the usual "edit the .md and reload" workflow keeps working.

Stdlib only — no markdown package, no node.

Usage:
    python3 scripts/prerender.py            # rewrite the pages
    python3 scripts/prerender.py --check    # exit 1 when a page is stale (CI)
"""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

# content/<name>.md -> the page that embeds it (licenses.html is plain HTML).
PAGES: dict[str, str] = {
    "about": "about.html",
    "achievements": "achievements.html",
    "contact": "contact.html",
    "cv": "cv.html",
    "education": "education.html",
}

HEADING = re.compile(r"^(#{1,6})[ \t]+(.+?)[ \t]*$")
HR = re.compile(r"^[ ]{0,3}(-{3,}|\*{3,}|_{3,})[ \t]*$")
FENCE = re.compile(r"^[ ]{0,3}(```+|~~~+)(.*)$")
LIST_ITEM = re.compile(r"^([ \t]*)([-*+])[ \t]+(.*)$")
URL = re.compile(r"https?://[^\s<>\"')\]]+|www\.[^\s<>\"')\]]+")
EMAIL = re.compile(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}")
PLACEHOLDER = re.compile(r"\x00(\d+)\x00")


def escape(text: str) -> str:
    return text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")


def _restore(text: str, store: list[str]) -> str:
    """Put stashed HTML fragments back; a fragment may hold other fragments."""
    while "\x00" in text:
        text = PLACEHOLDER.sub(lambda m: store[int(m.group(1))], text)
    return text


def _inline(text: str, store: list[str], label: bool = False) -> str:
    """Inline markdown -> HTML. `text` is already HTML-escaped.

    `label` marks the text of an already-collected link: inside a link there
    are no nested links, images or autolinks (that would nest <a> tags).
    """

    def stash(html: str) -> str:
        store.append(html)
        return "\x00%d\x00" % (len(store) - 1)

    # Code spans first: their content is opaque (no links, no emphasis).
    text = re.sub(r"`([^`]+)`", lambda m: stash("<code>%s</code>" % m.group(1)), text)

    if not label:
        text = re.sub(
            r"!\[([^\]]*)\]\(([^)\s]+)\)",
            lambda m: stash('<img src="%s" alt="%s">' % (m.group(2), m.group(1))),
            text,
        )
        text = re.sub(
            r"\[([^\]]+)\]\(([^)\s]+)\)",
            lambda m: stash('<a href="%s">%s</a>' % (m.group(2), _inline(m.group(1), store, True))),
            text,
        )

        def autolink(url: str) -> str:
            # Trailing sentence punctuation stays outside the link, like GFM.
            trail = ""
            while url and url[-1] in ".,;:!":
                trail = url[-1] + trail
                url = url[:-1]
            href = url if "://" in url else "https://" + url
            return stash('<a href="%s">%s</a>' % (href, url)) + trail

        # URLs before emails so "https://host/a@b.io" stays one link.
        text = URL.sub(lambda m: autolink(m.group(0)), text)
        text = EMAIL.sub(
            lambda m: stash('<a href="mailto:%s">%s</a>' % (m.group(0), m.group(0))), text
        )

    text = re.sub(r"\*\*(?=\S)(.+?)(?<=\S)\*\*", r"<strong>\1</strong>", text)
    text = re.sub(r"(?<!\*)\*(?=[^\s*])(.+?)(?<=\S)\*(?!\*)", r"<em>\1</em>", text)
    text = re.sub(r"(?<![\w])_(?=\S)(.+?)(?<=\S)_(?![\w])", r"<em>\1</em>", text)
    return text


def inline(text: str) -> str:
    store: list[str] = []
    return _restore(_inline(escape(text), store), store)


def _fence_close(line: str, ch: str) -> bool:
    return re.match(r"^[ ]{0,3}%s{3,}[ \t]*$" % re.escape(ch), line) is not None


def render_list(items: list[tuple[int, str]]) -> str:
    """items are (indent, text); deeper indents become nested <ul>s."""
    out: list[str] = []
    i = 0
    while i < len(items):
        indent, text = items[i]
        j = i + 1
        while j < len(items) and items[j][0] > indent:
            j += 1
        body = inline(text)
        if j > i + 1:
            body += "\n" + render_list(items[i + 1 : j])
        out.append("<li>%s</li>" % body)
        i = j
    # marked renders "<ul>\n" + items + "</ul>"; #content is white-space:pre-wrap,
    # so these newlines are part of the layout and have to be reproduced exactly.
    return "<ul>\n%s</ul>" % "".join(item + "\n" for item in out)


def is_block_start(line: str) -> bool:
    return bool(
        HEADING.match(line)
        or HR.match(line)
        or FENCE.match(line)
        or LIST_ITEM.match(line)
        or line.lstrip().startswith(">")
    )


def md_to_html(md: str) -> str:
    """Convert the markdown subset used by content/*.md to HTML.

    Output mirrors marked.js byte for byte (including the newline after every
    block): #content is white-space:pre-wrap, so the prerendered page and the
    page after the JS re-render have to lay out identically.
    """
    lines = md.replace("\r\n", "\n").replace("\r", "\n").split("\n")
    blocks: list[str] = []
    i, n = 0, len(lines)

    while i < n:
        line = lines[i]

        if not line.strip():
            i += 1
            continue

        m = FENCE.match(line)
        if m:
            marker, info = m.group(1), m.group(2).strip()
            body: list[str] = []
            i += 1
            while i < n and not _fence_close(lines[i], marker[0]):
                body.append(lines[i])
                i += 1
            i += 1  # skip the closing fence (or stop at EOF)
            cls = ' class="language-%s"' % escape(info.split()[0]) if info else ""
            body_text = escape("\n".join(body) + "\n")  # marked keeps one \n
            blocks.append("<pre><code%s>%s</code></pre>" % (cls, body_text))
            continue

        m = HEADING.match(line)
        if m:
            level = len(m.group(1))
            blocks.append("<h%d>%s</h%d>" % (level, inline(m.group(2).rstrip(" #")), level))
            i += 1
            continue

        if HR.match(line):
            blocks.append("<hr>")
            i += 1
            continue

        if line.lstrip().startswith(">"):
            quoted: list[str] = []
            while i < n and lines[i].lstrip().startswith(">"):
                quoted.append(re.sub(r"^[ ]{0,3}>[ ]?", "", lines[i]))
                i += 1
            blocks.append("<blockquote>\n%s</blockquote>" % md_to_html("\n".join(quoted)))
            continue

        m = LIST_ITEM.match(line)
        if m:
            base = len(m.group(1).replace("\t", "  "))
            items: list[tuple[int, str]] = [(base, m.group(3))]
            i += 1
            while i < n:
                cur = lines[i]
                if not cur.strip():
                    break  # a blank line ends the list
                m2 = LIST_ITEM.match(cur)
                if m2:
                    indent = len(m2.group(1).replace("\t", "  "))
                    if indent < base:
                        break
                    items.append((indent, m2.group(3)))
                    i += 1
                elif not is_block_start(cur):
                    # Lazy continuation: another line of the previous item.
                    indent, text = items[-1]
                    items[-1] = (indent, text + "\n" + cur.strip())
                    i += 1
                else:
                    break
            blocks.append(render_list(items))
            continue

        # Paragraph: consecutive plain lines until the next block.
        para = [line]
        i += 1
        while i < n and lines[i].strip() and not is_block_start(lines[i]):
            para.append(lines[i].strip())
            i += 1
        blocks.append("<p>%s</p>" % inline("\n".join(para)))

    # Every marked block renderer ends with "\n" (space tokens render as "").
    return "".join(block + "\n" for block in blocks)


def markers(name: str) -> tuple[str, str]:
    return (
        "<!-- prerender:content/%s.md:start -->" % name,
        "<!-- prerender:content/%s.md:end -->" % name,
    )


def inject(page: str, name: str, rendered: str, page_path: str) -> str:
    start, end = markers(name)
    head = page.find(start)
    tail = page.find(end)
    if head == -1 or tail == -1 or tail < head:
        raise SystemExit(
            "%s: missing %s ... %s markers around the #content element"
            % (page_path, start, end)
        )
    # No whitespace added around the fragment: #content is white-space:pre-wrap.
    return page[: head + len(start)] + rendered + page[tail:]


def build(name: str) -> tuple[Path, str, str, str]:
    """Returns (page_path, old_text, new_text, rendered) for one page."""
    md_path = ROOT / "content" / ("%s.md" % name)
    page_path = ROOT / PAGES[name]
    rendered = md_to_html(md_path.read_text(encoding="utf-8"))
    old = page_path.read_text(encoding="utf-8")
    return page_path, old, inject(old, name, rendered, str(page_path)), rendered


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--check",
        action="store_true",
        help="do not write; fail if a page is out of sync with its markdown",
    )
    args = parser.parse_args()

    stale: list[str] = []
    for name in PAGES:
        page_path, old, new, rendered = build(name)
        if old == new:
            print("ok       %s" % page_path.relative_to(ROOT))
            continue
        if args.check:
            stale.append(str(page_path.relative_to(ROOT)))
            print("STALE    %s (rerun scripts/prerender.py)" % page_path.relative_to(ROOT))
            continue
        page_path.write_text(new, encoding="utf-8")
        print("rendered %s (%d bytes)" % (page_path.relative_to(ROOT), len(rendered)))

    if stale:
        print("\n%d page(s) out of sync with content/*.md" % len(stale))
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main())
