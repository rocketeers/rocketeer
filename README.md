# Rocketeer

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/rocketeers/rocketeer?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](http://img.shields.io/travis/rocketeers/rocketeer.svg?style=flat-square)](https://travis-ci.org/rocketeers/rocketeer)
[![Latest Stable Version](http://img.shields.io/packagist/v/anahkiasen/rocketeer.svg?style=flat-square)](https://packagist.org/packages/anahkiasen/rocketeer)
[![Total Downloads](http://img.shields.io/packagist/dt/anahkiasen/rocketeer.svg?style=flat-square)](https://packagist.org/packages/anahkiasen/rocketeer)
[![Scrutinizer Quality Score](http://img.shields.io/scrutinizer/g/rocketeers/rocketeer.svg?style=flat-square)](https://scrutinizer-ci.com/g/rocketeers/rocketeer/)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/rocketeers/rocketeer.svg?style=flat-square)](https://scrutinizer-ci.com/g/rocketeers/rocketeer/)
[![Dependency Status](https://www.versioneye.com/user/projects/53f1c65f13bb0677b1000744/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/53f1c65f13bb0677b1000744)
[![Support via Gittip](http://img.shields.io/gittip/Anahkiasen.svg?style=flat-square)](https://www.gittip.com/Anahkiasen/)

**Rocketeer** is a modern PHP task runner and deployment package. It is inspired by the [Laravel Framework](http://laravel.com/) philosophy and thus aims to be fast, elegant, and more importantly easy to use.

Like the latter, emphasis is put on smart defaults and modern development. While it is coded in PHP, it can deploy any project from small HTML/CSS websites to large Rails applications.

## Main features

- **Versatile**, support for multiple connections, multiserver connections, multiple stages per server, etc.
- **Fast**, queue tasks and run them in parallel across all your servers and stages
- **Modulable**, not only can you add custom tasks and components, every core part of Rocketeer can be hot swapped, extended, hacked to bits, etc.
- **Preconfigured**, tired of defining the same routines again and again ? Rocketeer is made for modern development and comes with smart defaults and built-in tasks such as installing your application's dependencies
- **Powerful**, releases management, server checks, rollbacks, etc. Every feature you'd expect from a deployment tool is there

## Installation

The fastest way is to grab the binary:

```bash
$ wget http://rocketeer.autopergamene.eu/versions/rocketeer.phar
$ chmod +x rocketeer.phar
$ mv rocketeer.phar /usr/local/bin/rocketeer
```

More ways to setup Rocketeer can be found in the [official documentation](http://rocketeer.autopergamene.eu/#/docs/docs/I-Introduction/Getting-started).

## Usage

The available commands in Rocketeer are :

```
$ php rocketeer
  check        Check if the server is ready to receive the application
  cleanup      Clean up old releases from the server
  current      Display what the current release is
  deploy       Deploys the website
  flush        Flushes Rocketeer's cache of credentials
  help         Displays help for a command
  ignite       Creates Rocketeer's configuration
  list         Lists commands
  rollback     Rollback to the previous release, or to a specific one
  setup        Set up the remote server for deployment
  strategies   Lists the available options for each strategy
  teardown     Remove the remote applications and existing caches
  test         Run the tests on the server and displays the output
  update       Update the remote server without doing a new release
```

Documentation can be [found here](http://rocketeer.autopergamene.eu/#/docs/rocketeer/README)

## Testing

``` bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/rocketeers/rocketeer/blob/master/CONTRIBUTING.md) for details.

## Credits

- [Anahkiasen](https://github.com/Anahkiasen)
- [All Contributors](https://github.com/rocketeers/rocketeer/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/rocketeers/rocketeer/blob/master/LICENSE) for more information.

-----

## Available plugins and integrations

- [Campfire](https://github.com/rocketeers/rocketeer-campfire)
- [Slack](https://github.com/rocketeers/rocketeer-slack)
- [HipChat](https://github.com/hannesvdvreken/rocketeer-hipchat)
- [Wordpress](https://github.com/mykebates/wp-rocketeer)
- [Bugsnag](https://github.com/bramdevries/rocketeer-bugsnag)

## Why not Capistrano?

That's a question that's been asked to me, why not simply use Capistrano? I've used Capistrano in the past, it does everything you want it to do, that's a given.

But, it remains a Ruby package and one that's tightly coupled to Rails in some ways; Rocketeer makes it so that you don't have Ruby files hanging around your app. That way you configure it once and can use it wherever you want in the realm of your application, even outside of the deploy routine.
It's also meant to be a lot easier to comprehend, for first-time users or novices, Capistrano is a lot to take in at once – Rocketeer aims to be as simple as possible by providing smart defaults and speeding up the time between installing it and first hitting `deploy`.

It's also more thought out for the PHP world – although you can configure Capistrano to run Composer and PHPUnit, that's not something it expects from the get go, while those tasks that are a part of every PHP developer are integrated in Rocketeer's core deploy process.
