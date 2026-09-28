# Third-party components

PHP package versions and licenses are recorded in composer.lock and installed vendor metadata.

- Bootstrap 5.3.8: MIT, https://github.com/twbs/bootstrap.
- Laravel Echo 2.2.6: MIT, https://github.com/laravel/echo.
- Pusher JS 8.4.0: MIT, https://github.com/pusher/pusher-js.
- CKEditor 5 46.1.1: GPL-2.0-or-later edition, https://github.com/ckeditor/ckeditor5. Source imports are in resources/js/editor.js; package-lock.json pins dependencies. Run npm ci and npm run build:editor to reproduce the bundle. No premium plugins are used.

Prebuilt assets are stored in public/vendor. Original notices are retained. The project's source is GPL-2.0-or-later; see LICENSE. Dependencies retain their own licenses.
