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