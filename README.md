# ApproximateDateTime

[![Build Status](https://travis-ci.org/wiese/ApproximateDateTime.svg?branch=master)](https://travis-ci.org/wiese/ApproximateDateTime)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wiese/ApproximateDateTime/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wiese/ApproximateDateTime/)
[![Code Coverage](https://scrutinizer-ci.com/g/wiese/ApproximateDateTime/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wiese/ApproximateDateTime/?branch=master)

ApproximateDateTime is a library that helps determine possible dates and date
ranges given a more or less verbose list of criteria describing these dates.

## Purpose & Usage

Occasionally dates are not precisely known, but can only be described 
approximately — resulting in multiple different possibilities of actual 
occurrence.
For example, just knowing the year will result in a *date period* that covers 365
days (or 366 in leap years), adding a month to the list of *clues* will narrow 
this down to the amount of days in the month given, and so on.
More enlightening results can be achieved by clue sets including a weekday, or 
the fact that the moment you describe happened in the afternoon.

Motivation for the implementation arose when trying to plot albums of pictures
on a timeline, while for many of them precise information was unavailable.

### Clues

A user can provide one or multiple *clues* in order to describe the date(s).
Clues can add information by adding to a *whitelist* of options, to a *blacklist*
of options to avoid, or information on an occurence *before* or *after* a certain
data point, for any given unit. All of this information can be provided 
simulatanously and will be prioritized accordingly.

By default, if no year is explicitly *whitelist*ed, the current year will be used.

### Clue precedence
1. Blacklist
2. Whitelist
  1. Plain whitelist data points — surely, multiple can be specified
  2. Before — if multiple specified, the earliest is taken, i.e. the biggest time span
  3. After — if multiple specified, the latest is taken, i.e. the biggest time span

## Installation

Install into your project with composer:

    [me@localhost your-project]$ composer require wiese/approximate-datetime

## Development

There are not an awful lot of dependencies, and they can easily be satisfied by 
any number of setup flows and infrastructures you may use at your discretion. 
Yet, this project uses [docker](https://docs.docker.com/) to ease creating a 
level field for development, and [composer](https://getcomposer.org/doc/)
for dependency management — technologies you should be familiar with prior to 
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

	# assuming your docker host IP is 172.17.0.1
    [me@localhost ApproximateDateTime]$ docker run -v $(pwd):/app -w /app -e XDEBUG_CONFIG="remote_host=172.17.0.1" --rm wiese/php7.1-approximatedatetime ./vendor/bin/phpunit --color=always

### Checking codestyle

    [me@localhost ApproximateDateTime]$ docker run -v $(pwd):/app -w /app --rm wiese/php7.1-approximatedatetime ./vendor/bin/phpcs

##Thanks

To Charlie, Great Sun ⊕
