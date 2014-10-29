<?php
namespace NwLaravel\FileStorage;

use Aws\S3\S3Client;
use Dropbox\Client as DropboxClient;

use NaturalWeb\FileStorage\Storage\FileSystemStorage;
use NaturalWeb\FileStorage\Storage\S3Storage;
use NaturalWeb\FileStorage\Storage\DropboxStorage;
use NaturalWeb\FileStorage\Storage\StorageInterface;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $config;

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('naturalweb/nwlaravel');
    }
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $me = $this;

        $this->app->bind('nwlaravel.filestorage', function($app) use($me)
        {
            $config = $app['config']->get('nwlaravel::filestorage');

            $me->registerStorage($config);

            return new FileStorage($app['nwlaravel.filestorage.storage']);
        });
    }

    /**
     * Registra Storage Default
     * 
     * @param array $config
     * 
     * @return StorageInterface
     */
    protected function registerStorage($config)
    {
        switch ($config['default']) {
            case 'filesystem':
                return $this->registerFileSystemStorage($config);

            case 's3':
                return $this->registerS3Storage($config);

            case 'dropbox':
                return $this->registerDropboxStorage($config);
            
            default:
                throw new \InvalidArgumentException('Invalid storage filestorage.');
                break;
        }
    }

    /**
     * Criar storage file system
     *
     * @param array $config
     * 
     * @return StorageInterface
     */
    protected function registerFileSystemStorage(array $config)
    {
        $this->app['nwlaravel.filestorage.storage'] = $this->app->share(function($app) use ($config)
        {
            return new FileSystemStorage($config['root'], $config['host']);
        });
    }

    /**
     * Criar storage do aws S3
     *
     * @param array $config
     * 
     * @return StorageInterface
     */
    protected function registerS3Storage(array $config)
    {
        $this->app['nwlaravel.filestorage.storage'] = $this->app->share(function($app) use ($config)
        {
            $s3 = S3Client::factory(array(
                'key'    => $config['access'],
                'secret' => $config['secret']
            ));

            return new S3Storage($config['root'], $dropbox);
        });
    }

    /**
     * Criar storage do dropbox
     * 
     * @param array $config
     * 
     * @return StorageInterface
     */
    protected function registerDropboxStorage(array $config)
    {
        $this->app['nwlaravel.filestorage.storage'] = $this->app->share(function($app) use ($config)
        {
            $dropbox = new DropboxClient($config['token'], $config['app']);

            return new DropboxStorage($config['root'], $dropbox);
        });
    }
}