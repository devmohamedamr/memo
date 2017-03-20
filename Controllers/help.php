<?php

class help extends Controller{
    public function __construct(){
        parent::__construct();
        echo 'inside helpe'.'</br>';
    }

    public function other($value = false){
        echo 'inside other'.'</br>';
        echo 'op'.$value.'</br>';

        require 'Models/helpModel.php';
        $Model = new helpModel();
    }
}