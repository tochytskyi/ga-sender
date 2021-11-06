FROM php:8.0-cli-alpine

COPY . /usr/src/app
WORKDIR /usr/src/app

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN composer install

CMD [ "php", "./run.php" ]