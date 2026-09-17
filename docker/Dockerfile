FROM composer/composer:latest-bin AS composer

FROM php:8.3-cli-bookworm

# System packages

RUN apt-get update && apt-get install -y \
    unzip \
    zlib1g-dev \
    libzip-dev

# PHP extensions

RUN docker-php-ext-install \
    pdo_mysql \
    # For Psalm, to make use of JIT for a 20%+ performance boost. \
    opcache

# Composer

COPY --from=composer /composer /usr/bin/composer

# Code
WORKDIR /code