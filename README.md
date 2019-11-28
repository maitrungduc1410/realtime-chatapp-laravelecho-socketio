# Installation
## Pre-install
Check if you have `redis`, by running command: `redis-cli`
## Install guide
Clone this project.

Run the following commands:
- composer install
- npm install
- cp .env.example .env
- php artisan key:generate
- npm install -g laravel-echo-server

Then setup your database infor in `.env` to match yours

Now, migrate and seed the database:
```
php artisan migrate --seed
```
## Run the app
To the run app, run the following commands, each command in a separate terminal:
```
php artisan serve
npm run watch
laravel-echo-server start
php artisan queue:work
```

Now access your app at `localhost:8000`, register an account and try, open another browser tab with another account to test realtime chat.

# Running with docker
## Pre-install
Make sure you installed `docker` and `docker-compose`
## Guide
First create `.env` file
```
cp .env.example .env
```
Edit `.env` update the following parts:
```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laraveluser
DB_PASSWORD=laraveluserpass

...

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

...

REDIS_HOST=redis
REDIS_PASSWORD=redis_pass
REDIS_PORT=6379

...

LARAVEL_ECHO_SERVER_REDIS_HOST=redis
LARAVEL_ECHO_SERVER_REDIS_PORT=6379
LARAVEL_ECHO_SERVER_REDIS_PASSWORD=redis_pass
LARAVEL_ECHO_SERVER_AUTH_HOST=http://webserver:80
LARAVEL_ECHO_SERVER_DEBUG=false

APP_PORT=4000
MIX_FRONTEND_PORT=4000
```

Run the following command:
```

docker run --rm -v $(pwd):/app -w /app composer install --ignore-platform-reqs --no-autoloader --no-dev --no-interaction --no-progress --no-suggest --no-scripts --prefer-dist
docker run --rm -v $(pwd):/app -w /app composer dump-autoload --classmap-authoritative --no-dev --optimize
docker run --rm -v $(pwd):/app -w /app node npm install --production
docker run --rm -v $(pwd):/app -w /app node npm run prod
```

## Bootstrap application

Run the following command to start application:
```
docker-compose up -d --build
```
Now we need to generate project's key migrate and seed database. Run command:
```
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

Now access the app at: `localhost:4000`

If you want to change to another port instead of `4000`. Change `MIX_APP_PORT` and `MIX_FRONTEND_PORT` to the same one you want. Then run following commands:
```
docker run --rm -v $(pwd):/app -w /app node npm run prod
docker-compose down
docker-compose up -d
```

## Deploy to production
When deploying to production, normally you'll run you app with HTTPS (port 443), then your frontend will be served under HTTPS too. So changing the `MIX_FRONTEND_PORT` in `.env` to 443. If you choose another so change `MIX_FRONTEND_PORT` to match yours