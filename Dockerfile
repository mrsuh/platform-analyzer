FROM php:7.4-cli

RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

RUN groupadd -r developer && useradd -m -g developer developer

RUN chown -R developer /app

USER developer

RUN sh /app/bin/build.sh

WORKDIR /app