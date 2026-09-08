// fetchfolio - Config Loader
// custom.yaml dosyasini okuyup siteyi gunceller

let siteConfig = null;

async function loadConfig() {
  try {
    const response = await fetch('/custom.yaml');
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
    }
  };
}

function applyConfig() {
  if (!siteConfig) return;

  // Title guncelle
  if (siteConfig.site && siteConfig.site.title) {
    document.title = siteConfig.site.title;
    const terminalTitle = document.querySelector('.terminal-title');
    if (terminalTitle) terminalTitle.textContent = siteConfig.site.title;
  }

  // User/Host guncelle
  if (siteConfig.site) {
    const userHostEls = document.querySelectorAll('.user-host');
    if (userHostEls.length >= 2) {
      userHostEls[0].textContent = siteConfig.site.user || 'user';
      userHostEls[1].textContent = siteConfig.site.host || 'hostname';
    }
  }

  // Accent color guncelle
  if (siteConfig.theme && siteConfig.theme.accent_color) {
    const color = siteConfig.theme.accent_color;
    const style = document.createElement('style');
    style.textContent = `
      .ascii-art { color: ${color} !important; }
      .user-host { color: ${color} !important; }
      .key { color: ${color} !important; }
      .social-box:hover { border-color: ${color} !important; }
      .lang-btn:hover { color: ${color} !important; border-color: ${color} !important; }
      .back-link { color: ${color} !important; }
      .cursor { background: ${color} !important; }
      .terminal-body::-webkit-scrollbar-thumb:hover { background: ${color} !important; }
    `;
    document.head.appendChild(style);
  }
}

// Sayfa yuklendiginde config'i yukle
document.addEventListener('DOMContentLoaded', loadConfig);
