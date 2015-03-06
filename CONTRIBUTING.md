# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via pull requests on [Github].
Please make all pull requests to the `develop` branch, not the `master` branch.

## Before posting an issue

- If a command is failing, post the full output you get when running the command, with the `--verbose` flag
- If everything looks normal in said log, provide a log with the `--pretend` flag

## Pull Requests

- **[Symfony Coding Standard]** - The easiest way to apply the conventions is to run `composer lint`
- **Add tests!** - Your patch won't be accepted if it doesn't have tests.
- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.
- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.
- **Create feature branches** - Don't ask us to pull from your master branch.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.
- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

## Building the PHAR

Once you cloned the repository in local, in order to test your changes, two solutions :

- You can execute `php bin/rocketeer` which is what gets compiled as the entry point of the PHAR. This will directly read the files of the repository.
- You can also compile the PHAR by doing `composer build` which will output to `bin/rocketeer.phar`. In order to compile it you'll need to have the `phar.readonly` set to **Off** in your `php.ini`.

## Running Tests

``` bash
$ composer test
```

**Happy coding**!

[Github]: https://github.com/rocketeers/rocketeer
[Symfony Coding Standard]: http://symfony.com/doc/current/contributing/code/standards.html
