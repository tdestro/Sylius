# Dockerfile extending the generic PHP image with application files for a
# single application.
FROM gcr.io/google-appengine/php:2018-01-18-17-04

# Add our NGINX and php.ini config
ENV DOCUMENT_ROOT=/app/web

ENV FRONT_CONTROLLER_FILE=app_dev.php

RUN cp app/config/parameters.yml.dist app/config/parameters.yml


RUN curl -sSLO https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 && \
mv cloud_sql_proxy.linux.amd64 cloud_sql_proxy && chmod +x cloud_sql_proxy

RUN mkdir /cloudsql

COPY cloud_sql_proxy.conf /etc/supervisor/conf.d/cloud_sql_proxy.conf

# Workaround for AUFS-related permission issue:
# See https://github.com/docker/docker/issues/783#issuecomment-56013588
RUN cp -R ${APP_DIR} ${APP_DIR}-copy; rm -r ${APP_DIR}; mv ${APP_DIR}-copy ${APP_DIR}; chmod -R 750 ${APP_DIR}; chown -R www-data:www-data ${APP_DIR}

EXPOSE 8080
