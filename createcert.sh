#!/bin/bash
certbot certonly -d destromachines.com -d www.destromachines.com --email=tony.destro@gmail.com -a webroot --webroot-path=/var/www/letsencrypt --agree-tos
