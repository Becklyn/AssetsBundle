2.6.1
=====

*   Fixed a bug where the algorithm was missing from the `integrity` attribute in prod environment.


2.6.0
=====

*   Added support for the new `_modern.entry.js` + `entry.js` builds (this is the new way Kaba builds the entries, the old way is `_legacy.entry.js` + `entry.js`).
*   Fixed a bug where a dependency that was both required in legacy + modern builds was only loaded once for one of the entries.

2.5.0
=====

*   Added new debug command: `becklyn:assets:debug`.
*   Added support for modern + legacy builds for JS. These will only be linked if there are both a `@namespace/file.js` 
    as well as a `@namespace/_legacy.file.js` entry in the dependency map.
    These imports + their dependencies will be loaded using either `type="module"` (modern) or `nomodule` (legacy).
*   Updated composer dependencies

2.4.0
=====

*   **New Feature**: the dumped assets are now automatically compressed.
*   PHP 7.2+ and Symfony 4.2+ are now required
