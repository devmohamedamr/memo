<?php

/**
 * Class Controller
 * OR main controller
 */
class Controller{

    public function __construct()
    {
        //echo 'main controller'.'</br>';
        $this->View = new View();
    }

    public function LoadModel($name)
    {
        $path = '/Models'.$name.'Model.php';
        if(file_exists($path))
        {
            require 'Models/'.$name.'Model.php';
            $Modelname = $name.'Model';
            $this->model = new $Modelname;
        }

    }

}