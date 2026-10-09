// fetchfolio - Config loader
// Reads custom.yaml and customizes the site:
// - Uses relative paths so it also works in a GitHub Pages subdirectory.
// - Accent color is applied by updating the --ff-accent CSS variable
//   (site.css consumes it); no runtime <style> injection.
// - Empty social URLs hide their boxes on the home page.

let siteConfig = null;

async function loadConfig() {
  try {
    // 404.html is served at arbitrary URLs; FF_BASE there points at the site root.
    const cfgPath = (window.FF_BASE ? window.FF_BASE : './') + 'custom.yaml';
    const response = await fetch(cfgPath);
    if (!response.ok) throw new Error('Failed to load config');
    const yamlText = await response.text();
    siteConfig = jsyaml.load(yamlText);
    applyConfig();
  } catch (err) {
    console.warn('Failed to load custom.yaml, using defaults:', err);
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

// "user@hostname:~/about" -> "faruk@ylmz:~/about"
// The path suffix is preserved; on the home page it already ends with "~/".
function applyTitlePrefix() {
  const site = (siteConfig && siteConfig.site) || {};
  const prefix = (site.user || 'user') + '@' + (site.host || 'hostname');
  [document.title, document.querySelector('.terminal-title')].forEach(el => {
    if (!el) return;
    const idx = el.textContent.indexOf(':');
    if (idx === -1) {
      el.textContent = prefix + ':~/';
    } else {
      el.textContent = prefix + el.textContent.slice(idx);
    }
  });
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

  // Accent color -> CSS variable
  if (theme.accent_color && theme.accent_color !== '#38bdf8') {
    // Roughly validate hex/rgb format, otherwise fall back to the default.
    if (/^#[0-9a-fA-F]{3,8}$/.test(theme.accent_color) || /^rgba?\(/.test(theme.accent_color)) {
      document.documentElement.style.setProperty('--ff-accent', theme.accent_color);
    }
  }

  // Social links (custom.yaml social section)
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
    // Only override when custom.yaml defines a URL (e.g. your own profile).
    if (url && typeof url === 'string' && url.trim()) {
      box.href = url.trim();
      box.target = '_blank';
      box.rel = 'noopener noreferrer';
    }
  });
}

// Load the config on page load
document.addEventListener('DOMContentLoaded', loadConfig);
