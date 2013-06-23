Rocketeer [![Build Status](https://travis-ci.org/Anahkiasen/rocketeer.png?branch=master)](https://travis-ci.org/Anahkiasen/rocketeer)
=========

Rocketeer provides a fast and easy way to set-up and deploy your Laravel projects. **Rocketeer requires Laravel 4.1 as it uses the new _illuminate/remote_ component**.

## Using Rocketeer

- [Getting Started](docs/Getting-started.md)

## Using Rocketeer

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