<?php
namespace NetUp\Api;
include('NetUp\Api\NetUpApi.php');


$api =  new NetUpApi('admin','Qwerty123');

$api->Login();
var_dump($api->getMosaicList());
?> 