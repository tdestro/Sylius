#!/bin/bash
gcloud compute --project "destromachinesstore" ssh --zone "us-central1-c" "sylius" --command="docker exec -i sylius bash -c 'tar -zcvf /destromachines-letsencrypt.tar.gz /etc/letsencrypt'; docker cp sylius:/destromachines-letsencrypt.tar.gz /tmp/destromachines-letsencrypt.tar.gz; docker exec -i sylius bash -c 'rm /destromachines-letsencrypt.tar.gz'"
gcloud compute scp sylius:/tmp/destromachines-letsencrypt.tar.gz ./destromachines-letsencrypt.tar.gz
gcloud compute --project "destromachinesstore" ssh --zone "us-central1-c" "sylius" --command="rm /tmp/destromachines-letsencrypt.tar.gz"
rm -rf ./letsencrypt
mkdir ./letsencrypt
touch ./letsencrypt/.gitkeep
tar xvzf ./destromachines-letsencrypt.tar.gz --strip=1
rm ./destromachines-letsencrypt.tar.gz