<?php
/**
 * User: zhoubin
 * Date: 2017/5/19
 * Time: 12:17
 */

return [
    //sso域登录
    'passport'		=> [
        'url'	=> 'http://sso.ztgame.com/passport/soap/soapserver_passport.php?wsdl',
        'source_system_code'	=>	'scloudm',
        'cookie_expire'			=>	time()+86400,
        'cookie_path'			=>	'/',
        'cookie_domian'			=>	'.ztgame.com',
        'cookie_secure'			=>	0,
    ],
];

if (file_exists(__DIR__.'/params-local.php')) $config = array_merge($config,require(__DIR__.'/params-local.php'));
return $config;