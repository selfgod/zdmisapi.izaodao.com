<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/30
 * Time: 下午10:59
 */
require EASYSWOOLE_ROOT . '/Config/environment.php';
$config = require EASYSWOOLE_ROOT . '/Config/' . ENVIRONMENT . '.php';
return $config;