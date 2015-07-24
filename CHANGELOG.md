# CHANGELOG

Rocketeer follows the [Semantic Versioning 2.0](http://semver.org/spec/v2.0.0.html) spec.

## [2.2.0] - 2015-04-04

### Added
- Added `RemoteHandler::disconnect` to purge cached connections
- Added `LocalCloneStrategy` deploy strategy
- Added `sudo` and `sudoed` option to execute some commands as a sudo user (behaves the same way shell/shelled does)

### Fixed
- Fixed an issue on 32bit systems where releases would max out the integer limit
- Fixed an issue where commands would not return the proper status code
- Fixed HHVM support

## [2.1.3] - 2015-03-23

### Fixed
- Fixed an issue with multiservers deployments

## [2.1.2] - 2015-03-10

### Fixed
- Fixed an issue prevent using a custom command clas
- Fixed commands run in folders not working with LocalConnection
- Fixed port and key not being passed to rsync

## [2.1.1] - 2015-03-01

### Fixed
- Fixed version number

## [2.1.0] - 2015-03-08

### Added
- Added ability to have contextual server configuration by adding a `config` array to a server's configuration

### Fixed
- Fixed an issue when igniting with a connection other than "production"
- Fixed an issue with Polyglot strategies not properly propagating their results to parent tasks
- Fixed an issue with the `force` flag missing for Artisan commands

## [2.0.6] - 2015-02-12

### Fixed
- Fixed an issue when updating an application with SVN
- Fixed an issue where hidden inputs wouldn't work on Windows using the PHAR
- Fixed incorrect version in generated PHAR

## [2.0.5] - 2015-02-11

### Fixed
- Fixed an issue where trying to use an invalid connection would just fallback silently to the default one
- Fixed an issue where polyglot strategies would keep running after one of their child failed
- Fixed an issue where the logs filename would get recomputed every call
- Fixed a missing dependency in generated PHARs
- Fixed paths defined in `paths.php` sometimes being ignored
- Fixed an issue where jobs would use the wrong server on multiserver connections
- Fixed an issue with symlinks on non-GNU/Linux OSes

## [2.0.4] - 2014-12-08

### Changed
- Better way to get SVN revision (doesn't require auth anymore)
- Releases are now also pruned from the `states.json` file when cleaning up
- Running with debug verbosity (`-vvv`) now outputs *all* commands being executed (some were hidden)

### Fixed
- Fixed loading of strategies in `.rocketeer/strategies`
- Fixed polyglot strategies considered failures if non executable
- Fixed for alternative `which` responses being considered paths
- Fixed incorrect replacing of slashs and backslashes outside of paths
- Fixed ability to pass an unexisting release to the Rollback task
- Fixed a bug where using SVN would cause Rocketeer to execute empty commands during cloning
- Fixed an issue where binaries paths would be shared between connections

## [2.0.3] - 2014-11-12

### Fixed
- Fixed symlink overwrite issue

## [2.0.2] - 2014-11-07

### Added
- Added back the ability to define custom paths in `paths.php` and reference them via `{key}`

### Changed
- Better way to operate around symlinks
- The `passphrase` credential is now asked secretely

### Fixed
- Fixed a bug where credentials were passed twice to SVN checkout (once in the URL, once via options)
- Fixed custom tasks not being properly bound to container
- Fixed a bug in the `plugin:publish` command
- Fixed a bug where plugins installed globally wouldn't be found by Rocketeer
- Fixed the `no-clear` option name not being recognized during `update`
- Fixed some issues with SVN credentials
- Fixed a bug where multiserver connections would share some credentials instead of using their own

## [2.0.1] - 2014-10-25

### Added
- Added ability to pass the branch/tag/commit to deploy via `--branch` or `-B`
- Added ability to declare tasks fluently via `Rocketeer::task('name')->description('description')->does(string|array|Closure)`
- Added `no-cache` option to `rocketeer update` to not clear the cache on update

### Fixed
- Fixed a bug where ignition wouldn't work from the PHAR archive
- Fixed a missing dependency registered as a dev-dependency
- Fixed strictness of `which` system that would fail on some binaries
- Fixed a bug where events would never be registered when using only `hooks.php` with one connection/stage

## [2.0.0] - 2014-09-17

### Added
- Added ability to run tasks in parallel via the `--parallel` flag (or `-P`)
- Added ability to have multiple servers for one connection, just define them in a `servers` array in your connection, each entry being an usual connection credentials array
- Added support for defining contextual configurations in files (`.rocketeer/connections/{connection}/scm.php`, same for stages)
- Core tasks (Deploy, Check, Test, Migrate) now use a module system called Strategies
- Added a `Sync` DeployStrategy in addition to `Clone` and `Copy` that uses rsync to create a new release
- Added static helper `Rocketeer::getDetectedStage` to get the stage Rocketeer think's he's in on the server (for environment mappings)
- Added support for checking of HHVM extensions
- Added `Task::upload(file, destination)` to upload files to remote, destination can be null and the basename of the file would then be used

### Changed
- Output now lists which tasks were fired by which task/events, how long they should take, in a tree-like format that clarifies tasks and subtasks
- For breaking changes, see the [Upgrade Path](http://rocketeer.autopergamene.eu/#/docs/III-Further/Upgrade-Path)

### Fixed
- Fixed the `Copy` strategy
- Fixed a bug where registered events in `hooks` would make the notifier plugins fail
- Fixed a bug where `rocketeer current` would fail to find the related task
- Fixed a bug where Artisan wouldn't be found even if at the default location
- Fixed a bug where ignition would fail when the default connection isn't `production`
- Fixed a bug where logs would be misplaced
- Fixed a bug where tasks and events weren't properly loaded in Laravel
- Fixed a bug where releases would be asked to the server at each command, slowing down deployments
- Fixed a bug where events wouldn't be properly rebooted when using connections other than the default ones
- Fixed a bug where Rocketeer would ask for credentials again after switching connection

## [1.2.2] - 2014-06-05

### Added
- Added ability to disable composer completely
- Added support for ssh-agent for secure connections

### Changed
- The Notifier plugin module now has a hook for before and after deployment

### Fixed
- Fixed a bug that prevented the `--seed` option from working
- Fixed a bug when getting the user's home folder on Windows
- Fixed a bug where Composer-related tasks would be run even without a `composer.json` is found
- Fixed some compatibility issue with Laravel 4.2

## [1.2.1] - 2014-03-31

### Changed
- Split `remote/application_name` in `config/application_name` and `remote/app_directory` to allow contextual application folder name
- The `composer self-update` command is now commented out by default

### Fixed
- Fixed a bug where `composer install` wouldn't return the proper status code and would cancel deployment
- Fixed a bug where empty arrays wouldn't override defaults in the configuration
- Fixed path to home folder not being properly found in Windows environment

## [1.2.0] - 2014-03-08

### Added
- Added various SSH task-running helpers such as `Rocketeer::task(taskname, task)`
- Rocketeer now has a `copy` strategy that copies the previous release instead of cloning a new one on deploy
- Composer execution is now configurable via a callback
- Added an option to disable recursive git clone (submodules)
- Releases are now sorted by date when printed out in `rollback` and `current`

### Fixed
- Fixed a bug when running Setup would cancel the `--stage` option
- Fixed a bug where contextual options weren't properly merged with default ones

## [1.1.2] - 2014-02-12

### Added
- Added a `Rocketeer\Plugins\Notifier` class to easily add third-party deployment notification plugins

### Fixed
- Fixed a bug where the custom tasks/events file/folders might not exist

## [1.1.1] - 2014-02-08

### Fixed
- Fixed a bug where the `before` event if halting wouldn't cancel the Task firing
- Fixed a bug where some calls to the facade would crash in `tasks.php`

## [1.1.0] - 2014-02-08

### Added
- Events can now cancel the queue by returning false or returning `$task->halt(error)`
- Rocketeer now logs its output and commands
- Releases are now marked as completed or halted to avoid rollback to releases that errored
- Rocketeer will now automatically load `.rocketeer/tasks.php`/`.rocketeer/events.php` _or_ the contents of `.rocketeer/tasks`/`.rocketeer/events` if they're folders
- Hash is now computed with the actual configuration instead of the modification times to avoid unecessary reflushes
- Check task now uses the PHP version required in your `composer.json` file if the latter exists

### Fixed
- Use the server's time to timestamp releases instead of the local time
- Fixed a bug where incorrect current release would be returned for multi-servers setups

## [1.0.0] - 2014-01-13

### Added
- Rocketeer is now available as a [standalone PHAR](http://rocketeer.autopergamene.eu/versions/rocketeer.phar)
- Revamped plugin system
- Rocketeer hooks now use `illuminate/event` system, and can fire events during tasks (instead of just before and after)
- Permissions setting is now set in a callback to allow custom permissions routines
- Rocketeer now looks into `~/.ssh` by default for keys instead of asking
- Added the `--clean-all` flag to the `Cleanup` task to prune all but the latest release
- Deployments file is now cleared when the config files are changed
- Added an option to disable shallow clone as it caused some problems on some servers

### Deprecated
- Configuration is now split in multiple files, you'll need to redeploy the configuration files

### Fixed
- Fixed a bug where `CurrentRelease` wouldn't show any release with an empty/fresh deployments file
- Fixed some multiconnections related bugs
- Fixed some minor behaviors that were causing `--pretend` and/or `--verbose` to not output SCM commands

## [0.9.0] - 2013-11-15

### Added
- Rocketeer now supports SVN
- Rocketeer now has a [Campfire plugin](https://github.com/Anahkiasen/rocketeer-campfire)
- Added option to manually set remote variables when encountering problems
- Added keyphrase support

## [0.8.0] - 2013-10-19

### Added
- Rocketeer can now have specific configurations for stages and connections
- Better handling of multiple connections
- Added facade shortcuts `Rocketeer::execute(Task)` and `Rocketeer::on(connection[s], Task)` to execute commands on the remote servers
- Added the `--list` flag on the `rollback` command to show a list of available releases and pick one to rollback to
- Added the `--on` flag to all commands to specify which connections the task should be executed on (ex. `production`, `staging,production`)
- Added `deploy:flush` to clear Rocketeer's cache of credentials

## [0.7.0] - 2013-08-16

### Added
- Rocketeer can now work outside of Laravel
- Better handling of SSH keys
- Permissions are now entirely configurable
- Rocketeer now prompts for confirmation before executing the Teardown task
- Allow the use of patterns in shared folders
- Rocketeer now prompts for binaries it can't find (composer, phpunit, etc)

### Changed
- Share `sessions` folder by default

## [0.6.5] - 2013-07-29

### Added
- Make Rocketeer prompt for both server and SCM credentials if they're not stored
- `artisan deploy` now deploys the project if the `--version` flat is not passed
- Make Rocketeer forget invalid credentials provided by prompt

### Fixed
- Fix a bug where incorrect SCM urls would be generated

## [0.6.4] - 2013-07-16

### Added
- Make the output of commands in realtime when `--verbose` instead of when the command is done

### Changed
- Reverse sluggification of application name

### Fixed
- Fix a bug where custom Task classes would be analyzed as string commands
- Fix Rocketeeer not taking into account custom paths to **app/**, **storage/**, **public/** etc.

## [0.6.3] - 2013-07-11

### Changed
- Application name is now always sluggified as a security

### Fixed
- Fix a bug where the Check task would fail on pretend mode
- Fix a bug where invalid directory separators would get cached and used

## [0.6.2] - 2013-07-11

### Added
- Make the Check task check for the remote presence of the configured SCM

### Fixed
- Fix Rocketeer not being able to use a `composer.phar` on the server

## [0.6.1] - 2013-07-10

### Fixed
- Fixed a bug where the configured user would not have the rights to set permissions

## [0.6.0] - 2013-07-06

### Added
- Added multistage strategy
- Added compatibility to Laravel 4.0
- Split Git from the SCM implementation (**requires a config update**)

### Changed
- Migrations are now under a `--migrate` flag
- Releases are now named as `YmdHis` instead of `time()`
- If the `scm.branch` option is empty, Rocketeer will now use the current Git branch

### Fixed
- Fixed a delay where the `current` symlink would get updated before the complete end of the deploy
- Fixed errors with Git and Composer not canceling deploy
- Fixed some compatibility problems with Windows
- Fixed a bug where string tasks would not be run in latest release folder
- Fixed Apache username and group using `www-data` by default

## [0.5.0] - 2013-07-01

### Added
- Added a `deploy:update` task that updates the remote server without doing a new release
- Added a `deploy:test` to run the tests on the server
- Rocketeer can now prompt for Git credentials if you don't want to store them in the config
- The `deploy:check` command now checks PHP extensions for the cache/database/session drivers you set
- Rocketeer now share logs by default between releases
- Added ability to specify an array of Tasks in Rocketeer::before|after
- Added a `$silent` flag to make a `Task::run` call silent no matter what
- Rocketeer now displays how long the task took

## [0.4.0] - 2013-06-26

### Added
- Added ability to share files and folders between releases
- Added ability to create custom tasks integrated in the CLI
- Added a `deploy:check` Task that checks if the server is ready to receive a Laravel app
- Added `Task::listContents` and `Task::fileExists` helpers
- Added Task helper to run outstanding migrations
- Added `Rocketeer::add` method on the facade to register custom Tasks

### Fixed
- Fixed `Task::runComposer` not taking into account a local `composer.phar`

## [0.3.2] - 2013-06-25

### Fixed
- Fixed wrong tag used in `deploy:cleanup`

## [0.3.1] - 2013-06-24

### Added
- Added `--pretend` flag on all commands to print out a list of the commands that would have been executed instead of running them

## [0.3.0] - 2013-06-24

### Added
- Added `Task::runInFolder` to run tasks in a specific folder
- Added `Task::runForCurrentRelease` Task helper
- Added `Task::runTests` methods to run the PHPUnit tests of the application
- Integrated `Task::runTests` in the `Deploy` task under the `--tests` flag ; failing tests will cancel deploy and rollback

### Fixed
- Fixed a bug where `Task::run` would only return the last line of the command's output

## [0.2.0] - 2013-06-24

### Added
- The core of Rocketeer's actions is now split into a system of Tasks for flexibility
- Added a `Rocketeer` facade to easily add tasks via `before` and `after` (see Tasks docs)

## [0.1.1] - 2013-06-23

### Fixed
- Fixed a bug where the commands would try to connect to the remote hosts on construct
- Fixed `ReleasesManager::getPreviousRelease` returning the wrong release

## 0.1.0 - 2013-06-23

### Added
- Added `deploy:teardown` to remove the application from remote servers
- Added support for the connections defined in the remote config file
- Added `deploy:rollback` and `deploy:current` commands
- Added `deploy:cleanup` command
- Added config file
- Added `deploy:setup` and `deploy:deploy` commands

[2.2.0]: https://github.com/rocketeers/rocketeer/compare/2.1.3...2.2.0
[2.1.3]: https://github.com/rocketeers/rocketeer/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/rocketeers/rocketeer/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/rocketeers/rocketeer/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/rocketeers/rocketeer/compare/2.0.6...2.1.0
[2.0.6]: https://github.com/rocketeers/rocketeer/compare/2.0.5...2.0.6
[2.0.5]: https://github.com/rocketeers/rocketeer/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/rocketeers/rocketeer/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/rocketeers/rocketeer/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/rocketeers/rocketeer/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/rocketeers/rocketeer/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/rocketeers/rocketeer/compare/1.2.2...2.0.0
[1.2.2]: https://github.com/rocketeers/rocketeer/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/rocketeers/rocketeer/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/rocketeers/rocketeer/compare/1.1.2...1.2.0
[1.1.2]: https://github.com/rocketeers/rocketeer/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/rocketeers/rocketeer/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/rocketeers/rocketeer/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/rocketeers/rocketeer/compare/0.9.0...1.0.0
[0.9.0]: https://github.com/rocketeers/rocketeer/compare/0.8.0...0.9.0
[0.8.0]: https://github.com/rocketeers/rocketeer/compare/0.7.0...0.8.0
[0.7.0]: https://github.com/rocketeers/rocketeer/compare/0.6.5...0.7.0
[0.6.5]: https://github.com/rocketeers/rocketeer/compare/0.6.4...0.6.5
[0.6.4]: https://github.com/rocketeers/rocketeer/compare/0.6.3...0.6.4
[0.6.3]: https://github.com/rocketeers/rocketeer/compare/0.6.2...0.6.3
[0.6.2]: https://github.com/rocketeers/rocketeer/compare/0.6.1...0.6.2
[0.6.1]: https://github.com/rocketeers/rocketeer/compare/0.6.0...0.6.1
[0.6.0]: https://github.com/rocketeers/rocketeer/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/rocketeers/rocketeer/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/rocketeers/rocketeer/compare/0.3.2...0.4.0
[0.3.2]: https://github.com/rocketeers/rocketeer/compare/0.3.1...0.3.2
[0.3.1]: https://github.com/rocketeers/rocketeer/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/rocketeers/rocketeer/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/rocketeers/rocketeer/compare/0.1.1...0.2.0
[0.1.1]: https://github.com/rocketeers/rocketeer/compare/0.1.0...0.1.1
