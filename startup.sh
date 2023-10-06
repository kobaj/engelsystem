#!/bin/sh

# Does Docker exist locally?
command -v docker > /dev/null
if [ $? -ne 0 ]; then
  echo "You need to install docker on this machine before you can install Engelsystem/FC."
fi

# Kill and remove old programs, if they are running.
docker kill engelsystem-es_server-1 engelsystem-es_database-1 2&>1 > /dev/null
docker rm engelsystem-es_server-1 engelsystem-es_database-1  2&>1 > /dev/null

# Start-up all the scripts.
cd docker
docker compose build && \
docker compose up -d --wait && \
sleep 5 && \
docker compose exec es_server bin/migrate

if [ $? -eq 0 ]; then
  echo "The Engelsystem should now be running locally at http://localhost:5080. Enjoy!"
fi