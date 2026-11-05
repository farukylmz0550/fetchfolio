// fetchfolio - Static blog loader
// Blog metadata lives in content/blog/index.json and posts are Markdown files.

function blogRoot() {
  const script = document.currentScript && document.currentScript.src;
  return script ? script.replace(/\/src\/js\/load-blog\.js.*$/, '/') : '../../';
}

function escapeHtml(value) {
  return String(value).replace(/[&<>'"]/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
  }[char]));
}

function blogError(el, message) {
  el.innerHTML = '<p style="color:#ef4444;">' + escapeHtml(message) + '</p>';
}

function loadBlogIndex() {
  const list = document.getElementById('blog-list');
  if (!list) return;

  fetch(blogRoot() + 'content/blog/index.json', { cache: 'no-cache' })
    .then(response => {
      if (!response.ok) throw new Error('Could not load the blog catalogue.');
      return response.json();
    })
    .then(posts => {
      if (!Array.isArray(posts) || posts.length === 0) {
        list.innerHTML = '<p class="blog-empty">No posts yet.</p>';
        return;
      }
      list.innerHTML = posts.map(post => {
        const slug = encodeURIComponent(post.slug || '');
        return '<article class="blog-card">' +
          '<div class="blog-meta">' + escapeHtml(post.date || '') + '</div>' +
          '<h2><a href="post.html?slug=' + slug + '">' + escapeHtml(post.title || 'Untitled post') + '</a></h2>' +
          '<p>' + escapeHtml(post.excerpt || '') + '</p>' +
          '<a class="blog-read" href="post.html?slug=' + slug + '">read post &gt;</a>' +
          '</article>';
      }).join('');
    })
    .catch(error => blogError(list, error.message));
}

function loadBlogPost() {
  const content = document.getElementById('content');
  if (!content) return;
  const slug = new URLSearchParams(window.location.search).get('slug');
  if (!slug || !/^[a-z0-9][a-z0-9-]*$/.test(slug)) {
    blogError(content, 'Post not found.');
    return;
  }

  fetch(blogRoot() + 'content/blog/index.json', { cache: 'no-cache' })
    .then(response => response.json())
    .then(posts => {
      const post = posts.find(entry => entry.slug === slug);
      if (!post || !/^[a-z0-9][a-z0-9-]*\.md$/.test(post.file || '')) throw new Error('Post not found.');
      document.title = 'user@hostname:~/blog/' + slug;
      return fetch(blogRoot() + 'content/blog/' + post.file, { cache: 'no-cache' });
    })
    .then(response => {
      if (!response.ok) throw new Error('Could not load the post.');
      return response.text();
    })
    .then(markdown => {
      if (typeof marked === 'undefined') throw new Error('Failed to load marked.js.');
      content.innerHTML = marked.parse(markdown);
    })
    .catch(error => blogError(content, error.message));
}

document.addEventListener('DOMContentLoaded', () => {
  loadBlogIndex();
  loadBlogPost();
});
