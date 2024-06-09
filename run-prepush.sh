#!/usr/bin/env bash

docker compose up -d php7

while ! docker compose logs php7 | grep -m 1 "php7 has started"; do
    echo "Waiting for php7 to start..."
    sleep 1
done

docker compose exec php7 composer validate --strict

docker compose exec php7 composer aternus:style-fix

docker compose exec php7 composer aternus:style-check

docker compose exec php7 composer aternus:test
