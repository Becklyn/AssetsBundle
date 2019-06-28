2.5.0
=====

*   Added new debug command: `becklyn:assets:debug`.
*   Added support for modern + legacy builds for JS. These will only be linked if there are both a `@namespace/file.js` 
    as well as a `@namespace/_legacy.file.js` entry in the dependency map.
    These imports + their dependencies will be loaded using either `type="module"` (modern) or `nomodule` (legacy).

2.4.0
=====

*   **New Feature**: the dumped assets are now automatically compressed.
*   PHP 7.2+ and Symfony 4.2+ are now required
