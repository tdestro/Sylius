#!/bin/bash

sudo mkdir /cloudsql
sudo /home/vagrant/cloud_sql_proxy -instances=destromachinesstore:us-central1:destro-machines-store-db -dir=/cloudsql  -credential_file ./DestroMachinesStore-2601b370cb00.json
