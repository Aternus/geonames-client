#!/usr/bin/env bash

log() {
    GREEN='\033[0;32m'
    NC='\033[0m'
    echo -e "${GREEN}→ $*${NC}"
}

log "cleaning up..."
rm .phpunit.result.cache

log "setting up composer..."
rm -rf /usr/src/app/vendor
rm -rf /usr/src/app/composer.lock
composer install

log "php7 has started 🚀"
tail -f /dev/null
