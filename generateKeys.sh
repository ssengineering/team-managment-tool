#! /bin/bash

# Create keys directory if it does not exist
if [ ! -d keys ]; then
	echo "Creating keys directory"
	mkdir keys
fi

# Declare each service
# If more microservices are added in the future,
#   add them to this array
services=( "tmt" "resources" "permissions" )

# For each element in the services array
for i in "${services[@]}"
do
	# If the key pair does not exist (or only one exists) generate one
	if [ ! -f keys/$i.pem ] || [ ! -f keys/$i.pem ]; then
		echo "Generating new key pair for $i service"
		openssl genrsa -out keys/$i.pem 2048 &> /dev/null
		openssl rsa -in keys/$i.pem -pubout > keys/$i.pub
		continue
	fi

	# If the key pair already exists, do nothing

done

echo "Success!"
