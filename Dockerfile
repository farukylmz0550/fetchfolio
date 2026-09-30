# FetchFolio — statik siteyi nginx ile servis eder
FROM nginx:1.27-alpine

# Varsayılan config'i kaldır, FetchFolio config'ini koy
RUN rm /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/fetchfolio.conf

# Site dosyaları
COPY index.html 404.html favicon.ico custom.yaml /usr/share/nginx/html/
COPY assets/ /usr/share/nginx/html/assets/
COPY content/ /usr/share/nginx/html/content/
COPY src/ /usr/share/nginx/html/src/

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
