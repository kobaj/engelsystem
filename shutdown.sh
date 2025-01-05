#!/bin/sh

compose() {
  if command -v docker-compose >/dev/null 2>&1; then
    # Use docker-compose if it's installed
    docker-compose "$@"
  else
    # Use docker compose if docker-compose is not installed
    docker compose "$@"
  fi
}

cd docker
compose down

if [ $? -eq 0 ]; then
  echo "The Engelsystem should now be turned off!"
fi

printf 'Delete the database volume? (y/n)? '
read answer

if [ "$answer" != "${answer#[Yy]}" ] ;then
  docker volume rm engelsystem_db

  if [ $? -eq 0 ]; then
    echo "Deleted docker volume engelsystem_db"
  fi
fi
