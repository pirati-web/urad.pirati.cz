<?php


class DocsStorage
{

    use Nette\SmartObject;
    
    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function save($file, $contents)
    {
        file_put_contents($this->dir . '/' . $file, $contents);
    }
    
    public function get($file){
        return file_get_contents($this->dir . '/' . $file);
    }

    public function get_path($file){
        return $this->dir . '/' . $file;
    }
    
}
