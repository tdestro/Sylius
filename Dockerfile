FROM debian:stretch-slim

ENV DEBIAN_FRONTEND noninteractive
ENV COMPOSER_HOME /opt/composer

RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/main_mailer_type select Satellite system'"]
RUN ["/bin/bash", "-c", "debconf-set-selections <<< 'postfix postfix/relayhost string [smtp.mailgun.org]:2525'"]

# Install some useful packages
RUN apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install \
        apt-transport-https bash-completion ca-certificates curl gnupg imagemagick \
        less make perceptualdiff procps ssh-client sudo vim wget nginx openssl \
        supervisor  nano postfix libsasl2-modules cron jpegoptim unzip expect && \
    apt-get clean && apt-get --yes --quiet autoremove --purge && \
    rm -rf  /var/lib/apt/lists/* /tmp/* /var/tmp/* \
            /usr/share/doc/* /usr/share/groff/* /usr/share/info/* /usr/share/linda/* \
            /usr/share/lintian/* /usr/share/locale/* /usr/share/man/*

# Add Sury PHP repository
RUN wget -O sury.gpg https://packages.sury.org/php/apt.gpg && apt-key add sury.gpg && rm sury.gpg
ADD dockerincludes/sury.list /etc/apt/sources.list.d/sury.list
COPY ./dockerincludes/stretch-backports.list /etc/apt/sources.list.d/stretch-backports.list

# Install PHP with some extensions
RUN apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet -t stretch-backports install \
        php7.2-cli php7.2-apcu php7.2-mbstring php7.2-curl php7.2-gd php7.2-imagick php7.2-intl php7.2-bcmath \
        php7.2-pgsql php7.2-soap php7.2-xdebug php7.2-xml php7.2-zip php7.2-ldap \
        php7.2-fpm php7.2-dev python-certbot-nginx php-pear && \
    apt-get clean && apt-get --yes --quiet autoremove --purge && \
    rm -rf  /var/lib/apt/lists/* /tmp/* /var/tmp/* \
            /usr/share/doc/* /usr/share/groff/* /usr/share/info/* /usr/share/linda/* \
            /usr/share/lintian/* /usr/share/locale/* /usr/share/man/*

RUN pecl channel-update pecl.php.net && \
pear channel-update pear.php.net && \
pecl install protobuf && \
echo "extension=protobuf.so" >> /etc/php/7.2/fpm/php.ini && \
echo "extension=protobuf.so" >> /etc/php/7.2/cli/php.ini

# RUN phpdismod xdebug
# Activate xdebug while on gce.
RUN echo "[Xdebug]" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.remote_host = 74.111.126.192" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.remote_autostart = 0" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.remote_enable = 1" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.remote_port = 9005" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.default_enable = 1" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.remote_connect_back = 0" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.profiler_enable=0" >> /etc/php/7.2/fpm/php.ini && \
echo "xdebug.max_nesting_level=10000" >> /etc/php/7.2/fpm/php.ini

# Configure PHP FPM
RUN sed -i "s/user = www-data/user = docker/" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i "s/group = www-data/group = docker/" /etc/php/7.2/fpm/pool.d/www.conf

RUN mkdir -p /run/php && sed -i "s/listen = .*/listen = 9001/" /etc/php/7.2/fpm/pool.d/www.conf

# Install cloud proxy.
RUN curl -sSLO https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 && \
mv cloud_sql_proxy.linux.amd64 /usr/sbin/cloud_sql_proxy && chmod +x /usr/sbin/cloud_sql_proxy && mkdir /cloudsql /etc/cloud_sql_proxy
COPY ./dockerincludes/DestroMachinesStore-2601b370cb00.json /etc/cloud_sql_proxy/DestroMachinesStore-2601b370cb00.json

# Setup Postfix to talk to mailgun
COPY ./dockerincludes/sasl_passwd /etc/postfix/sasl_passwd

RUN chown root:root /etc/postfix/sasl_passwd && \
postmap /etc/postfix/sasl_passwd && \
rm /etc/postfix/sasl_passwd && \
chmod 600 /etc/postfix/sasl_passwd.db && \
echo 'smtp_tls_security_level = encrypt' >> /etc/postfix/main.cf && \
echo 'smtp_sasl_auth_enable = yes' >> /etc/postfix/main.cf && \
echo 'smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd' >> /etc/postfix/main.cf && \
echo 'smtp_sasl_security_options = noanonymous' >> /etc/postfix/main.cf && \
echo 'smtp_sasl_tls_security_options = noanonymous' >> /etc/postfix/main.cf && \
echo 'smtp_sasl_mechanism_filter = AUTH LOGIN' >> /etc/postfix/main.cf

