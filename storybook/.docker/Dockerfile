FROM composer:2.4 AS build

COPY . /autoupgrade

WORKDIR /autoupgrade
RUN composer install --no-dev --no-interaction --optimize-autoloader

WORKDIR /autoupgrade/storybook
RUN composer install --no-dev --no-interaction --optimize-autoloader


FROM node:20
#ENV IS_IN_DOCKER 1

RUN apt update \
    && apt install -y php8.2 php-dom \
    && rm -rf /var/lib/apt/lists/*

COPY --from=build /autoupgrade /autoupgrade

WORKDIR /autoupgrade/storybook

RUN npm install
