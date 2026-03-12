# geonames-client project

## Terminal

1. Always work from inside Docker for this repository.
2. Use the `php7` service as the default terminal environment.
3. Start and wait for readiness before running commands:
   `docker compose up -d php7 && until docker compose logs php7 | grep -q "php7 has started"; do sleep 1; done`
4. Run project commands via Docker, for example:
    - One-off command: `docker compose exec php7 sh -lc "<command>"`
    - Interactive shell: `docker compose exec php7 sh`
5. Only run `docker compose ...` commands on the host when needed to manage the
   container itself.

## Running PHP code

1. Run PHP inside Docker.
2. Use the `php7` service as the default PHP interpreter. Using a different
   service will affect the generated `vendor` folder.
