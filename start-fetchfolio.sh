#!/bin/sh
set -eu
BLOG_DIR=/usr/share/nginx/html/content/blog DATA_DIR=/data python3 /usr/share/nginx/html/server.py &
exec nginx -g 'daemon off;'
