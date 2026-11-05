#!/usr/bin/env python3
"""Small self-hosted FetchFolio admin API (stdlib only)."""
import hashlib, hmac, json, os, re, secrets, time
from http import HTTPStatus
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlparse

BLOG_DIR = Path(os.environ.get('BLOG_DIR', '/app/content/blog'))
DATA_DIR = Path(os.environ.get('DATA_DIR', '/data'))
AUTH_FILE = DATA_DIR / 'admin.json'
SLUG = re.compile(r'^[a-z0-9][a-z0-9-]{1,79}$')
SESSIONS = {}

def read_json(path, fallback):
    try: return json.loads(path.read_text(encoding='utf-8'))
    except (OSError, ValueError): return fallback

def posts():
    return read_json(BLOG_DIR / 'index.json', [])

def save_posts(items):
    tmp = BLOG_DIR / 'index.json.tmp'
    tmp.write_text(json.dumps(items, indent=2, ensure_ascii=False) + '\n', encoding='utf-8')
    tmp.replace(BLOG_DIR / 'index.json')

def password_hash(password, salt=None):
    salt = salt or secrets.token_hex(16)
    digest = hashlib.pbkdf2_hmac('sha256', password.encode(), salt.encode(), 240000).hex()
    return {'salt': salt, 'hash': digest}

def authorized(handler):
    token = handler.headers.get('Cookie', '').replace('fetchfolio_session=', '').split(';')[0]
    return token in SESSIONS and SESSIONS[token] > time.time()

class Handler(BaseHTTPRequestHandler):
    def log_message(self, fmt, *args): pass

    def send_json(self, value, status=200):
        raw = json.dumps(value, ensure_ascii=False).encode()
        self.send_response(status); self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Cache-Control', 'no-store'); self.send_header('Content-Length', str(len(raw)))
        self.end_headers(); self.wfile.write(raw)

    def body(self):
        length = min(int(self.headers.get('Content-Length', 0)), 2_000_000)
        return json.loads(self.rfile.read(length))

    def do_GET(self):
        if self.path == '/api/posts': return self.send_json(posts())
        if self.path == '/api/setup/status': return self.send_json({'configured': AUTH_FILE.exists()})
        if self.path == '/api/me': return self.send_json({'authenticated': authorized(self)})
        self.send_json({'error': 'Not found'}, 404)

    def do_POST(self):
        try: data = self.body()
        except (ValueError, json.JSONDecodeError): return self.send_json({'error': 'Invalid JSON'}, 400)
        if self.path == '/api/setup':
            if AUTH_FILE.exists(): return self.send_json({'error': 'Admin is already configured'}, 409)
            username, password = data.get('username','').strip(), data.get('password','')
            if len(username) < 3 or len(password) < 12: return self.send_json({'error': 'Username min 3, password min 12 characters'}, 400)
            DATA_DIR.mkdir(parents=True, exist_ok=True); AUTH_FILE.write_text(json.dumps({'username': username, **password_hash(password)}), encoding='utf-8')
            return self.send_json({'ok': True})
        if self.path == '/api/login':
            account = read_json(AUTH_FILE, {})
            check = password_hash(data.get('password',''), account.get('salt'))
            if not hmac.compare_digest(data.get('username',''), account.get('username','')) or not hmac.compare_digest(check['hash'], account.get('hash','')):
                return self.send_json({'error': 'Invalid credentials'}, 401)
            token = secrets.token_urlsafe(32); SESSIONS[token] = time.time() + 86400
            self.send_response(200); self.send_header('Content-Type','application/json'); self.send_header('Set-Cookie', f'fetchfolio_session={token}; HttpOnly; SameSite=Strict; Path=/; Max-Age=86400'); self.end_headers(); self.wfile.write(b'{"ok":true}'); return
        if self.path == '/api/logout':
            if authorized(self): SESSIONS.pop(self.headers.get('Cookie','').split('=',1)[-1].split(';')[0], None)
            return self.send_json({'ok': True})
        if self.path == '/api/posts':
            if not authorized(self): return self.send_json({'error':'Unauthorized'}, 401)
            slug, title, markdown = data.get('slug','').strip(), data.get('title','').strip(), data.get('markdown','')
            if not SLUG.fullmatch(slug) or not title or not isinstance(markdown, str): return self.send_json({'error':'Valid slug, title and Markdown are required'}, 400)
            items = posts()
            if any(p.get('slug') == slug for p in items): return self.send_json({'error':'Slug already exists'}, 409)
            BLOG_DIR.mkdir(parents=True, exist_ok=True); (BLOG_DIR / f'{slug}.md').write_text(markdown, encoding='utf-8')
            items.insert(0, {'slug':slug, 'title':title, 'date':data.get('date') or time.strftime('%Y-%m-%d'), 'excerpt':data.get('excerpt','').strip(), 'file':f'{slug}.md'})
            save_posts(items); return self.send_json({'ok': True}, 201)
        self.send_json({'error':'Not found'}, 404)

    def do_PUT(self):
        if not authorized(self): return self.send_json({'error':'Unauthorized'}, 401)
        slug = urlparse(self.path).path.removeprefix('/api/posts/')
        if not SLUG.fullmatch(slug): return self.send_json({'error':'Invalid slug'}, 400)
        try: data = self.body()
        except (ValueError, json.JSONDecodeError): return self.send_json({'error':'Invalid JSON'}, 400)
        items = posts(); post = next((p for p in items if p.get('slug') == slug), None)
        if not post: return self.send_json({'error':'Not found'}, 404)
        (BLOG_DIR / post['file']).write_text(data.get('markdown',''), encoding='utf-8')
        for key in ('title','excerpt','date'):
            if key in data: post[key] = data[key]
        save_posts(items); self.send_json({'ok':True})

    def do_DELETE(self):
        if not authorized(self): return self.send_json({'error':'Unauthorized'}, 401)
        slug = urlparse(self.path).path.removeprefix('/api/posts/')
        items = posts(); post = next((p for p in items if p.get('slug') == slug), None)
        if not post: return self.send_json({'error':'Not found'}, 404)
        (BLOG_DIR / post['file']).unlink(missing_ok=True); save_posts([p for p in items if p.get('slug') != slug]); self.send_json({'ok':True})

if __name__ == '__main__':
    BLOG_DIR.mkdir(parents=True, exist_ok=True); DATA_DIR.mkdir(parents=True, exist_ok=True)
    ThreadingHTTPServer(('0.0.0.0', int(os.environ.get('PORT', '3001'))), Handler).serve_forever()