# Setup supervisor
RUN mkdir -p /var/log/supervisor
COPY ./dockerincludes/supervisord.conf /etc/supervisor/supervisord.conf

# Setup PHP-FPM
COPY ./dockerincludes/php-fpm.conf /etc/php/7.2/fpm/php-fpm.conf

# Setup NGINX
COPY ./dockerincludes/nginx.conf ./dockerincludes/fastcgi_params ./dockerincludes/gzip_params /etc/nginx/

# Setup Let's Encrypt
RUN mkdir /var/www/letsencrypt
COPY ./letsencrypt /etc/letsencrypt
RUN rm -rf letsencrypt

RUN ["/bin/bash", "-c", "ln -s `ls -rt /etc/letsencrypt/archive/destromachines.com/cert* | tail -n1` /etc/letsencrypt/live/destromachines.com/cert.pem"]
RUN ["/bin/bash", "-c", "ln -s `ls -rt /etc/letsencrypt/archive/destromachines.com/chain* | tail -n1` /etc/letsencrypt/live/destromachines.com/chain.pem"]
RUN ["/bin/bash", "-c", "ln -s `ls -rt /etc/letsencrypt/archive/destromachines.com/fullchain* | tail -n1` /etc/letsencrypt/live/destromachines.com/fullchain.pem"]
RUN ["/bin/bash", "-c", "ln -s `ls -rt /etc/letsencrypt/archive/destromachines.com/privkey* | tail -n1` /etc/letsencrypt/live/destromachines.com/privkey.pem"]

# Add a "docker" user
RUN useradd docker --shell /bin/bash --create-home \
  && usermod --append --groups sudo docker \
  && echo 'ALL ALL = (ALL) NOPASSWD: ALL' >> /etc/sudoers \
  && echo 'docker:secret' | chpasswd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
# Composer needs a writable cache directory.
mkdir -p $COMPOSER_HOME && \
chown -R www-data.www-data $COMPOSER_HOME

# Copy all app files
RUN chsh -s /bin/bash www-data && \
mkdir /app
COPY . /app
COPY ./dockerincludes/DestroMachinesStore-965e04f545e7.json /app/DestroMachinesStore-965e04f545e7.json
RUN cd /app && \
chown -R www-data.www-data /app && \
su -m www-data -c "php -d auto_prepend_file='' /usr/local/bin/composer \
install \
--optimize-autoloader \
--no-interaction \
--no-ansi \
--no-progress \
--no-scripts --prefer-dist"
RUN su -m www-data -c "/app/bin/console cache:clear -vvv --env=prod --no-debug" && \
/app/bin/console ckeditor:install --env=prod --no-interaction --no-ansi && \
/app/bin/console assets:install public --env=prod --no-interaction --no-ansi
RUN chsh -s /usr/sbin/nologin www-data

COPY ./dockerincludes/config.js /app/web/bundles/fosckeditor/config.js

# Performance related changes.
RUN sed -i 's/;opcache.memory_consumption.*/opcache.memory_consumption=256/' /etc/php/7.2/fpm/php.ini; sed -i 's/;opcache.max_accelerated_files.*/opcache.max_accelerated_files=20000/' /etc/php/7.2/fpm/php.ini; sed -i 's/;opcache.validate_timestamps.*/opcache.validate_timestamps=0/' /etc/php/7.2/fpm/php.ini; sed -i 's/;realpath_cache_size.*/realpath_cache_size=4096K/' /etc/php/7.2/fpm/php.ini;
RUN sed -i 's/post_max_size = .*/post_max_size = 10M/' /etc/php/7.2/fpm/php.ini; sed -i 's/upload_max_filesize = .*/upload_max_filesize = 10M/' /etc/php/7.2/fpm/php.ini; sed -i 's/memory_limit = .*/memory_limit = -1/' /etc/php/7.2/fpm/php.ini;
RUN sed -i -r -e 's/display_errors = Off/display_errors = On/g' /etc/php/7.2/fpm/php.ini

# Disable ckeditor from stripping markup.
WORKDIR /home/docker/

ENV PATH=bin:vendor/bin:$PATH

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]