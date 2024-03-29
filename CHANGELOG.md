2.7.3
=====

*   (bug) Require correct version of `symfony/service-contracts` to be fully Symfony 6 compatible.


2.7.2
=====

*   (bug) Require correct version of `symfony/cache-contracts` to be fully Symfony 6 compatible.


2.7.1
=====

*   (improvement) Add missing return type for `CacheWarmer::isOptional()`.


2.7.0
=====

*   (feature) Add support for PHP 8.
*   (internal) Fix Symfony deprecations.
*   (internal) Replace TravisCI with GitHub Actions.
*   (improvement) Add missing property types and return types.
*   (internal) Remove support for Symfony 4.4.
*   (improvement) Add support for Symfony 6.


2.6.9
=====

*   (bug) Fix small BC break in older symfony versions.


2.6.8
=====

*   (improvement) Add global `allow_cors` option for assets.


2.6.7
=====

*   (bug) Fix invalid cache key.


2.6.6
=====

*   (deprecation) Deprecate the option `dependency_maps` from the config. These are now automatically loaded for all namespaces
    from path `/js/_dependencies.json`.
*   (bug) Always automatically load dependency maps for all extensions (and cache the map).
*   (improvement) Add dump of dependency map to debug command output. 
*   (improvement) Add proper section headlines to debug command output. 


2.6.5
=====

*   (bug) Fixed a circular dependency issue.
*   (improvement) The specialized file types are now properly autowired.
*   (internal) Bump required Symfony version to 4.3+


2.6.4
=====

*   (improvement) Add specialized existence check in `AssetUrl` in debug mode when creating URLs. It circumvents the file loading, this heavily improves performance in debug requests with a cold cache.


2.6.3
=====

*   Allow older versions of `Symfony/Mime` to be used with this lib. This re-establishes usage in Symfony 4.x projects again, as they most likely restricted the version
    of Symfony components to 4.x.


2.6.2
=====

*   Allow Symfony 5.


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
