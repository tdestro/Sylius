#!/bin/bash
gcloud compute --project "destromachinesstore" ssh --zone "us-central1-c" "sylius" --command="container=\$(docker ps --format '{{.Names}}' 2>&1 | sed 1q) && docker exec -i \$container bash -c 'tar -zcvf /destromachines-letsencrypt.tar.gz /etc/letsencrypt' && docker cp \$container:/destromachines-letsencrypt.tar.gz /tmp/destromachines-letsencrypt.tar.gz && docker exec -i \$container bash -c 'rm /destromachines-letsencrypt.tar.gz'"
gcloud compute scp sylius:/tmp/destromachines-letsencrypt.tar.gz ./destromachines-letsencrypt.tar.gz
gcloud compute --project "destromachinesstore" ssh --zone "us-central1-c" "sylius" --command="rm /tmp/destromachines-letsencrypt.tar.gz"
rm -rf ./letsencrypt
mkdir ./letsencrypt
touch ./letsencrypt/.gitkeep
tar xvzf ./destromachines-letsencrypt.tar.gz --strip=1
rm ./destromachines-letsencrypt.tar.gz