<?php

require_once __DIR__ . '/../vendor/autoload.php';


use EasySwoole\Index;

use App\App;


(new Index())->index();

echo "\n";

echo (new App())->index();