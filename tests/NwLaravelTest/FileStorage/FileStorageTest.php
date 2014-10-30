<?php
namespace NwLaravelTest\FileStorage;

use PHPUnit_Framework_TestCase;
use NwLaravel\FileStorage\FileStorage;
use Mockery as m;
use NwLaravel\Image\Facade as Image;
use Illuminate\Support\Facades\Validator;

class FileStorageTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $storage = m::mock('NaturalWeb\FileStorage\Storage\StorageInterface');
        $object = new FileStorage($storage);

        $this->assertInstanceOf('NaturalWeb\FileStorage\FileStorage', $object);
    }

    public function testMethodsSetAndGetMaxSize()
    {
        $sizeDefault = intval(preg_replace('[^0-9]', '', ini_get('upload_max_filesize')));

        $storage = m::mock('NaturalWeb\FileStorage\Storage\StorageInterface');
        $object = new FileStorage($storage);

        $this->assertAttributeEquals(null, 'maxSize', $object);

        $max = $object->getMaxSize();
        $this->assertEquals($sizeDefault, $max);
        $this->assertAttributeEquals($sizeDefault, 'maxSize', $object);

        $object->setMaxSize(4);
        $max = $object->getMaxSize();   
        $this->assertEquals(4, $max);
        $this->assertAttributeEquals(4, 'maxSize', $object);
    }

    public function testMethodsSetAndGetPathTmp()
    {
        $storage = m::mock('NaturalWeb\FileStorage\Storage\StorageInterface');
        $object = new FileStorage($storage);

        $this->assertAttributeEquals(null, 'pathTmp', $object);

        $path = $object->getPathTmp();
        $this->assertEquals('/tmp', $path);
        $this->assertAttributeEquals('/tmp', 'pathTmp', $object);

        $object->setPathTmp('/other/path');
        $path = $object->getPathTmp();
        $this->assertEquals('/other/path', $path);
        $this->assertAttributeEquals('/other/path', 'pathTmp', $object);
    }

    public function testMethodUploadImageThownExceptionMimes()
    {
        $this->setExpectedException('NwLaravel\FileStorage\FileStorageException', '', 1);

        // Mocks
        $mimes = 'jpeg,jpg,png,gif';
        $random = true;
        $field = 'campo';

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('cdr'));

        $object = m::mock('NwLaravel\FileStorage\FileStorage[]');

        $object->uploadImage($field, $file, '/folder/destino', 80, 60, $random);
    }

    public function testMethodUploadImageThownExceptionRedimensionar()
    {
        $this->setExpectedException('NwLaravel\FileStorage\FileStorageException', '', 2);

        // Mocks
        Image::shouldReceive('resize')
            ->once()
            ->with('/path/foobar.jpg', 80, 60);

        $mimes = 'jpeg,jpg,png,gif';
        $random = true;
        $field = 'campo';

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('jpg'));

        $object = m::mock('NwLaravel\FileStorage\FileStorage[uploadTmp]');

        $dados = array('name' => 'foobar.jpg', 'path' => '/path/foobar.jpg');
        $object->shouldReceive('uploadTmp')
            ->once()
            ->with($field, $file, $random, $mimes)
            ->andReturn($dados);

        $object->uploadImage($field, $file, '/folder/destino', 80, 60, $random);
    }

    public function testMethodUploadImageSuccess()
    {
        // Mocks
        Image::shouldReceive('resize')
            ->once()
            ->with('/path/foobar.jpg', 80, 60)
            ->andReturn(true);

        $mimes = 'jpeg,jpg,png,gif';
        $random = true;
        $field = 'campo';

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('jpg'));

        $object = m::mock('NwLaravel\FileStorage\FileStorage[uploadTmp,save]');

        $dados = array('name' => 'foobar.jpg', 'path' => '/path/foobar.jpg');
        $object->shouldReceive('uploadTmp')
            ->once()
            ->with($field, $file, $random, $mimes)
            ->andReturn($dados);

        $object->shouldReceive('save')
            ->once()
            ->with($dados['name'], $dados['path'], '/folder/destino')
            ->andReturn(true);

        $return = $object->uploadImage($field, $file, '/folder/destino', 80, 60, $random);

        $this->assertTrue($return);
    }

    public function testMethodUploadFile()
    {
        // Mocks
        $random = false;
        $field = 'campo';
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $object = m::mock('NwLaravel\FileStorage\FileStorage[uploadTmp,save]');

        $dados = array('name' => 'baz.doc', 'path' => '/path/baz.doc');
        $object->shouldReceive('uploadTmp')
            ->once()
            ->with($field, $file, $random, null)
            ->andReturn($dados);

        $object->shouldReceive('save')
            ->once()
            ->with($dados['name'], $dados['path'], '/folder/destino')
            ->andReturn(true);

        $return = $object->uploadFile($field, $file, '/folder/destino', $random);

        $this->assertTrue($return);
    }

    public function testMethodUploadTmpThownException()
    {
        $this->setExpectedException('NwLaravel\FileStorage\FileStorageException', 'Msg Error', 3);

        $sizeDefault = intval(preg_replace('[^0-9]', '', ini_get('upload_max_filesize')));

        // Mocks
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $random = false;
        $field = 'campo';

        $validator = m::mock('Illuminate\Validation\Validator');
        $validator->shouldReceive('fails')
            ->once()
            ->andReturn(true);

        $validator->shouldReceive('messages')
            ->once()
            ->andReturn('Msg Error');

        $rules = array('max' => $sizeDefault*1024);

        Validator::shouldReceive('make')
            ->once()
            ->with(array($field => $file), array($field => $rules), array("{$field}.max" => "O :attribute deve ser menor que {$sizeDefault}Mb"))
            ->andReturn($validator);

        $storage = m::mock('NaturalWeb\FileStorage\Storage\StorageInterface');
        $object = new FileStorage($storage);

        $return = $object->uploadTmp($field, $file, $random);
        
        $this->assertTrue($return);
    }

    public function testMethodUploadTmpSuccess()
    {
        // Mocks
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('Nome é Rüim dõ Arquívo.doc'));

        $file->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('doc'));

        $random = false;
        $field = 'campo';
        $pathTmp = sys_get_temp_dir();
        $nameBom = 'nome-e-ruim-do-arquivo.doc';

        $file->expects($this->once())
            ->method('move')
            ->with($pathTmp, $nameBom)
            ->will($this->returnValue(true));

        $validator = m::mock('Illuminate\Validation\Validator');
        $validator->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        $size = 3;
        $rules = array('max' => $size*1024, 'mimes' => 'doc,txt');
        Validator::shouldReceive('make')
            ->once()
            ->with(array($field => $file), array($field => $rules), array("{$field}.max" => "O :attribute deve ser menor que {$size}Mb"))
            ->andReturn($validator);
        $storage = m::mock('NaturalWeb\FileStorage\Storage\StorageInterface');
        $object = new FileStorage($storage);
        $object->setMaxSize($size);

        $return = $object->uploadTmp($field, $file, $random, 'doc,txt');
        
        $expect = array('name' => $nameBom, 'path' => $pathTmp.'/'.$nameBom);
        $this->assertEquals($expect, $return);
    }
}