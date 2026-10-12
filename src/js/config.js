// fetchfolio - Config loader
// Reads fetchfolio.config.yaml and customizes the site:
// - fetchfolio.config.yaml is THE single config file, always at the site root.
// - The site root is derived from this script's own URL, so it works on any
//   deploy layout that keeps src/js/config.js relative to the root.
// - Accent color is applied by updating the --ff-accent CSS variable
//   (site.css consumes it); no runtime <style> injection.
// - The SEO <title> copy is left alone; only the terminal bar (and pages that
//   still carry the raw "user@hostname:~/..." template title) get rewritten.
// - Empty social URLs keep the template homepage hrefs (see applySocialLinks).

const CONFIG_FILE = 'fetchfolio.config.yaml';

let siteConfig = null;

async function loadConfig() {
  try {
    // Resolve the site root from the script URL (config.js is always
    // served from <root>/src/js/). Works at / and any subdirectory.
    let root = '/';
    if (document.currentScript && document.currentScript.src) {
      root = document.currentScript.src.replace(/\/src\/js\/config.js.*$/, '/');
    }
    const cfgPath = root + CONFIG_FILE;
    // cache: 'no-cache' revalidates every load, so config edits show up
    // after a plain reload (no cache-busting double-retry needed).
    const response = await fetch(cfgPath, { cache: 'no-cache' });
    if (!response.ok) throw new Error('Failed to load config');
    const yamlText = await response.text();
    // { json: true } turns duplicate YAML keys into errors instead of
    // silently keeping the last one — config conflicts fail loudly here.
    siteConfig = jsyaml.load(yamlText, { json: true });
    applyConfig();
  } catch (err) {
    console.warn('Failed to load fetchfolio.config.yaml, using defaults:', err);
    siteConfig = getDefaultConfig();
    applyConfig();
  }
}

function getDefaultConfig() {
  return {
    site: {
      user: 'user',
      host: 'hostname'
    },
    theme: {
      accent_color: '#38bdf8'
    },
    social: {}
  };
}

// "user@hostname:~/about" -> "custom@values:~/about"
// The path suffix is preserved; on the home page it already ends with "~/".
// Only the terminal bar and the raw template title are rewritten — the SEO
// <title> copy (e.g. "About Me — Your Name") must stay untouched, so the
// template prefix is the signal that a page still ships the default title.
function applyTitlePrefix() {
  const site = (siteConfig && siteConfig.site) || {};
  const prefix = (site.user || 'user') + '@' + (site.host || 'hostname');
  const withPrefix = (text) => {
    const idx = text.indexOf(':');
    return idx === -1 ? prefix + ':~/' : prefix + text.slice(idx);
  };

  const el = document.querySelector('.terminal-title');
  if (el) el.textContent = withPrefix(el.textContent);

  if (document.title.indexOf('user@hostname:') === 0) {
    document.title = withPrefix(document.title);
  }
}

// Home page h1: site.name from fetchfolio.config.yaml. When the key is
// missing the markup value stays, which is also what crawlers read.
function applySiteName() {
  const site = (siteConfig && siteConfig.site) || {};
  const el = document.querySelector('[data-site-name]');
  if (el && typeof site.name === 'string' && site.name.trim()) {
    el.textContent = site.name.trim();
  }
}

function applyConfig() {
  if (!siteConfig) return;

  const site = siteConfig.site || {};
  const theme = siteConfig.theme || {};

  // User / host in the home page info block
  if (site.user || site.host) {
    const userHostEls = document.querySelectorAll('.user-host');
    if (userHostEls.length >= 2) {
      userHostEls[0].textContent = site.user || 'user';
      userHostEls[1].textContent = site.host || 'hostname';
    }
  }
  applyTitlePrefix();
  applySiteName();

  // Accent color -> CSS variable
  if (theme.accent_color && theme.accent_color !== '#38bdf8') {
    // Roughly validate hex/rgb format, otherwise fall back to the default.
    if (/^#[0-9a-fA-F]{3,8}$/.test(theme.accent_color) || /^rgba?\(/.test(theme.accent_color)) {
      document.documentElement.style.setProperty('--ff-accent', theme.accent_color);
    }
  }

  // ASCII art — accepts a |- block string; empty keeps the template art
  const artEl = document.querySelector('.ascii-art');
  if (artEl && siteConfig.ascii_art && typeof siteConfig.ascii_art === 'string' && siteConfig.ascii_art.trim()) {
    artEl.textContent = siteConfig.ascii_art;
  }

  // Social links (fetchfolio.config.yaml social section)
  applySocialLinks();
}

function applySocialLinks() {
  const social = (siteConfig && siteConfig.social) || {};
  // key -> CSS box class mapping
  const map = {
    github: 'github',
    youtube: 'youtube',
    makerworld: 'makerworld',
    linkedin: 'linkedin',
    discord: 'purple'
  };

  Object.keys(map).forEach(key => {
    const box = document.querySelector('.social-box.' + map[key]);
    if (!box) return;
    const url = social[key];
    // The template HTML already points every box at the network's homepage.
    // Only override when fetchfolio.config.yaml defines a URL (e.g. your own profile).
    if (url && typeof url === 'string' && url.trim()) {
      box.href = url.trim();
      box.target = '_blank';
      box.rel = 'noopener noreferrer';
    }
  });
}

// Load the config on page load
document.addEventListener('DOMContentLoaded', loadConfig);
