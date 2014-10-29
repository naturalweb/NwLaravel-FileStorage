NwLaravel FileStorage
=====================

### Installation

- [API on Packagist](https://packagist.org/packages/naturalweb/nwlaravel-filestorage)
- [API on GitHub](https://github.com/naturalweb/NwLaravel-FileStorage)

In the `require` key of `composer.json` file add the following

    "naturalweb/nwlaravel-filestorage": "~0.1"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'NwLaravel\FileStorage\FileStorageServiceProvider'` to the end of the `$providers` array

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'NwLaravel\FileStorage\FileStorageServiceProvider',

),
```

At the end of `config/app.php` add `'FileStorage'    => 'NwLaravel\FileStorage\FileStorageFacade'` to the `$aliases` array

```php
'aliases' => array(

    'App'        => 'Illuminate\Support\Facades\App',
    'Artisan'    => 'Illuminate\Support\Facades\Artisan',
    ...
    'FileStorage'    => 'NwLaravel\FileStorageFacade',

),
```

### Configuration

Publish config using artisan CLI.

~~~
php artisan config:publish naturalweb/nwlaravel-filestorage
~~~

The configuration to `app/config/packages/naturalweb/nwlaravel-filestorage/config/filestorage.php`. This file will look somewhat like:

```php
<?php

/*
|--------------------------------------------------------------------------
| Configuration FileStorage
|--------------------------------------------------------------------------
*/

return array(

    'default'  => 'filesystem',

    'path_tmp'  => sys_get_temp_dir(),
    
    'storages'  => array(

        'filesystem' => array(
            'root' => public_path('/uploads'),
            'host' => url('uploads'),
        ),

        's3' => array(
            'root'    => '/bucket',
            'access'  => 'your-access',
            'secret'  => 'your-secret',
        ), 

        'dropbox' => array(
            'root'   => '/folder',
            'token'  => 'your-token',
            'app'    => 'your-app',
        ),
    ),
);
```

### Usage
```php
$name = 'name-file.txt';
$source = '/source/path/file.txt';
$folder = '/folder/destino';
$override = true;
$bool = FileStorage::save($name, $source, $folder, $override);
```
