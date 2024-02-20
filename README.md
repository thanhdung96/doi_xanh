# Doi Xanh Media interview project (Backend part)

## Requirements

- Symfony (binary, latest)
- PHP >= 8
- Mysql >= 5.6
- Node >= 21

## Setup

- Clone the project
- Install requried bundles by `composer install`
- Create a copy of doctrine.yaml (originated from config/packages) and put the copy in config/packages/dev
- Uncomment the dbname, host, user, password parametres in doctrine.dbal.connections.default (in the doctrine.yaml copy) and fill in the required information
- Do migrations by `php bin/console doctrine:migration:migrate`
- Start the server by `symfony server:start --no-tls`

## Note

- The backend is configured to be exposed to CORS from frontend project for the sake of simplicity
- Base setup exposes port 8000
