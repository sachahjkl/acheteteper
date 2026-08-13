FROM php:8.5-fpm-alpine

ENV APP_DIR=/app
ENV DB_PATH=/data/database.db
ENV UPLOADS_PATH=/tmp/uploads
ENV DEBUG=false

RUN apk update && apk add --no-cache nginx php85-fileinfo php85-pdo_sqlite

WORKDIR ${APP_DIR}

COPY . ${APP_DIR}

RUN mkdir -p /data /var/run/php /var/cache/nginx /var/log/nginx \
    && if [ -f /app/data/database.db ]; then cp /app/data/database.db ${DB_PATH}; fi \
    && mkdir -p ${UPLOADS_PATH} \
    && if [ -d /app/src/uploads ] && [ -z "$(ls -A ${UPLOADS_PATH})" ]; then cp -r /app/src/uploads/. ${UPLOADS_PATH}/; fi \
    && chmod -R a+rX ${APP_DIR} \
    && chown -R www-data:www-data /data ${UPLOADS_PATH}

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

EXPOSE 8000

CMD ["/entrypoint.sh"]
