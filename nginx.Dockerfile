FROM nginx:1.18-alpine AS nginx

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80