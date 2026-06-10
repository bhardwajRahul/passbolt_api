Release song: TBD

## [5.13.0-test.2] - 2026-06-10
### Added
- PB-42980 As an administrator I can upgrade my Passbolt CE instance to a Pro edition from the product
- PB-42980 As an administrator I can downgrade my Passbolt Pro instance back to CE from the product
- PB-51980 Adds a healthcheck that reports the edition currently served by the instance
- PB-51533 As an admin I can contain my_group_user in PUT /groups.json
- PB-52020 As an administrator I can run the healthcheck command if the DB is not reachable
- PB-51039 Extends the /healthcheck/status.json endpoint to verify additional components such as the cache

### Fixed
- PB-51161 Stops folder cycle detection at the personal folder boundary
- PB-50013 Fixes user session being destroyed in Safari when fetching avatar images from the web application
- PB-52027 Fixes SCIM endpoints returning non-standard HTTP status codes
- PB-51646 Fixes missing spaces in the email sent when a user lost their key/passphrase and recovery is aborted

### Security
- PB-52135 Upgrades mobiledetect/mobiledetectlib
- PB-51940 Fixes qs security vulnerability advisory GHSA-q8mj-m7cp-5q26 (Medium)
- PB-51639 Fixes PKSA-pwvr-3754-v57r security vulnerability advisory affecting composer/composer package
- PB-51194 PBL-15-006: Fixes internal UUID still disclosed in SCIM user creation conflict response (Low)

### Maintenance
- PB-51650 Introduces ScimSettingsDto for the ScimGetSettingsService::getSettings()
- PB-51647 Adds unit tests for GroupsUsersTable::isManager()
- PB-52010 Removes cakephp/bake from composer dev requirements
- PB-52126 Upgrades symfony/string to 7.4.13
- PB-51570 Upgrades CakePHP to v5.3.6 and replaces _execute() calls with process() to fix deprecations
- PB-52070 Fixes "Use expr() instead of newExpr()" deprecation warning after CakePHP upgrade
- PB-48002 Removes security.prompt from the SSO configuration
- PB-49755 Removes GitLab CI definition (moved to the ci-definitions repository)
- PB-49425 Refactors DirectorySync controller tests using fixture factories
- PB-35955 Refactors /healthcheck/status.json endpoint to use a pluggable default status strategy
