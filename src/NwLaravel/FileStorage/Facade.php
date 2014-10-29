<?php
namespace NwLaravel\FileStorage;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    protected static function getFacadeAccessor()
    {
        return 'nwlaravel.filestorage';
    }
}