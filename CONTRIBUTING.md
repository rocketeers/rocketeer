# How to make a pull request

Please make all pull requests to the `develop` branch, not the `master` branch.
Coding standards are PSR2-tabs.

# Before posting an issue

- If a command is failing, post the full output you get when running the command, with the `--verbose` flag
- If everything looks normal in said log, provide a log with the `--pretend` flag

# Building the PHAR

Once you cloned the repository in local, in order to test your changes, two solutions :

- You can execute `php bin/rocketeer` which is what gets compiled as the entry point of the PHAR. This will directly read the files of the repository.
- You can also compile the PHAR by doing `php bin/compile` which will output to `bin/rocketeer.phar`. In order to compile it you'll need to have the `phar.readonly` set to **On** in your `php.ini`.