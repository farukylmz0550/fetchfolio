// fetchfolio - Markdown loader
// Fetches a markdown file and renders it as HTML inside #content.
// XHR fallback exists for file:// pages (status 0), where fetch() may fail.

function loadMarkdown(file) {
  const contentEl = document.getElementById('content');
  if (!contentEl) return;

  function render(md) {
    if (typeof marked === 'undefined') {
      contentEl.innerHTML = '<p style="color:#ef4444;">Failed to load marked.js.</p>';
    } else {
      contentEl.innerHTML = marked.parse(md);
    }
  }

  function fail(err) {
    contentEl.innerHTML =
      '<p style="color:#ef4444;">Failed to load Markdown.</p>' +
      '<p style="color:#64748b; font-size:12px; margin-top:8px;">' +
      'File: ' + file + '<br>' +
      'If you opened this page via file://, serve the site over HTTP instead:<br>' +
      '<code style="color:#38bdf8;">python3 -m http.server 2222</code></p>';
  }

  fetch(file)
    .then(res => {
      if (!res.ok) throw new Error(res.status);
      return res.text();
    })
    .then(render)
    .catch(() => {
      const xhr = new XMLHttpRequest();
      xhr.open('GET', file, true);
      xhr.onload = () => (xhr.status === 200 || xhr.status === 0) ? render(xhr.responseText) : fail(xhr.status);
      xhr.onerror = () => fail(null);
      xhr.send();
    });
}
