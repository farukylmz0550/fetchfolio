function loadMarkdown(file) {
  const contentEl = document.getElementById('content');
  if (!contentEl) return;

  if (typeof marked === 'undefined') {
    contentEl.innerHTML = '<p style="color:#ef4444;">marked.js yüklenemedi.</p>';
    return;
  }

  const lang = localStorage.getItem('lang') || 'tr';
  const basePath = file.replace(/[^/]+$/, '');
  const fileName = file.split('/').pop();
  const langPrefix = lang + '/';
  const fullPath = basePath + langPrefix + fileName;

  function tryFetch(url) {
    return new Promise((resolve, reject) => {
      fetch(url).then(res => {
        if (!res.ok) throw new Error(res.status);
        return res.text();
      }).then(resolve).catch(reject);
    });
  }

  function tryXHR(url) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('GET', url, true);
      xhr.onload = () => {
        if (xhr.status === 200 || xhr.status === 0) resolve(xhr.responseText);
        else reject(new Error(xhr.status));
      };
      xhr.onerror = () => reject(new Error('network'));
      xhr.send();
    });
  }

  function load(url) {
    return tryFetch(url).catch(() => tryXHR(url));
  }

  load(fullPath)
    .then(md => {
      contentEl.innerHTML = marked.parse(md);
    })
    .catch(() => {
      load(file)
        .then(md => {
          contentEl.innerHTML = marked.parse(md);
        })
        .catch(() => {
          contentEl.innerHTML =
            '<p style="color:#ef4444;">Markdown yüklenemedi.</p>' +
            '<p style="color:#64748b; font-size:12px; margin-top:8px;">' +
            'Dosya: ' + fullPath + '<br>' +
            'Eğer file:// ile açtıysanız bir HTTP sunucusu kullanın:<br>' +
            '<code style="color:#38bdf8;">python3 -m http.server 8000</code></p>';
        });
    });
}
