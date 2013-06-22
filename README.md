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

For now, Rocketeer comes with two main commands :

- `artisan deploy:setup` will create the relevant folders on your remote connection (ie, `current` and `releases` _ala_ Capistrano).
- `artisan deploy:deploy` will deploy the current version of the repository as a release, make it the current one, and execute whatever tasks you configured Rocketeer to do afterwards.