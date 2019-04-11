<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

$config = array(
    'db'              => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=twocloud;host=172.29.211.187',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'username'       => 'twocloud',
        'password'       => 'mobile'
    ),
    'db2'              => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=cc_openSource;host=172.29.211.241 ',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'username'       => 'blueking',
        'password'       => 'blueking@!123'
    ),
    'service_manager' => array(
        'factories' => array(),
    ),
    'firm_config'     => [
        100000 => [
            'name'      => '星云 - IDC',
            'desc'      => '星云云主机',
            'alias'     => 'sCloud',
            'com'       => 'Giant',
            'authForm'  => [
                'username' => '用户名',
                'password' => '密码'
            ],
            'invokable' => '\Application\FirmApiAdapter\SCloud'
        ],
        100001 => [
            'name'      => '腾讯云',
            'desc'      => '腾讯云服务器（CVM, Cloud Virtual Machine）是指运行在腾讯云数据中心里的服务器， 它是一种可以弹性伸缩的计算服务，您可以根据业务需要使用它来构建和托管您的软件系统。<br>请通过腾讯云官方平台（云产品->监控与管理->云API密钥）获取SECRET_ID和SECRET_KEY并填写在下面的表单中提交。',
            'alias'     => 'qCloud',
            'com'       => 'Tencent',
            'authForm'  => [
                'secretId'  => 'SECRET ID',
                'secretKey' => 'SECRET KEY'
            ],
            'invokable' => '\Application\FirmApiAdapter\QCloud'
        ],
        100002 => [
            'name'      => '阿里云',
            'desc'      => '',
            'alias'     => 'pHost',
            'com'       => 'Alibaba',
            'authForm'  => [
                'AccessKeyId'     => '密钥ID(Access Key ID)',
                'AccessKeySecret' => '密钥(Access Key Secret)'
            ],
            'invokable' => '\Application\FirmApiAdapter\ACloud'
        ],
        100003 => [
            'name'      => 'UCloud',
            'desc'      => '',
            'alias'     => 'others',
            'com'       => 'UCloud',
            'authForm'  => [
                'PublicKey' => '用户公钥',
                'PrivateKey' => '用户私钥'
            ],
            'invokable' => '\Application\FirmApiAdapter\UCloud'
        ],
        100004 => [
            'name'      => '物理机',
            'desc'      => '星云物理机',
            'alias'     => 'pHost',
            'com'       => 'Giant',
            'authForm'  => [
                'username' => '用户名',
                'password' => '密码'
            ],
            'invokable' => '\Application\FirmApiAdapter\PhysicalHost'
        ],
        100005 => [
            'name'      => 'CDN',
            'desc'      => '云上云CDN',
            'alias'     => 'pHost',
            'com'       => 'Giant',
            'authForm'  => [
                'username' => '用户名',
                'password' => '密码'
            ],
            'invokable' => '\Application\FirmApiAdapter\Cdn'
        ],
        100006 => [
            'name'      => 'AWS',
            'desc'      => 'AWS',
            'alias'     => 'AWS',
            'com'       => 'Giant',
            'authForm'  => [
                'access_key' => 'Access Key',
                'secret_key' => 'Secret Key'
            ],
            'invokable' => '\Application\FirmApiAdapter\Aws'
        ],
        100007 => [
            'name'      => '星云 - 研发',
            'desc'      => '星云 - 研发主机',
            'alias'     => 'sCloudDev',
            'com'       => 'Giant',
            'authForm'  => [
                'username' => '用户名',
                'password' => '密码'
            ],
            'invokable' => '\Application\FirmApiAdapter\SCloudDev'
        ],
    ],
    'view_manager' => array(
        'display_not_found_reason' => false,
        'display_exceptions'       => false
    ),
    'firm_hosts' => require 'FirmHostConfig.php',
    'os_types'   => require 'OSTypeConfig.php',
    'params'     => require 'params.php',
);
if (file_exists(__DIR__.'/global-local.php')) $config = array_merge($config,require(__DIR__.'/global-local.php'));
return $config;