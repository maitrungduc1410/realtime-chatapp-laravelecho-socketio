FROM node:20-alpine

LABEL maintainer="Mai Trung Duc (maitrungduc1410@gmail.com)"

WORKDIR /app

COPY echo.json /app/echo.json
COPY laravel-echo-server.json /app/laravel-echo-server.json

RUN npm install -g pm2 laravel-echo-server

RUN chown -R node:node /app

EXPOSE 6001

USER node

CMD ["pm2-runtime", "echo.json"]