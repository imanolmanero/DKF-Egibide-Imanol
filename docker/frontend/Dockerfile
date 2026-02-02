# Build
FROM node:20-alpine AS build

WORKDIR /app

COPY ../../frontend/package*.json ./
RUN npm ci

COPY ../../frontend/ .
RUN npm run build

# Servir web compilada
FROM httpd:2.4-alpine

RUN sed -i 's/#LoadModule rewrite_module/LoadModule rewrite_module/' /usr/local/apache2/conf/httpd.conf

COPY --from=build /app/dist /usr/local/apache2/htdocs/
COPY docker/frontend/custom-vhost.conf /usr/local/apache2/conf.d/custom-vhost.conf

EXPOSE 80
