<?php
// autoload files
//******************************
//config files
require 'Config/Paths.php';
require 'Config/db.php';
//libraries files
require LIBS.'rain.tpl.class.php';
require LIBS.'Bootstrap.php';
require LIBS.'Controller.php';
require LIBS.'Model.php';
require LIBS.'rb.php';
require LIBS.'Session.php';
require LIBS.'View.php';
require LIBS.'Validation.php';

//connection to db
R::setup(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASS); //for both mysql or mariaDB
// Bootstrap
$app = new Bootstrap();