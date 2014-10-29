<?php
namespace NwLaravelTest\FileStorage;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use NwLaravel\FileStorage\Facade as FacadeFilestorage;

class FacadeTest extends PHPUnit_Framework_TestCase
{
    protected function callProtectedMethod($object, $method, array $args=array())
    {
        $class = new ReflectionClass(get_class($object));
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    public function testGetFacadeAccessor()
    {
        $object = new FacadeFilestorage;

        $return = $this->callProtectedMethod($object, 'getFacadeAccessor');
        
        $this->assertEquals('nwlaravel.filestorage', $return);
    }
}