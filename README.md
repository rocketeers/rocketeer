# Rocketeer

[![Build Status](https://travis-ci.org/Anahkiasen/rocketeer.png?branch=master)](https://travis-ci.org/Anahkiasen/rocketeer)
[![Latest Stable Version](https://poser.pugx.org/anahkiasen/rocketeer/v/stable.png)](https://packagist.org/packages/anahkiasen/rocketeer)
[![Total Downloads](https://poser.pugx.org/anahkiasen/rocketeer/downloads.png)](https://packagist.org/packages/anahkiasen/rocketeer)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/badges/quality-score.png?s=20d9a4be6695b7677c427eab73151c1a9d803044)](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/)
[![Code Coverage](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/badges/coverage.png?s=f6e022cbcf1a51f82b5d9e6fb30bd1643fc70e76)](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/)
[![Support via Gittip](http://img.shields.io/gittip/Anahkiasen.svg)](https://www.gittip.com/Anahkiasen/)

**Rocketeer** is a task runner and deployment package for the PHP world. It is inspired by the [Laravel Framework](http://laravel.com/) philosophy and thus aims to be fast, elegant, and more importantly easy to use.

## Installation

The easiest way is to get the latest compiled version [from the website](http://rocketeer.autopergamene.eu/versions/rocketeer.phar), put it at the root of the project you want to deploy, and hit `php rocketeer.phar ignite`. You'll get asked a series of questions that should get you up and running in no time.

Rocketeer also integrates nicely with the Laravel framework, for that refer to the [Getting Started](https://github.com/Anahkiasen/rocketeer/wiki/Getting-started) pages of the documentation.

## Usage

The available commands in Rocketeer are :

```
$ php rocketeer
  check      Check if the server is ready to receive the application
  cleanup    Clean up old releases from the server
  current    Display what the current release is
  deploy     Deploy the website.
  flush      Flushes Rocketeer's cache of credentials
  help       Displays help for a command
  ignite     Creates Rocketeer's configuration
  list       Lists commands
  rollback   Rollback to the previous release, or to a specific one
  setup      Set up the remote server for deployment
  teardown   Remove the remote applications and existing caches
  test       Run the tests on the server and displays the output
  update     Update the remote server without doing a new release.
```

## Testing

``` bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/anahkiasen/rocketeer/blob/master/CONTRIBUTING.md) for details.

## Credits

- [Anahkiasen](https://github.com/Anahkiasen)
- [All Contributors](https://github.com/anahkiasen/rocketeer/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/anahkiasen/rocketeer/blob/master/LICENSE) for more information.

-----

## Available plugins

- [Campfire](https://github.com/Anahkiasen/rocketeer-campfire)
- [Slack](https://github.com/Anahkiasen/rocketeer-slack)

## Why not Capistrano ?

That's a question that's been asked to me, why not simply use Capistrano ? I've used Capistrano in the past, it does everything you want it to do, that's a given.

But, it remains a Ruby package and one that's tightly coupled to Rails in some ways; Rocketeer makes it so that you don't have Ruby files hanging around your app. That way you configure it once and can use it wherever you want in the realm of your application, even outside of the deploy routine.
It's also meant to be a lot easier to comprehend, for first-time users or novices, Capistrano is a lot to take at once – Rocketeer aims to be as simple as possible by providing smart defaults and speeding up the time between installing it and first hitting `deploy`.

It's also more thought out for the PHP world – although you can configure Capistrano to run Composer and PHPUnit, that's not something it expects from the get go, while those tasks that are a part of every PHP developer are integrated in Rocketeer's core deploy process.

## Table of contents

### Getting started

- **[What's Rocketeer](https://github.com/Anahkiasen/rocketeer/wiki/Whats-Rocketeer)**
- **[Getting Started](https://github.com/Anahkiasen/rocketeer/wiki/Getting-started)**

### Core concepts

- **[Tasks](https://github.com/Anahkiasen/rocketeer/wiki/Tasks)**
- **[Events](https://github.com/Anahkiasen/rocketeer/wiki/Events)**
- **[Connections and stages](https://github.com/Anahkiasen/rocketeer/wiki/Connections-Stages)**
- **[Plugins](https://github.com/Anahkiasen/rocketeer/wiki/Plugins)**

### Going further

- **[Architecture](https://github.com/Anahkiasen/rocketeer/wiki/Architecture)**
- **[Troubleshooting](https://github.com/Anahkiasen/rocketeer/wiki/Troubleshooting)**
