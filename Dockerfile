# FetchFolio — serves the static site with nginx
FROM nginx:1.27-alpine

# Remove the default config and use the FetchFolio one
RUN apk add --no-cache python3 && rm /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/fetchfolio.conf

# Site files
COPY index.html admin.html 404.html about.html achievements.html contact.html cv.html education.html licenses.html blog.html post.html favicon.ico robots.txt sitemap.xml fetchfolio.config.yaml server.py /usr/share/nginx/html/
COPY assets/ /usr/share/nginx/html/assets/
COPY content/ /usr/share/nginx/html/content/
COPY src/ /usr/share/nginx/html/src/
COPY start-fetchfolio.sh /usr/local/bin/start-fetchfolio
RUN chmod +x /usr/local/bin/start-fetchfolio

EXPOSE 80
VOLUME ["/data"]

CMD ["/usr/local/bin/start-fetchfolio"]
