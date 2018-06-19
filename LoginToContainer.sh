#!/bin/bash
gcloud compute --project "destromachinesstore" ssh --zone "us-central1-c" "sylius" --command="docker exec -i sylius bash"