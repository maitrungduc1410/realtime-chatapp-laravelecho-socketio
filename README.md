# Introduction
Realtime chat app with Laravel, VueJS, Laravel Echo, SocketIO, Redis including Queue, Schedule Task, Laravel Horizon

## Overview
This app contains following features:
- Multiple chat rooms
- Realtime chat with Private and Presence Channel
- Each room contains Share area (everyone can chat) or Private chat with a specific user in the room
- Notification to user on receiving message (both on side bar and on Topbar of browser)
- Bot scheduled message
- Message reaction like Facebook Messenger (Realtime notify others on reaction)
## Screenshots

![Realtime chat app](./public/intro_images/overview.png "App overview")

<div class="tip" markdown="1">
<img src="./public/intro_images/reaction.png" width="200" alt="Message Reaction">
<img src="./public/intro_images/typing.png" width="200" alt="Typing">
<img src="./public/intro_images/seen.png" width="200" alt="Seen message">
</div>

![Horizon](./public/intro_images/horizon.png "Horizon")
# Installation
## Prerequisite
Check if you have `redis` installed, by running command: `redis-cli`
## Install guide
Clone this project.

Run the following commands:
```
composer install
npm install
cp .env.example .env
php artisan key:generate
npm install -g laravel-echo-server
```

Then setup your database infor in `.env` to match yours

Now, migrate and seed the database:
```
php artisan migrate --seed
```
## Run the app
To run the app, run the following commands, each command in **a separate terminal**:
```
php artisan serve
npm run watch
laravel-echo-server start
php artisan queue:work
```

Now access your app at `localhost:8000`, register an account and try, open another browser tab with another account to test realtime chat.

# Demo
You can view a real demo here: https://realtime-chat.jamesisme.com

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

REDIS_HOST=redis
REDIS_PASSWORD=redis_pass
REDIS_PORT=6379

...

LARAVEL_ECHO_SERVER_REDIS_HOST=redis
LARAVEL_ECHO_SERVER_REDIS_PORT=6379
LARAVEL_ECHO_SERVER_REDIS_PASSWORD=redis_pass
LARAVEL_ECHO_SERVER_AUTH_HOST=http://webserver:80
LARAVEL_ECHO_SERVER_DEBUG=false

...
```

Next, Run the following commands:
```
docker run --rm -v $(pwd):/app -w /app composer install --ignore-platform-reqs --no-autoloader --no-dev --no-interaction --no-progress --no-suggest --no-scripts --prefer-dist
docker run --rm -v $(pwd):/app -w /app composer dump-autoload --classmap-authoritative --no-dev --optimize
docker run --rm -v $(pwd):/app -w /app node npm install --production
docker run --rm -v $(pwd):/app -w /app node npm run prod
```
The commands above are equivalent with: 
- **composer install <...other options>**
- **composer dump-autoload <...other options>**
- **npm install --production**
- **npm run prod**

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

If you want to change to another port instead of `4000`. Change `MIX_APP_PORT` and `MIX_FRONTEND_PORT` to the same one you want. Then run following command to rebuild frontend:
```
docker run --rm -v $(pwd):/app -w /app node npm run prod
```

## Note
Every command with **Laravel** you need to run it like follow:
```
docker-compose exec app php artisan <same like normal>
```

Every command with **composer** need to run like follow:
```
docker run --rm -v $(pwd):/app -w /app composer <same like normal>
```

Every command with **npm** need to run like follow:
```
docker run --rm -v $(pwd):/app -w /app node npm run dev/watch/prod
```

## Deploy to production
When deploying to production, normally you'll run you app with HTTPS (port 443), then your frontend will be served under HTTPS too. So changing the `MIX_FRONTEND_PORT` in `.env` to 443.

Other settings are same