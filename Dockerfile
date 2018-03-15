# Dockerfile extending the generic PHP image with application files for a
# single application.
# FROM gcr.io/google-appengine/php:2018-01-18-17-04
FROM gcr.io/google-appengine/php:latest

## run it again as it does not get it all.
RUN php -d "disable_functions=" /usr/local/bin/composer install

# Add our NGINX and php.ini config
ENV DOCUMENT_ROOT=/app/web

ENV FRONT_CONTROLLER_FILE=app_dev.php

RUN cp app/config/parameters.yml.dist app/config/parameters.yml

RUN echo "deb http://ftp.debian.org/debian jessie-backports main" > /etc/apt/sources.list.d/backports.list
RUN apt-get update -o Dir::Etc::sourcelist="sources.list.d/backports.list" \
-o Dir::Etc::sourceparts="-" -o APT::Get::List-Cleanup="0"
RUN apt-get --assume-yes -t jessie-backports install certbot
RUN mkdir /var/www/letsencrypt
RUN cp -r letsencrypt/* /etc/letsencrypt/ && rm -rf letsencrypt


RUN apt-get --assume-yes install nano cron
COPY crond.conf /etc/supervisor/conf.d/crond.conf
RUN crontab -l | { cat; echo "0 */12 * * * root /usr/bin/certbot renew --renew-hook \"/usr/bin/supervisorctl restart nginx\""; } | crontab -

RUN curl -sSLO https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 && \
mv cloud_sql_proxy.linux.amd64 cloud_sql_proxy && chmod +x cloud_sql_proxy

RUN mkdir /cloudsql

RUN echo "[supervisorctl]\n[inet_http_server]\nport = 127.0.0.1:9001\n[rpcinterface:supervisor]\nsupervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface\n" >> /etc/supervisor/supervisord.conf
COPY cloud_sql_proxy.conf /etc/supervisor/conf.d/cloud_sql_proxy.conf

# Workaround for AUFS-related permission issue:
# See https://github.com/docker/docker/issues/783#issuecomment-56013588
RUN cp -R ${APP_DIR} ${APP_DIR}-copy; rm -r ${APP_DIR}; mv ${APP_DIR}-copy ${APP_DIR}; chmod -R 750 ${APP_DIR}; chown -R www-data:www-data ${APP_DIR}

EXPOSE 8080 80 443
