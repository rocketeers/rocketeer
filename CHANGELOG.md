### Roadmap

- Allow the adding of custom tasks to the CLI (like `deploy:mytask`)
- Add `--pretend` option to console commands to only display the commands that would be executed

------------

### Changelog

### 0.2.1

- Added **Task::runForCurrentRelease** Task helper
- Fixed a bug where **Task::run** would only return the last line of the command's output
- Added **Task::runTests** methods to run the PHPUnit tests of the application
- Integrated **Task::runTests** in the **Deploy** task under the `--tests` flag ; failing tests will cancel deploy and rollback

### 0.2.0

- The core of Rocketeer's actions is now split into a system of Tasks for flexibility
- Added a `Rocketeer` facade to easily add tasks via `before` and `after` (see Tasks docs)

### 0.1.1

- Fixed a bug where the commands would try to connect to the remote hosts on construct
- Fixed **ReleasesManager::getPreviousRelease** returning the wrong release

### 0.1.0

- Add `deploy:teardown` to remove the application from remote servers
- Add support for the connections defined in the remote config file
- Add `deploy:rollback` and `deploy:current` commands
- Add `deploy:cleanup` command
- Add config file
- Add `deploy:setup` and `deploy:deploy` commands