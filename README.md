Rocketeer
=========

Rocketeer provides a fast and easy to set-up way to deploy your Laravel projects.

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