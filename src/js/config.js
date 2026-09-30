// fetchfolio - Config Loader
// custom.yaml dosyasini okuyup siteyi gunceller.
// - Relative path kullanir: GitHub Pages alt dizininde (…/fetchfolio/) de calisir.
// - Vurgu rengi artık runtime <style> enjeksiyonu yerine --ff-accent CSS
//   degiskenini gunceller (site.css bunu tuketir).

let siteConfig = null;

async function loadConfig() {
  try {
    // 404.html GH Pages alt dizininde servis edilir; FF_BASE orada site kökünü gösterir.
    const cfgPath = (window.FF_BASE ? window.FF_BASE : './') + 'custom.yaml';
    const response = await fetch(cfgPath);
    if (!response.ok) throw new Error('Config yuklenemedi');
    const yamlText = await response.text();
    siteConfig = jsyaml.load(yamlText);
    applyConfig();
  } catch (err) {
    console.warn('custom.yaml yuklenemedi, varsayilan degerler kullaniliyor:', err);
    siteConfig = getDefaultConfig();
    applyConfig();
  }
}

function getDefaultConfig() {
  return {
    site: {
      title: 'user@hostname:~/',
      user: 'user',
      host: 'hostname'
    },
    theme: {
      accent_color: '#38bdf8'
    },
    social: {}
  };
}

// "user@host:" oneki - sayfalarin toggleLang()'i bunu kullanir.
function getUserHostPrefix() {
  const site = (siteConfig && siteConfig.site) || {};
  return (site.user || 'user') + '@' + (site.host || 'hostname');
}

// "user@hostname:~/hakkimda" -> "farukylmz@thinkpad:~/hakkimda"
// Path soneki korunur; ana sayfada bu zaten "~/" ile biter.
function applyTitlePrefix() {
  const prefix = getUserHostPrefix();
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

  // Baslik / user / host
  if (site.user || site.host) {
    const userHostEls = document.querySelectorAll('.user-host');
    if (userHostEls.length >= 2) {
      userHostEls[0].textContent = site.user || 'user';
      userHostEls[1].textContent = site.host || 'hostname';
    }
  }
  applyTitlePrefix();

  // Vurgu rengi -> CSS degiskeni
  if (theme.accent_color && theme.accent_color !== '#38bdf8') {
    // Hex/rgb formatini kabaca dogrula, aksi halde varsayilana geri don.
    if (/^#[0-9a-fA-F]{3,8}$/.test(theme.accent_color) || /^rgba?\(/.test(theme.accent_color)) {
      document.documentElement.style.setProperty('--ff-accent', theme.accent_color);
    }
  }

  // Sosyal linkler (custom.yaml social bölümünden)
  // Kutu siniflari site.css'te aynen korunur; URL'si olmayan kutu gizlenir.
  applySocialLinks();
}

function applySocialLinks() {
  const social = (siteConfig && siteConfig.social) || {};
  // key -> CSS kutu sinifi eslesmesi
  const map = {
    github: 'github',
    youtube: 'youtube',
    makerworld: 'makerworld',
    huggingface: 'yellow',
    gravatar: 'gravatar',
    discord: 'purple'
  };

  Object.keys(map).forEach(key => {
    const box = document.querySelector('.social-box.' + map[key]);
    if (!box) return;
    const url = social[key];
    if (url && typeof url === 'string' && url.trim()) {
      box.href = url.trim();
      box.target = '_blank';
      box.rel = 'noopener noreferrer';
    } else {
      // URL tanimlanmamissa kutuyu gizle (bos '#' linki kalmasin).
      box.style.display = 'none';
    }
  });
}

// Sayfa yuklendiginde config'i yukle
document.addEventListener('DOMContentLoaded', loadConfig);
