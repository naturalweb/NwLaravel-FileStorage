<?php
namespace NwLaravel\FileStorage;

use NaturalWeb\FileStorage\FileStorage as BaseFileStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use NwLaravel\Image\Facade as Image;

class FileStorage extends BaseFileStorage
{
    protected $maxSize;

    protected $pathTmp;

    public function setMaxSize($maxSize)
    {
        $this->maxSize = intval($maxSize);
    }

    public function getMaxSize()
    {
        if ( !$this->maxSize ) {
            // 2MB default
            $this->maxSize = intval(preg_replace('[^0-9]', '', ini_get('upload_max_filesize')));
        }

        return $this->maxSize;
    }

    public function setPathTmp($pathTmp)
    {
        $this->pathTmp = $pathTmp;
    }

    public function getPathTmp()
    {
        if ( empty($this->pathTmp) ) {
            $this->pathTmp = sys_get_temp_dir();
        }

        return $this->pathTmp;
    }

    /**
     * uploadImageFile
     * 
     * @param UploadedFile $file
     * @param int $width
     * @param int $height
     * @param bool $random
     * 
     * @return array (name => '', path => '')
     */
    public function uploadImage(UploadedFile $file, $folderRemote, $width, $height, $random = false)
    {
        $mimes = 'jpeg,jpg,png,gif';

        if ( ! in_array($file->guessExtension(), explode(",", $mimes)) )
        {
            throw new FileStorageException("Formato de imagem inválido, somente aceitos os ({$mimes})", 1);
        }

        $dados = $this->uploadTmp($file, $random, $mimes);
        
        if ( ! Image::resize($dados['path'], $width, $height) )
        {
            // Clear File Tmp
            @unlink($dados['path']);
            throw new FileStorageException('Erro ao tentar redimensionar a imagem!', 2);
        }

        return $this->saveStorage($dados, $folderRemote);
    }

    /**
     * Upload File
     * 
     * @param UploadedFile $file
     * @param bool         $random
     * 
     * @return array (name => '', path => '')
     */
    public function uploadFile(UploadedFile $file, $folderRemote, $random = false, $mimes = null)
    {
        $dados = $this->uploadTmp($file, $random, $mimes);

        return $this->saveStorage($dados, $folderRemote);
    }

    /**
     * Sava no Storage e exclui o arquivo tmp
     * 
     */
    protected function saveStorage($dados, $folderRemote)
    {
        // Save Storage
        $return = $this->save($dados['name'], $dados['path'], $folderRemote);

        // Clear File Tmp
        @unlink($dados['path']);

        return $return;
    }

    /**
     * UploadTmp
     * 
     * @param UploadedFile $file
     * @param bool         $random
     * 
     * @return array (name => '', path => '')
     */
    public function uploadTmp(UploadedFile $file, $random = false, $mimes = null)
    {
        $rules = array('max' => $this->getMaxSize());
        if ( !is_null($mimes) ) $rules['mimes'] = $mimes;

        $validator = Validator::make(
            array('file' => $file),
            array('file' => $rules)
        );

        if ($validator->fails()) {
            throw new FileStorageException($validator->messages(), 3);
        }

        $path = sys_get_temp_dir();
        $ext  = $file->guessExtension();
        $name = $file->getClientOriginalName();
        $name = rtrim($name, $ext);
        $name = Str::slug($name);
        if ($random) { $name = Str::random(); }
        $name = $name . '.' . $ext;

        $file->move($path, $name);

        $path = "{$path}/{$name}";

        return compact('name', 'path');
    }
}