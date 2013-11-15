### Changelog

### 0.9.0

- **Rocketeer now supports SVN**
- **Rocketeer now has a [Campfire plugin](https://github.com/Anahkiasen/rocketeer-campfire)**
- Add option to manually set remote variables when encountering problems
- Add keyphrase support

### 0.8.0

- **Rocketeer can now have specific configurations for stages and connections**
- **Better handling of multiple connections**
- **Added facade shortcuts `Rocketeer::execute(Task)` and `Rocketeer::on(connection[s], Task)` to execute commands on the remote servers**
- Added the `--list` flag on the `rollback` command to show a list of available releases and pick one to rollback to
- Added the `--on` flag to all commands to specify which connections the task should be executed on (ex. `production`, `staging,production`)
- Added `deploy:flush` to clear Rocketeer's cache of credentials

### 0.7.0

- **Rocketeer can now work outside of Laravel**
- **Better handling of SSH keys**
- Permissions are now entirely configurable
- Rocketeer now prompts for confirmation before executing the Teardown task
- Allow the use of patterns in shared folders
- Share `sessions` folder by default
- Rocketeer now prompts for binaries it can't find (composer, phpunit, etc)

### 0.6.5

- **Make Rocketeer prompt for both server and SCM credentials if they're not stored**
- **`artisan deploy` now deploys the project if the `--version` flat is not passed**
- Make Rocketeer forget invalid credentials provided by prompt
- Fix a bug where incorrect SCM urls would be generated

### 0.6.4

- Make the output of commands in realtime when `--verbose` instead of when the command is done
- Fix a bug where custom Task classes would be analyzed as string commands
- Fix Rocketeeer not taking into account custom paths to **app/**, **storage/**, **public/** etc.
- Reverse sluggification of application name

### 0.6.3

- Application name is now always sluggified as a security
- Fix a bug where the Check task would fail on pretend mode
- Fix a bug where invalid directory separators would get cached and used

### 0.6.2

- Make the Check task check for the remote presence of the configured SCM
- Fix Rocketeer not being able to use a `composer.phar` on the server

### 0.6.1

- Fix a bug where the configured user would not have the rights to set permissions

### 0.6.0

- **Add multistage strategy**
- **Add compatibility to Laravel 4.0**
- Migrations are now under a `--migrate` flag
- Split Git from the SCM implementation (**requires a config update**)
- Releases are now named as `YmdHis` instead of `time()`
- If the `scm.branch` option is empty, Rocketeer will now use the current Git branch
- Fix a delay where the `current` symlink would get updated before the complete end of the deploy
- Fix errors with Git and Composer not canceling deploy
- Fix some compatibility problems with Windows
- Fix a bug where string tasks would not be run in latest release folder
- Fix Apache username and group using `www-data` by default

### 0.5.0

- **Add a `deploy:update` task that updates the remote server without doing a new release**
- **Add a `deploy:test` to run the tests on the server**
- **Rocketeer can now prompt for Git credentials if you don't want to store them in the config**
- The `deploy:check` command now checks PHP extensions for the cache/database/session drivers you set
- Rocketeer now share logs by default between releases
- Add ability to specify an array of Tasks in Rocketeer::before|after
- Added a `$silent` flag to make a `Task::run` call silent no matter what
- Rocketeer now displays how long the task took

### 0.4.0

- **Add ability to share files and folders between releases**
- **Add ability to create custom tasks integrated in the CLI**
- **Add a `deploy:check` Task that checks if the server is ready to receive a Laravel app**
- Add `Task::listContents` and `Task::fileExists` helpers
- Add Task helper to run outstanding migrations
- Add `Rocketeer::add` method on the facade to register custom Tasks
- Fix `Task::runComposer` not taking into account a local `composer.phar`

### 0.3.2

- Fixed wrong tag used in `deploy:cleanup`

### 0.3.1

- Added `--pretend` flag on all commands to print out a list of the commands that would have been executed instead of running them

### 0.3.0

- Added `Task::runInFolder` to run tasks in a specific folder
- Added `Task::runForCurrentRelease` Task helper
- Fixed a bug where `Task::run` would only return the last line of the command's output
- Added `Task::runTests` methods to run the PHPUnit tests of the application
- Integrated `Task::runTests` in the `Deploy` task under the `--tests` flag ; failing tests will cancel deploy and rollback

### 0.2.0

- The core of Rocketeer's actions is now split into a system of Tasks for flexibility
- Added a `Rocketeer` facade to easily add tasks via `before` and `after` (see Tasks docs)

### 0.1.1

- Fixed a bug where the commands would try to connect to the remote hosts on construct
- Fixed `ReleasesManager::getPreviousRelease` returning the wrong release

### 0.1.0

- Add `deploy:teardown` to remove the application from remote servers
- Add support for the connections defined in the remote config file
- Add `deploy:rollback` and `deploy:current` commands
- Add `deploy:cleanup` command
- Add config file
- Add `deploy:setup` and `deploy:deploy` commands
