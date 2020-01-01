# Introduction
This **master** branch is a starter project for [my blog](https://viblo.asia/p/viet-ung-dung-chat-trong-laravel-su-dung-private-va-presence-channel-OeVKBRLrKkW)

You can read through the blog to understand step by step to make a realtime chat app using Private and Presence channel in Laravel.

But if you just want to have a working example, switch to branch [**full-tutorial**](https://github.com/maitrungduc1410/private-realtime-chat/tree/full-tutorial) and follow its instruction
# Installation
## Pre-install
Check if you have `redis`, by running command: `redis-cli` (you'll need `redis` later, but it's mandatory so you need to check it as very first step)
## Install guide
Clone this project.

Run the following commands:
- composer install
- npm install
- cp .env.example .env
- php artisan key:generate

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
```

Now access your app at `localhost:8000` and you're ready to go

# Demo

You can view a real demo here: https://realtime-chat.jamesisme.com.

The demo source is in branch **full-app**