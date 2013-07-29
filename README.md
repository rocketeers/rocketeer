# Rocketeer

[![Build Status](https://travis-ci.org/Anahkiasen/rocketeer.png?branch=master)](https://travis-ci.org/Anahkiasen/rocketeer)
[![Latest Stable Version](https://poser.pugx.org/anahkiasen/rocketeer/v/stable.png)](https://packagist.org/packages/anahkiasen/rocketeer)
[![Total Downloads](https://poser.pugx.org/anahkiasen/rocketeer/downloads.png)](https://packagist.org/packages/anahkiasen/rocketeer)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/badges/quality-score.png?s=20d9a4be6695b7677c427eab73151c1a9d803044)](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/)
[![Code Coverage](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/badges/coverage.png?s=f6e022cbcf1a51f82b5d9e6fb30bd1643fc70e76)](https://scrutinizer-ci.com/g/Anahkiasen/rocketeer/)

Rocketeer provides a fast and easy way to set-up and deploy your Laravel projects. **Rocketeer requires Laravel 4.1 as it uses the new _illuminate/remote_ component**.
It can be used on Laravel 4.0 but requires a tiny-bit more setup, see the getting started guide for more informations.

## Using Rocketeer

I recommend you checkout this [Getting Started](https://github.com/Anahkiasen/rocketeer/wiki/Getting-started) guide before anything. It will get you quickly set up to use Rocketeer.

The available commands in Rocketeer are :

```
deploy
  deploy:check               Check if the server is ready to receive the application
  deploy:cleanup             Clean up old releases from the server
  deploy:current             Display what the current release is
  deploy:deploy              Deploy the website.
  deploy:rollback            Rollback to the previous release, or to a specific one
  deploy:rollback {release}  Rollback to a specific release
  deploy:setup               Set up the remote server for deployment
  deploy:teardown            Remove the remote applications and existing caches
  deploy:test                Run the tests on the server and displays the output
  deploy:update              Update the remote server without doing a new release
```

## Tasks

An important concept in Rocketeer is Tasks : most of the commands you see right above are using predefined Tasks underneath : **Rocketeer\Tasks\Setup**, **Rocketeer\Tasks\Deploy**, etc.
Now, the core of Rocketeer is you can hook into any of those Tasks to perform additional actions, for this you'll use the `before` and `after` arrays of Rocketeer's config file.

You can read more about Tasks and what you can do with them [in the wiki](https://github.com/Anahkiasen/rocketeer/wiki/Tasks).

## Why not Capistrano ?

That's a question that's been asked to me, why not simply use Capistrano ? I've used Capistrano in the past, it does everything you want it to do, that's a given.

But, it remains a Ruby package and one that's tightly coupled to Rails in some ways; Rocketeer makes it so that you don't have Ruby files hanging around your app. That way you configure it once and can use it wherever you want in the realm of Laravel, even outside of the deploy routine.
It's also meant to be a lot easier to comprehend, for first-time users or novices, Capistrano is a lot to take at once – Rocketeer aims to be as simple as possible by providing smart defaults and speeding up the time between installing it and first hitting `deploy`.

It's also more thought out for the PHP world – although you can configure Capistrano to run Composer and PHPUnit, that's not something it expects from the get go, while those tasks that are a part of every Laravel developer are integrated in Rocketeer's core deploy process.

## Table of contents

- **[Getting Started](https://github.com/Anahkiasen/rocketeer/wiki/Getting-started)**
- **[Tasks](https://github.com/Anahkiasen/rocketeer/wiki/Tasks)**
- **[Architecture](https://github.com/Anahkiasen/rocketeer/wiki/Architecture)**