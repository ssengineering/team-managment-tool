#! /bin/bash

# Pull images
echo "Pulling docker images...";
docker-compose pull --allow-insecure-ssl;

# Install PHP dependencies with composer
echo "Installing dependencies via Composer...";
docker-compose -f helpers.yml up composer;

# Build db importer
echo "Building database import container";
docker-compose -f helpers.yml build dbimport;

# Import the backup of sites_ops database and structure of microservice databases
echo "Importing the database...";
docker-compose -f helpers.yml up dbimport;

# Generate all necessary RSA keys
echo "Generating RSA keys";
./generateKeys.sh;
