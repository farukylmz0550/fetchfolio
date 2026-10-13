# FetchFolio — serves the static site with nginx
FROM nginx:1.27-alpine

# Remove the default config and use the FetchFolio one
RUN rm /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/fetchfolio.conf

# Site files
COPY index.html 404.html about.html achievements.html contact.html cv.html education.html licenses.html favicon.ico robots.txt sitemap.xml fetchfolio.config.yaml /usr/share/nginx/html/
COPY assets/ /usr/share/nginx/html/assets/
COPY content/ /usr/share/nginx/html/content/
COPY src/ /usr/share/nginx/html/src/

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
