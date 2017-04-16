<?php

class ProductFacade
{

    private $products = array();
    private $file;

    function __construct($file)
    {
        $this->file = $file;
        $this->compile();
    }

    private function compile()
    {
        $lines = $this->getProductFileLines($this->file);
        foreach ($lines as $line) {
            $id = $this->getIDFromLine($line);
            $name = $this->getNameFromLine($line);
            $this->products[$id] = $this->getProductObjectFromID($id, $name);
        }
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function getProduct($id)
    {
        if (isset($this->products[$id])) {
            return $this->products[$id];
        }
        return null;
    }

    private function getProductFileLines($fileName)
    {
        if (file_exists($fileName)) {
            $file = file($fileName);
            return $file;
        }
        throw new Exception("Файла $fileName не существует");
    }

    private function getNameFromLine($line)
    {
        if (preg_match("/.*-(.*)\s\d+/", $line, $array)) {
            return str_replace('_', ' ', $array[1]);
        }
        return '';
    }

    private function getIDFromLine($line)
    {
        if (preg_match("/^(\d{0,3})-/", $line, $array)) {
            return $array[1];
        }
        return -1;
    }

    private function getProductObjectFromID($id, $name)
    {
        return array('id' => $id, 'name' => $name);
    }

}

$facade = new ProductFacade('test.txt');
var_dump($facade->getProducts());
