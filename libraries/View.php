<?php

class View{

    public function __construct()
    {
        //echo 'this is view';
    }

    public function get($name)
    {
        require 'Views/'.$name.'.php';
    }
}