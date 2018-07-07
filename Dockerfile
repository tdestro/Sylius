# Dockerfile extending the generic PHP image with application files for a
# single application.
FROM gcr.io/google-appengine/php:2018-05-30-14-38
#:2018-05-01-12-13

#RUN php -d "disable_functions=" /usr/local/bin/composer install

ENV DOCUMENT_ROOT=/app/web

ENV FRONT_CONTROLLER_FILE=app.php

ENV SKIP_LOCKDOWN_DOCUMENT_ROOT=true

RUN cp app/config/parameters.yml.dist app/config/parameters.yml

RUN apt-get update; apt-get --assume-yes install software-properties-common; add-apt-repository ppa:certbot/certbot; apt-get update;
RUN apt-get --assume-yes install python-certbot-nginx
RUN mkdir /var/www/letsencrypt
RUN cp -r letsencrypt/* /etc/letsencrypt/ && rm -rf letsencrypt

RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/main_mailer_type select Satellite system'"]
RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/relayhost string [smtp.mailgun.org]:2525'"]

RUN DEBIAN_FRONTEND=noninteractive apt-get --assume-yes install nano postfix rsyslog libsasl2-modules cron jpegoptim; mkfifo /var/spool/postfix/public/pickup

RUN crontab -l | { cat; echo "0 */12 * * * root /usr/bin/certbot renew --renew-hook \"/usr/bin/supervisorctl restart nginx\""; } | crontab -

RUN curl -sSLO https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 && \
mv cloud_sql_proxy.linux.amd64 cloud_sql_proxy && chmod +x cloud_sql_proxy

RUN mkdir /cloudsql

RUN mv ./sasl_passwd /etc/postfix/sasl_passwd;chown root:root /etc/postfix/sasl_passwd; postmap /etc/postfix/sasl_passwd;rm /etc/postfix/sasl_passwd;chmod 600 /etc/postfix/sasl_passwd.db

RUN echo "[supervisorctl]\n[inet_http_server]\nport = 127.0.0.1:9001\n[rpcinterface:supervisor]\nsupervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface\n" >> /etc/supervisor/supervisord.conf
COPY cloud_sql_proxy.conf /etc/supervisor/conf.d/cloud_sql_proxy.conf
COPY crond.conf /etc/supervisor/conf.d/crond.conf

RUN echo 'smtp_tls_security_level = encrypt' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_auth_enable = yes' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_security_options = noanonymous' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_tls_security_options = noanonymous' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_mechanism_filter = AUTH LOGIN' >> /etc/postfix/main.cf

RUN sed -i 's/disable_functions = exec, passthru, proc_open, proc_close, shell_exec, show_source, symlink, system/disable_functions = exec, passthru, shell_exec, show_source, symlink, system/g' /opt/php72/lib/php.ini

COPY postfix.conf /etc/supervisor/conf.d/postfix.conf
# Workaround for AUFS-related permission issue:
# See https://github.com/docker/docker/issues/783#issuecomment-56013588

# Performance related changes.
RUN sed -i 's/;opcache.memory_consumption.*/opcache.memory_consumption=256/' /opt/php72/lib/php.ini; sed -i 's/;opcache.max_accelerated_files.*/opcache.max_accelerated_files=20000/' /opt/php72/lib/php.ini; sed -i 's/;opcache.validate_timestamps.*/opcache.validate_timestamps=0/' /opt/php72/lib/php.ini; sed -i 's/;realpath_cache_size.*/realpath_cache_size=4096K/' /opt/php72/lib/php.ini;
RUN sed -i 's/post_max_size = .*/post_max_size = 5M/' /opt/php72/lib/php.ini; sed -i 's/upload_max_filesize = .*/upload_max_filesize = 5M/' /opt/php72/lib/php.ini; sed -i 's/memory_limit = .*/memory_limit = -1/' /opt/php72/lib/php.ini; rm -Rf /app/var/cache/*; chown -R www-data:www-data /app/var;
#cache dirs cannnot be owned by root but by www-data. clearing the cache should be done by rm -Rf /app/var/cache/*; otherwise it explodes
RUN sed -i -r -e 's/display_errors = Off/display_errors = On/g' /opt/php72/lib/php.ini
#RUN sed -i 's/;opcache.error_log.*/opcache.error_log=\/app\/opcache.log/' /opt/php72/lib/php.ini;
#RUN sed -i 's/;opcache.log_verbosity_level.*/opcache.log_verbosity_level=4/' /opt/php72/lib/php.ini;
#RUN sed -i 's/;log_level = notice/log_level = debug/' /opt/php/etc/php-fpm.conf;
#RUN sed -i 's/error_log.*/error_log = \/app\/php-fpm.log/' /opt/php/etc/php-fpm.conf;
#
#RUN /app/bin/console --env=prod --no-debug cache:warmup
#curl -Is fastcgi://127.0.0.1:9000/en_US/taxons/category/gear | head -n 1
#2018/06/29 01:33:05 [error] 38#38: *7 upstream timed out (110: Connection timed out) while reading response header from upstream, client: 74.111.126.192, server: www.destromachines.com, request: "GET /en_US/taxons/category/gear HTTP/1.1", upstream: "http://127.0.0.1:8080/en_US/taxons/category/gear", host: "35.193.153.87", referrer: "https://35.193.153.87/en_US/pages/blog"
 #2018/06/29 01:33:05 [info] 38#38: *114 epoll_wait() reported that client prematurely closed connection, so upstream connection is closed too while sending request to upstream, client: 127.0.0.1, server: , request: "GET /en_US/taxons/category/gear HTTP/1.0", upstream: "fastcgi://127.0.0.1:9000", host: "35.193.153.87", referrer: "https://35.193.153.87/en_US/pages/blog"
RUN bin/console ckeditor:install --env=prod
RUN chown -R www-data:www-data /app/var

# dynamic php fpm takes too many resources for our tiny box.
RUN sed -i 's/pm = dynamic/pm = ondemand/' /opt/php/etc/php-fpm.conf;

EXPOSE 8080 80 443