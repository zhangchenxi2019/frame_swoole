<?php
use EasySwoole\Route\Route;

Route::get('/index',"IndexController@index");
Route::get('/index/hello',function(){
    return 'hello';
});

