FROM php:8.5-fpm-alpine

ENV APP_DIR=/app
ENV DB_PATH=/data/database.db
ENV UPLOADS_PATH=/tmp/uploads
ENV DEBUG=false

RUN apk add --no-cache nginx sqlite

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions pdo pdo_sqlite fileinfo

WORKDIR ${APP_DIR}

COPY . ${APP_DIR}

RUN mkdir -p /data /var/run/php /var/cache/nginx /var/log/nginx \
    && if [ -f /app/data/database.db ]; then cp /app/data/database.db ${DB_PATH}; fi \
    && mkdir -p ${UPLOADS_PATH} \
    && if [ -d /app/src/uploads ] && [ -z "$(ls -A ${UPLOADS_PATH})" ]; then cp -r /app/src/uploads/. ${UPLOADS_PATH}/; fi \
    && chown -R www-data:www-data /data ${UPLOADS_PATH}

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

EXPOSE 8000

CMD ["/entrypoint.sh"]

