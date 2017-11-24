BecklynAssetsBundle
===================

High-performance asset handling for symfony projects.

This bundle is a simplified and specialized replacement for `symfony/asset`.


Installation
------------

```bash
composer require "becklyn/assets-bundle"
```

and then add the bundle in your `AppKernel` / `bundles.php`.


Info / Purpose
--------------

This bundle aims to provide a way to transparently handle long-term caching of assets.

All assets in your entry points are copied (and possibly transformed) to `public/assets/` and the file names modified, so that they contain the content hash of the given file.
It also provides Twig functions to use these translated paths transparently in a symfony application.

In Twig the original filename is given to the `asset*` function and it automatically generates the HTML reference for the hashed file name.

**Warning:** the directory `public/assets/` is completely cleared on cache clear, so it should exclusively be managed by this bundle.

All requests to the `assets/` directory can be cached for a long time, like with a expiry date in the far future or even `immutable` headers.


Usage
-----

The asset generation and preparation is done automatically.
The assets are pregenerated on cache clear + warmup or on-the-fly generated if encountered with an empty cache.

In the configuration a mapping of namespaces to directories is defined. These namespaces are then used to retrieve paths to these files by using the `@{namespace}/{path}` syntax:

```yaml
becklyn_assets:
    entries:
        bundles: "public/bundles"
```

In Twig, the following functions can be used:

```twig
{# automatically generates the <script></script> tags #}
{{ assets_js([
    "@bundles/app/js/bootstrap.js",
    "@bundles/app/js/app.js",
]) }}

{# automatically generates the <link> tags #}
{{ assets_js([
    "@bundles/app/css/bootstrap.css",
    "@bundles/app/css/app.css",
]) }}

{# just returns the URL #}
<img src="{{ asset("@bundles/app/img/logo.svg") }}" alt="Company Logo">
```


Asset Processing
----------------

Currently only one asset processor is included in the bundle. Asset processors are chosen by file extension and are supposed to transform the given file content.
They are not supposed to alter the file in terms of minification, but to adjust import paths (like `url(...)` in CSS) to the new paths.

Currently registered processors:

| Processor      | File Extensions | Purpose                   |
| -------------- | --------------- | ------------------------- |
| `CssProcessor` | `.css`          | Rewrite `url(...)` paths. |


**Warning:**
The asset processors are using the paths from the `AssetCache`. This implies a race condition, as the processor can only rewrite paths of files that were already added to the cache. So files with a processor are deferred and only processed after all other files are finished (at least if the command / cache warmer is used). There still can be race conditions, if files with processors depend on each other. 


Configuration
-------------

All configuration values with their description and defaults:

```yaml
becklyn_assets:
    # the entry points. Maps the namespace to the directory (relative to `%kernel.project_dir%`)
    # -> is required
    entries:
        bundles: "public/bundles"
        app: "assets"
        
    # the absolute path to the `public/` (or `web/`) directory
    public_path: '%kernel.project_dir%/public' 
    # relative path to the directory, where the assets are copied to (relative to `public_path`)
    output_dir: 'assets' 
```

Command
-------

The bundle provides a command to clear and warmup the cache:

```bash
# clear + warmup
php bin/console becklyn:assets:reset

# only clear
php bin/console becklyn:assets:reset --no-warmup
``` 

Normally these commands are not required, as the bundle automatically registers as **cache clearer** and **cache warmer**.
