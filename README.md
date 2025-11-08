# Botble Media Manager for Laravel

Botble Media is a drop-in media library built for Laravel 10 and 11 projects. It provides a full UI for browsing folders, uploading files (including chunked uploads), generating thumbnails, and integrating with cloud storage drivers such as Amazon S3, DigitalOcean Spaces, Wasabi, Backblaze B2, and BunnyCDN.

## Requirements

- PHP 8.1+
- Laravel 10 or 11
- Database connection supported by Laravel's Eloquent ORM
- Node/npm only if you plan to rebuild the frontend assets (precompiled assets are already shipped)

## 1. Install the package

```bash
composer require botblemedia/media-manager:dev-main
```

> **Heads up:** the package has not been tagged with a stable release yet. If your application enforces `"minimum-stability": "stable"` you will see an error like `Could not find a version of package botblemedia/media-manager matching your minimum-stability (stable)` when installing. You can resolve this either by requiring the `dev-main` constraint (as above) or by lowering your minimum stability (e.g. add `"minimum-stability": "dev", "prefer-stable": true` to your `composer.json`).

The service provider and the `RvMedia` facade are auto-discovered through the package's `composer.json`, so you do not need to register them manually.【F:composer.json†L36-L44】

## 2. Publish assets & configuration

Publish the package resources so that you can customise them from your application:

```bash
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-config
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-translations
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-views
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-assets
```

Configuration files are merged under both `core/media/media.php` and `config/media.php`, so you can manage settings in whichever location suits your project.【F:src/Base/Traits/LoadAndPublishDataTrait.php†L25-L54】【F:src/Providers/MediaServiceProvider.php†L82-L118】

## 3. Run the migrations

The package ships with migrations for the media tables. Run them after publishing the configuration:

```bash
php artisan migrate
```

All migration files live under `database/migrations` inside the package, including updates for metadata such as folder colours and visibility flags.【F:database/migrations/2024_05_12_091229_add_column_visibility_to_table_media_files.php†L1-L41】【F:database/migrations/2023_12_07_095130_add_color_column_to_media_folders_table.php†L1-L34】

## 4. Configure access & routing

By default the media UI is served from `/media` and is protected by the `web` and `auth` middleware. You can change the prefix or middleware stack in `config/media.php` once the config has been published.【F:config/media.php†L4-L33】

The package also exposes granular permission flags (e.g. `files.create`, `folders.destroy`) that you can map to your application's authorisation layer.【F:config/permissions.php†L1-L38】

## 5. Storage drivers & environment variables

`RvMedia` determines the active filesystem disk via the `media_driver` setting (defaults to `public`).【F:src/RvMedia.php†L1194-L1200】 When the service provider boots, it synchronises configuration and updates Laravel's filesystem defaults so the media disk becomes the application's default storage driver.【F:src/Providers/MediaServiceProvider.php†L118-L178】

For S3-compatible drivers you can configure credentials via the package settings (`media_aws_*`, `media_do_spaces_*`, `media_backblaze_*`, etc.) or mirror them in your `.env`. Wasabi and BunnyCDN receive dedicated storage adapters during boot.【F:src/Providers/MediaServiceProvider.php†L124-L178】

If you prefer to keep uploads inside `public/`, set `RV_MEDIA_USE_STORAGE_SYMLINK=false` (the default). Otherwise, enable it to use `storage:link` and Laravel's `public` disk.【F:config/media.php†L108-L112】

## 6. Include the media UI in your views

The admin view at `resources/views/vendor/core/media/index.blade.php` renders the full manager. It pushes the header/footer assets and renders the content panel via helper methods exposed by the facade.【F:resources/views/index.blade.php†L1-L12】 You can include it directly in a route that returns the published view or embed `RvMedia::renderHeader()`, `RvMedia::renderContent()`, and `RvMedia::renderFooter()` in your own Blade layout.

If you need popup integration for editors or form fields, the `/media/popup` route serves an embeddable version of the UI.【F:routes/web.php†L5-L39】

## 7. Optional features

- **Chunked uploads**: Enable large file uploads by setting `RV_MEDIA_UPLOAD_CHUNK=true` and adjusting the chunk size / max file size in the config. The package also provides a scheduled command that clears old chunk files when enabled.【F:config/media.php†L74-L105】【F:src/Providers/MediaServiceProvider.php†L180-L208】
- **Document preview**: Toggle the built-in Google/Microsoft document preview providers with `RV_MEDIA_DOCUMENT_PREVIEW_ENABLED` and choose the provider via `RV_MEDIA_DOCUMENT_PREVIEW_PROVIDER`.【F:config/media.php†L106-L137】
- **Watermarks & thumbnails**: Configure watermark source, opacity, and position, and control thumbnail generation through the same configuration file.【F:config/media.php†L56-L73】【F:config/media.php†L138-L144】

## 8. Artisan commands

When running in the console the service provider registers several artisan commands for thumbnail generation, cropping, watermark insertion, and clearing temporary chunk files. These commands are available once the package is installed and can be scheduled as needed.【F:src/Providers/MediaServiceProvider.php†L180-L208】

With these steps you can plug the Botble Media manager into an existing Laravel project and tailor it to match your storage, authorisation, and UI requirements.
