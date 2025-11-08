<?php

namespace Botble\Media\Providers;

use Aws\S3\S3Client;
use Botble\Base\Supports\AdminHelper;
use Botble\Base\Supports\BaseHelper;
use Botble\Base\Supports\HtmlBuilder;
use Botble\Base\Supports\ServiceProvider;
use Botble\Media\Chunks\Storage\ChunkStorage;
use Botble\Media\Commands\ClearChunksCommand;
use Botble\Media\Commands\CropImageCommand;
use Botble\Media\Commands\DeleteThumbnailCommand;
use Botble\Media\Commands\GenerateThumbnailCommand;
use Botble\Media\Commands\InsertWatermarkCommand;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Models\MediaSetting;
use Botble\Media\Repositories\Eloquent\MediaFileRepository;
use Botble\Media\Repositories\Eloquent\MediaFolderRepository;
use Botble\Media\Repositories\Eloquent\MediaSettingRepository;
use Botble\Media\Repositories\Interfaces\MediaFileInterface;
use Botble\Media\Repositories\Interfaces\MediaFolderInterface;
use Botble\Media\Repositories\Interfaces\MediaSettingInterface;
use Botble\Media\Storage\BunnyCDN\BunnyCDNAdapter;
use Botble\Media\Storage\BunnyCDN\BunnyCDNClient;
use Botble\Media\Supports\HookManager;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\AwsS3V3Adapter as IlluminateAwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

/**
 * @since 02/07/2016 09:50 AM
 */
class MediaServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton('botble.base.helper', fn () => new BaseHelper());
        $this->app->singleton('botble.base.html', fn () => new HtmlBuilder());
        $this->app->singleton('botble.base.admin-helper', fn ($app) => new AdminHelper($app['router'], $app['request']));
        $this->app->singleton(HookManager::class, fn ($app) => new HookManager($app));

        $this->app->singleton(SettingStore::class, function ($app) {
            $config = $app['config']->get('core.media.media.settings', []);

            if (empty($config)) {
                $config = $app['config']->get('media.settings', []);
            }

            return new SettingStore($config);
        });

        $this->app->bind(MediaFileInterface::class, function () {
            return new MediaFileRepository(new MediaFile());
        });

        $this->app->bind(MediaFolderInterface::class, function () {
            return new MediaFolderRepository(new MediaFolder());
        });

        $this->app->bind(MediaSettingInterface::class, function () {
            return new MediaSettingRepository(new MediaSetting());
        });

        $this->app->singleton(ChunkStorage::class);

        if (! class_exists('RvMedia')) {
            AliasLoader::getInstance()->alias('RvMedia', RvMedia::class);
        }
    }

    public function boot(): void
    {
        $this
            ->setNamespace('core/media')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['media'])
            ->loadAndPublishConfigurations(['permissions'])
            ->publishConfigToRoot(['media'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->publishAssets();

        $this->synchronizeMediaConfig();

        $config = $this->app->make('config');
        $setting = $this->app->make(SettingStore::class);

        $config->set([
            'core.media.media.chunk.enabled' => (bool) $setting->get(
                'media_chunk_enabled',
                $config->get('core.media.media.chunk.enabled')
            ),
            'core.media.media.chunk.chunk_size' => (int) $setting->get(
                'media_chunk_size',
                $config->get('core.media.media.chunk.chunk_size')
            ),
            'core.media.media.chunk.max_file_size' => (int) $setting->get(
                'media_max_file_size',
                $config->get('core.media.media.chunk.max_file_size')
            ),
        ]);

        if (! $config->get('core.media.media.use_storage_symlink')) {
            RvMedia::setUploadPathAndURLToPublic();
        }

        $this->app->resolving(FilesystemManager::class, function (): void {
            Storage::extend('wasabi', function ($app, $config) {
                $config['url'] = 'https://' . $config['bucket'] . '.s3.' . $config['region'] . '.wasabisys.com/';

                $client = new S3Client([
                    'endpoint' => $config['url'],
                    'bucket_endpoint' => true,
                    'credentials' => [
                        'key' => $config['key'],
                        'secret' => $config['secret'],
                    ],
                    'region' => $config['region'],
                    'version' => 'latest',
                ]);

                $adapter = new AwsS3V3Adapter($client, $config['bucket'], trim($config['root'], '/'));

                return new IlluminateAwsS3V3Adapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config,
                    $client,
                );
            });

            Storage::extend('bunnycdn', function ($app, $config) {
                $adapter = new BunnyCDNAdapter(
                    new BunnyCDNClient(
                        $config['storage_zone'],
                        $config['api_key'],
                        $config['region']
                    ),
                    'https://' . $config['hostname']
                );

                return new FilesystemAdapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config
                );
            });

            $config = $this->app->make('config');
            $setting = $this->app->make(SettingStore::class);

            $mediaDriver = RvMedia::getMediaDriver();

            $config->set([
                'filesystems.default' => $mediaDriver,
                'filesystems.disks.public.throw' => true,
            ]);

            switch ($mediaDriver) {
                case 's3':
                    RvMedia::setS3Disk([
                        'key' => $setting->get('media_aws_access_key_id', $config->get('filesystems.disks.s3.key')),
                        'secret' => $setting->get('media_aws_secret_key', $config->get('filesystems.disks.s3.secret')),
                        'region' => $setting->get('media_aws_default_region', $config->get('filesystems.disks.s3.region')),
                        'bucket' => $setting->get('media_aws_bucket', $config->get('filesystems.disks.s3.bucket')),
                        'url' => $setting->get('media_aws_url', $config->get('filesystems.disks.s3.url')),
                    ]);

                    break;
                case 'digitalocean':
                    RvMedia::setDigitalOceanDisk([
                        'key' => $setting->get('media_do_spaces_access_key_id'),
                        'secret' => $setting->get('media_do_spaces_secret_key'),
                        'region' => $setting->get('media_do_spaces_default_region'),
                        'bucket' => $setting->get('media_do_spaces_bucket'),
                        'endpoint' => $setting->get('media_do_spaces_endpoint'),
                        'use_path_style_endpoint' => (bool) $setting->get('media_do_spaces_use_path_style_endpoint', false),
                    ]);

                    break;
                case 'backblaze':
                    RvMedia::setBackblazeDisk([
                        'key' => $setting->get('media_backblaze_access_key_id'),
                        'secret' => $setting->get('media_backblaze_secret_key'),
                        'region' => $setting->get('media_backblaze_default_region'),
                        'bucket' => $setting->get('media_backblaze_bucket'),
                        'url' => $setting->get('media_backblaze_url'),
                        'endpoint' => $setting->get('media_backblaze_endpoint'),
                        'use_path_style_endpoint' => (bool) $setting->get('media_backblaze_use_path_style_endpoint', false),
                    ]);

                    break;

                default:
                    do_action('cms_setup_media_disk', $mediaDriver);

                    break;
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateThumbnailCommand::class,
                CropImageCommand::class,
                DeleteThumbnailCommand::class,
                InsertWatermarkCommand::class,
                ClearChunksCommand::class,
            ]);

            $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
                if (RvMedia::getConfig('chunk.clear.schedule.enabled')) {
                    $schedule
                        ->command(ClearChunksCommand::class)
                        ->cron(RvMedia::getConfig('chunk.clear.schedule.cron'));
                }
            });
        }
    }

    protected function publishConfigToRoot(array $files): static
    {
        foreach ($files as $file) {
            $path = $this->modulePath('config' . DIRECTORY_SEPARATOR . $file . '.php');

            if (file_exists($path)) {
                $this->publishes([
                    $path => config_path($file . '.php'),
                ], 'botble-media-config');
            }
        }

        return $this;
    }

    protected function synchronizeMediaConfig(): void
    {
        $config = $this->app->make('config');

        $coreMediaConfig = $config->get('core.media.media', []);
        $rootMediaConfig = $config->get('media', []);

        if (empty($coreMediaConfig) && empty($rootMediaConfig)) {
            return;
        }

        $merged = array_replace_recursive($coreMediaConfig, $rootMediaConfig);

        $config->set('core.media.media', $merged);
        $config->set('media', $merged);
    }
}
