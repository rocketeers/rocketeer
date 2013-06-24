### Roadmap

- Allow the adding of custom tasks to the CLI (like `deploy:mytask`)

------------

### Changelog

### 0.2.0

- The core of Rocketeer's actions is now split into a system of Tasks for flexibility
- Added a `Rocketeer` facade to easily add tasks via `before` and `after` (see Tasks docs)

### 0.1.1

- Fixed a bug where the commands would try to connect to the remote hosts on construct
- Fixed ReleasesManager::getPreviousRelease returning the wrong release

### 0.1.0

- Add `deploy:teardown` to remove the application from remote servers
- Add support for the connections defined in the remote config file
- Add `deploy:rollback` and `deploy:current` commands
- Add `deploy:cleanup` command
- Add config file
- Add `deploy:setup` and `deploy:deploy` commands