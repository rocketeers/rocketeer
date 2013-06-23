Rocketeer [![Build Status](https://travis-ci.org/Anahkiasen/rocketeer.png?branch=master)](https://travis-ci.org/Anahkiasen/rocketeer) [![Latest Stable Version](https://poser.pugx.org/anahkiasen/rocketeer/v/stable.png)](https://packagist.org/packages/anahkiasen/rocketeer) [![Total Downloads](https://poser.pugx.org/anahkiasen/rocketeer/downloads.png)](https://packagist.org/packages/anahkiasen/rocketeer)
=========

Rocketeer provides a fast and easy way to set-up and deploy your Laravel projects. **Rocketeer requires Laravel 4.1 as it uses the new _illuminate/remote_ component**.

## Using Rocketeer

I recommend you checkout this [Getting Started](https://github.com/Anahkiasen/rocketeer/wiki/Getting-started) guide before anything. It will get you quickly set up to use Rocketeer.

The available commands in Rocketeer are :

```
deploy
	deploy:setup               Set up the website for deployment
	deploy:deploy              Deploy the website.
	deploy:cleanup             Clean up old releases from the server
	deploy:current             Displays what the current release is
	deploy:rollback {release}  Rollback to a specific release
	deploy:rollback            Rollback to the previous release
	deploy:teardown            Removes the remote applications and existing caches
```

## Tasks

An important concept in Rocketeer is Tasks : most of the commands you see right above are using predefined Tasks underneath : **Rocketeer\Tasks\Setup**, **Rocketeer\Tasks\Deploy**, etc.
Now, the core of Rocketeer is you can hook into any of those Tasks to peform additional actions, for this you'll use the `before` and `after` arrays of Rocketeer's config file.

You can read more about Tasks and what you can do with them [in the wiki](https://github.com/Anahkiasen/rocketeer/wiki/Tasks).
