<?php

class Bootstrap{


     function __construct()
    {
        $url = isset($_GET['url']) ? $_GET['url'] : null;
        $url = explode('/',trim($url,'/'));


        if(empty($url[0]))
        {
            require 'Controllers/index.php';
            $index = new index();
            return false;
        }

        //print_r($url);
        //file directory
        $file =  'Controllers/'.$url[0].'.php';

       if(file_exists($file))
        {
            require $file;

        }else{

           require 'Controllers/error.php';
           $error = new Error();
            return false;

        }


        $controller = new $url[0];
        $controller->LoadModel($url[0]);

        if(isset($url[2]))
        {
            if(method_exists($controller,$url[1]))
            {
                //function with value
                $controller->{$url[1]}($url[2]);
            }

        }else
        {
            //controller with function
            if(isset($url[1])){
                    $controller->{$url[1]}();
            }else{
               // $controller->index();
            }
        }
    }

}