#!/bin/bash

docker run -p 80:8080 \
-v "`pwd`/data:/var/www/html/data" \
eugrade
