# Verification

Environment: Windows, PHP 8.4.26, Laravel 12.69.2, SQLite for local demo and isolated in-memory SQLite for PHPUnit.

- 18 tests, 94 assertions passed: registration/login/logout, prevention of role escalation, owner-only job CRUD, sanitizer behavior, search filters, duplicate application prevention, notification dispatch, job expiry, bookmarks, application updates, dashboards for four roles, admin restrictions, profile validation, salary validation and cascading deletion.
- Migrations and demonstration seed completed successfully.
- Blade templates compiled successfully.
- Laravel Pint formatted the PHP code.
- Desktop (1440px) and mobile (390px) screenshots were inspected. Mobile has no horizontal page overflow.
- Browser checks cover homepage, employer sign-in and the locally bundled rich-text editor. Screenshots are included in this folder.

Limits: MySQL configuration and portable migrations are supplied, but MySQL was not available here to run an integration test. Real SMTP and Pusher delivery were not tested without user credentials. Notification dispatch, database notification persistence, read state and valid avatar storage are covered by automated tests. Database/log notification delivery can be demonstrated with the queue worker.

Run from the project directory:

```sh
php artisan test
php artisan view:cache
```


## Upgrade verification

Four additional workflow tests passed (22 assertions), bringing the total to 22 tests and 116 assertions across the original and upgrade suites. These cover history recording without duplicate unchanged events, authorization, workspace tabs, applicant filters, suggestion exclusions, company search, and salary sorting.

The workspace PHP runtime currently reports that Windows Application Control blocks its curl extension. Local database workflows pass; external Pusher/SMTP integrations remain unverified.
