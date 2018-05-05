# Dockerfile extending the generic PHP image with application files for a
# single application.
# FROM gcr.io/google-appengine/php:2018-01-18-17-04
FROM gcr.io/google-appengine/php:2018-03-27-16-05

## run it again as it does not get it all.
RUN php -d "disable_functions=" /usr/local/bin/composer install

ENV DOCUMENT_ROOT=/app/web

ENV FRONT_CONTROLLER_FILE=app.php

ENV SKIP_LOCKDOWN_DOCUMENT_ROOT=true

RUN cp app/config/parameters.yml.dist app/config/parameters.yml

RUN echo "deb http://ftp.debian.org/debian jessie-backports main" > /etc/apt/sources.list.d/backports.list
RUN apt-get update -o Dir::Etc::sourcelist="sources.list.d/backports.list" \
-o Dir::Etc::sourceparts="-" -o APT::Get::List-Cleanup="0"
RUN apt-get --assume-yes -t jessie-backports install certbot
RUN mkdir /var/www/letsencrypt
RUN cp -r letsencrypt/* /etc/letsencrypt/ && rm -rf letsencrypt

RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/main_mailer_type select Satellite system'"]
RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/mailname string destromachines.com'"]
RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/relayhost string [smtp.mailgun.org]:2525'"]

RUN DEBIAN_FRONTEND=noninteractive apt-get --assume-yes install nano postfix rsyslog libsasl2-modules cron jpegoptim; mkfifo /var/spool/postfix/public/pickup
COPY crond.conf /etc/supervisor/conf.d/crond.conf
RUN crontab -l | { cat; echo "0 */12 * * * root /usr/bin/certbot renew --renew-hook \"/usr/bin/supervisorctl restart nginx\""; } | crontab -

RUN curl -sSLO https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 && \
mv cloud_sql_proxy.linux.amd64 cloud_sql_proxy && chmod +x cloud_sql_proxy

RUN mkdir /cloudsql

RUN mv ./sasl_passwd /etc/postfix/sasl_passwd;chown root:root /etc/postfix/sasl_passwd; postmap /etc/postfix/sasl_passwd;rm /etc/postfix/sasl_passwd;chmod 600 /etc/postfix/sasl_passwd.db

RUN echo "[supervisorctl]\n[inet_http_server]\nport = 127.0.0.1:9001\n[rpcinterface:supervisor]\nsupervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface\n" >> /etc/supervisor/supervisord.conf
COPY cloud_sql_proxy.conf /etc/supervisor/conf.d/cloud_sql_proxy.conf

RUN cp /etc/resolv.conf /var/spool/postfix/etc/; \
echo 'smtp_tls_security_level = encrypt' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_auth_enable = yes' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_security_options = noanonymous' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_tls_security_options = noanonymous' >> /etc/postfix/main.cf; \
echo 'smtp_sasl_mechanism_filter = AUTH LOGIN' >> /etc/postfix/main.cf

COPY postfix.conf /etc/supervisor/conf.d/postfix.conf
# Workaround for AUFS-related permission issue:
# See https://github.com/docker/docker/issues/783#issuecomment-56013588

RUN cp -R /app /app-copy; rm -r /app; mv /app-copy /app; chmod -R 750 /app; chown -R www-data:www-data /app

EXPOSE 8080 80 443
