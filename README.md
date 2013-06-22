Rocketeer [![Build Status](https://travis-ci.org/Anahkiasen/rocketeer.png?branch=master)](https://travis-ci.org/Anahkiasen/rocketeer)
=========

## Setup

Rocketeer provides a fast and easy to set-up way to deploy your Laravel projects.

**Rocketeer requires Laravel 4.1 as it uses the new _illuminate/remote_ component***.

### With Package Installer

Simply do this :

```
artisan package:install anahkiasen/rocketeer
artisan config:publish anahkiasen/rocketeer
```

### Manually

To use it, add the following to your `composer.json` file :

```json
"anahkiasen/rocketeer": "dev-master"
```

And this line to the `providers` array in your `app/config/app.php` file :

```php
'Rocketeer\RocketeerServiceProvider',
```

Then publish the config :

```
artisan config:publish anahkiasen/rocketeer
```

### Additional steps

Now before you go all crazy and deploy on everything you find you have to files to set up :

- The first one is the `app/config/remote.php` file, this will set up the **Remote** Laravel component, use it to provide the connection informations for the various servers you want to deploy to
- Then the `app/config/packages/anahkiasen/rocketeer/config.php` file, where you'll provide more concrete deployment-related informations – see the example file below for what it might look like if you're feeling a little lost

## Using Rocketeer

The available commands in Rocketeer are :

```
deploy
  deploy:setup                Set up the website for deployment
  deploy:deploy               Deploy the website.
  deploy:cleanup              Clean up old releases from the server
  deploy:current              Displays what the current release is
  deploy:rollback {release}   Rollback to a specific release
  deploy:rollback             Rollback to the previous release
  deploy:teardown             Removes the remote applications and existing caches
```

## Example config file

```php
<?php return array(

  // Git Repository
  //////////////////////////////////////////////////////////////////////

  'git' => array(

    // The SSH/HTTPS adress to your Git Repository
    'repository' => 'https://bitbucket.org/myUsername/facebook.git',

    // Its credentials
    'username'   => 'myUsername',
    'password'   => 'myPassword',

    // The branch to deploy
    'branch'     => 'master',
  ),

  // Remote server
  //////////////////////////////////////////////////////////////////////

  'remote' => array(

    // The root directory where your applications will be deployed
    'root_directory'   => '/home/www/',

    // The default name of the application to deploy
    'application_name' => 'facebook',

    // The number of releases to keep at all times
    'releases' => 4,
  ),

  // Tasks
  //////////////////////////////////////////////////////////////////////

  // Here you can define custom tasks to execute after certain actions
  'tasks' => array(

    // Tasks to execute before commands
    'before' => array(),

    // Tasks to execute after commands
    'after' => array(
      'deploy:deploy'  => array(
        'bower install',
        'php artisan basset:build'
      ),
    ),
  ),

);
```