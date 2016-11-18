# ApproximateDateTime

[![Build Status](https://travis-ci.org/wiese/ApproximateDateTime.svg?branch=master)](https://travis-ci.org/wiese/ApproximateDateTime)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wiese/ApproximateDateTime/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wiese/ApproximateDateTime/)


ApproximateDateTime is a library that helps determine possible dates and date
ranges given a more or less verbose list of criteria describing these dates.

## Installation

Install into your project with composer:

composer require wiese/approximate-datetime

## Development

There are not an awful lot of dependencies which can easily be 
satisfied by any number of alternative setup flows and infrastructures you may 
use at your discretion. Yet, this project uses [docker](https://docs.docker.com/) 
to ease creating a level field for development, and [composer](https://getcomposer.org/doc/)
for dependency management - technologies you should be familiar with before 
submitting changes.

### Checking out the code

    [me@localhost ~]$ git clone git@github.com:wiese/ApproximateDateTime.git
    [me@localhost ~]$ cd ApproximateDateTime

### Installing (dev) dependencies

    [me@localhost ApproximateDateTime]$ docker run --rm -v $(pwd):/app composer/composer install

### Setting up infrastructure

    [me@localhost ApproximateDateTime]$ docker build -t wiese/php7.1-approximatedatetime .

### Running tests

    [me@localhost ApproximateDateTime]$ docker run -v $(pwd):/app -w /app --rm wiese/php7.1-approximatedatetime ./vendor/bin/phpunit --color=always

#### With debugging

    [me@localhost ApproximateDateTime]$ docker run -v $(pwd):/app -w /app -e XDEBUG_CONFIG="remote_host=172.17.0.1" --rm wiese/php7.1-approximatedatetime ./vendor/bin/phpunit --color=always

### Checking codestyle

    [me@localhost ApproximateDateTime]$ docker run -v $(pwd):/app -w /app --rm wiese/php7.1-approximatedatetime ./vendor/bin/phpcs
