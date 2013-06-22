Rocketeer
=========

## Setup

Rocketeer provides a fast and easy to set-up way to deploy your Laravel projects. **Rocketeer requires Laravel 4.1***.

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

And you're good to go. Simply edit the config file with the relevant informations.

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
```