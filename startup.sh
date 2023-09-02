#!/bin/sh

# Does Docker exist locally?
command -v docker > /dev/null
if [ $? -ne 0 ]; then
  echo "You need to install docker on this machine before you can install Engelsystem/FC."
fi

# Start-up all the scripts.
cd docker
docker compose build
docker compose up -d --wait
docker compose exec es_server bin/migrate

echo "The Engelsystem should now be running locally at http://localhost:5080. Enjoy!"