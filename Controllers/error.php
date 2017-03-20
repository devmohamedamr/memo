<?php

class Error extends Controller{

    public function __construct()
    {
        parent::__construct();
        $this->View->msg = 'error';
        $this->View->get('error/index');
    }
}