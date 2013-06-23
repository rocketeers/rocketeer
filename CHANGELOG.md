### Roadmap

- Split the actual tasks (deploy, cleanup, etc) from their commands for easier testing and reuse
- Custom tasks as classes that allow you to use Rocketeer's helpers
- Create tasks with callbacks to allow things like running tests and cancelling deploy if tests fail

------------

### Changelog

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