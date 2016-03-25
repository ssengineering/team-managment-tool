#!/bin/bash

cd /tmp
curl -O https://codeload.github.com/phpredis/phpredis/tar.gz/2.2.7
tar -zxvf 2.2.7
cd phpredis-2.2.7
phpize
./configure
make && make install
