<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

return array(
    'router'             => array(
        'routes' => array(
            'home'                                => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'submit-resource'                     => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/submit-resource',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'submit-resource',
                    ),
                ),
            ),
            'yfwq'                                => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/yfwq',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'yfwq',
                    ),
                ),
            ),
            'cdn'                                 => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/cdn',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'cdn',
                    ),
                ),
            ),
            'ysjk'                                => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/ysjk',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'ysjk',
                    ),
                ),
            ),
            'yaq'                                 => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/yaq',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'yaq',
                    ),
                ),
            ),
            'fzjh'                                => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/fzjh',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'fzjh',
                    ),
                ),
            ),
            'yjk'                                 => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/yjk',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'yjk',
                    ),
                ),
            ),
            'resource'                            => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/resource',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'resource',
                    ),
                ),
            ),
            'about'                               => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/about',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'about',
                    ),
                ),
            ),
            'login'                               => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'login',
                    ),
                ),
            ),
            'register'                            => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/register',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'register',
                    ),
                ),
            ),
            'refresh-captcha'                     => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/refresh-captcha',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'refresh-captcha',
                    ),
                ),
            ),
            'manager'                             => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/manager',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'index',
                    ),
                ),
            ),
            'qiuqiu'                             => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/qiuqiu',
                    'defaults' => array(
                        'controller' => 'Application\Controller\QiuQiu',
                        'action'     => 'index',
                    ),
                ),
            ),
            'qiuqiu-cur-ping'                             => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/qiuqiu-cur-ping',
                    'defaults' => array(
                        'controller' => 'Application\Controller\QiuQiu',
                        'action'     => 'cur-ping',
                    ),
                ),
            ),
            'qiuqiu-his-ping'                             => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/qiuqiu-his-ping',
                    'defaults' => array(
                        'controller' => 'Application\Controller\QiuQiu',
                        'action'     => 'his-ping',
                    ),
                ),
            ),
            'qq-ping-data'                             => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/qq-ping-data',
                    'defaults' => array(
                        'controller' => 'Application\Controller\QiuQiu',
                        'action'     => 'ping-data',
                    ),
                ),
            ),
            'monitor-data'                        => array(
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => array(
                    'route'    => '/monitor-data',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'monitor-data',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'control'                             => array(
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => array(
                    'route'    => '/control',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'control',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'add-to-jump-server'                  => array(
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => array(
                    'route'    => '/add-to-jump-server',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'add-to-jump-server',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'get-operation-log'                   => array(
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => array(
                    'route'    => '/get-operation-log',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-operation-log',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'server-list'                         => array(
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => array(
                    'route'    => '/server-list',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'server-list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'server-operation'                    => array(
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => array(
                    'route'    => '/server-operation',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'server-operation',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'server-bind'                         => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/server-bind',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'server-bind',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'server-unbind'                       => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/server-unbind',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'server-unbind',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'detail'                              => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/detail',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'detail',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id/:hid/:rid]',
                            'constraints' => array(
                                'id'  => '[0-9]*',
                                'hid' => '[0-9a-zA-Z_-]*',
                                'rid' => '[0-9a-zA-Z_-]*'

                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'add-alert-host'                      => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/add-alert-host',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'add-alert-host',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id/:hid/:rid]',
                            'constraints' => array(
                                'id'  => '[0-9]*',
                                'hid' => '[0-9a-zA-Z_-]*',
                                'rid' => '[0-9a-zA-Z_-]*'

                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'add-physical-host'                   => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/add-physical-host',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'add-physical-host',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'jump-server-manager'                 => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/jump-server-manager',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'jump-server-manager',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'audit-log'                           => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/audit-log',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'audit-log',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'financial-report'                    => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/financial-report[/:type]',
                    'constraints' => array(
                        'type' => '[1-3]{1}'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'financial-report',
                    )
                ),
            ),
            'get-scloud-report'                   => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/get-scloud-report',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-scloud-report',
                    )
                ),
            ),
            'export-scloud-report'                => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/export-scloud-report',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'export-scloud-report',
                    )
                ),
            ),
            'get-bandwidth-report'                => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/get-bandwidth-report',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-bandwidth-report',
                    )
                ),
            ),
            'export-bandwidth-report'             => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/export-bandwidth-report',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'export-bandwidth-report',
                    )
                ),
            ),
            'export-netflow-report'               => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/export-netflow-report[/:index]',
                    'constraints' => array(
                        'index' => '[0-9]+'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'export-netflow-report',
                    )
                ),
            ),
            'add-financial-product'               => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/add-financial-product',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'add-financial-product',
                    )
                ),
            ),
            'edit-financial-product'              => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/edit-financial-product',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'edit-financial-product',
                    )
                ),
            ),
            'get-financial-product'               => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/get-financial-product/:id',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-financial-product',
                    )
                ),
            ),
            'get-netflow-grap'                    => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/get-netflow-grap',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-netflow-grap',
                    )
                ),
            ),
            'del-financial-product'               => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/del-financial-product/:id',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'del-financial-product',
                    )
                ),
            ),
            'add-financial-product-group'         => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/add-financial-product-group',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'add-financial-product-group',
                    )
                ),
            ),
            'edit-financial-product-group'        => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/edit-financial-product-group',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'edit-financial-product-group',
                    )
                ),
            ),
            'add-financial-public-group'          => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/add-financial-public-group',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'add-financial-public-group',
                    )
                ),
            ),
            'edit-financial-public-group'         => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/edit-financial-public-group',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'edit-financial-public-group',
                    )
                ),
            ),
            'get-financial-public-group'          => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/get-financial-public-group/:pid/:pid2',
                    'constraints' => array(
                        'pid'  => '\d+',
                        'pid2' => '\d+'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-financial-public-group',
                    )
                ),
            ),
            'del-financial-public-group'          => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/del-financial-public-group/:pid/:pid2',
                    'constraints' => array(
                        'pid'  => '\d+',
                        'pid2' => '\d+'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'del-financial-public-group',
                    )
                ),
            ),
            'edit-financial-product-group-prices' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/edit-financial-product-group-prices',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'edit-financial-product-group-prices',
                    )
                ),
            ),
            'get-financial-product-group'         => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/get-financial-product-group/:id',
                    'constraints' => array(
                        'id' => '\d+'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-financial-product-group',
                    )
                ),
            ),
            'del-financial-product-group'         => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/del-financial-product-group/:id',
                    'constraints' => array(
                        'id' => '\d+'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'del-financial-product-group',
                    )
                ),
            ),
            'get-financial-product-group-prices'  => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/get-financial-product-group-prices/:id',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-financial-product-group-prices',
                    )
                ),
            ),
            'get-excel'                           => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/get-excel',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-excel',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'p-get-order'                         => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/p-get-order',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-get-orders',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'p-create-order'                      => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/p-create-order',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-create-order',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'p-deal-order'                        => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/p-deal-order',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-deal-order',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'p-cancel-order' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/p-cancel-order',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-cancel-order',
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:id]',
                            'constraints' => array(
                                'id'  => '[0-9]*'
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'p-contacts-list'                     => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/p-contacts-list',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-contacts-list',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'p-contacts-create'                   => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/p-contacts-create',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-contacts-create',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'p-contacts-delete'                   => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/p-contacts-delete',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'p-contacts-delete',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'get-image-list'                      => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/get-image-list',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-image-list',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'panel'                               => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/panel',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'panel',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'get-panel-data'                      => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/get-panel-data',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-panel-data',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'get-panel-flow-detail'               => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/get-panel-flow-detail',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-panel-flow-detail',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'vnc-console'                         => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/vnc-console',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'vnc-console',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'cor-index'                           => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/cor-index',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Corporation',
                        'action'     => 'index',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'corporation'   => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/corp',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Corporation',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application'                         => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'm'                                   => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/m',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Manager',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:action[/:id]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'         => '[0-9]*'
                            ),
                            'defaults'    => array(),
                        ),
                    ),
                ),
            ),
            'download-alert-agent'                => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/download-alert-agent',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'download-alert-agent',
                    ),
                ),
            ),
            'do-test-speed'                       => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/do-test-speed',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'do-test-speed',
                    ),
                ),
            ),
            'jump-server-bind-list'               => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/jump-server-bind-list',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'jump-server-bind-list',
                    ),
                ),
            ),
            'get-alert-group'                     => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/get-alert-group',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'get-alert-group',
                    ),
                ),
            ),
            'opt-alert-user'                      => array(
                'type'    => 'segment',
                'options' => array(
                    'route'       => '/opt-alert-user/:type',
                    'constraints' => array(
                        'type' => '[1-2]{1}'
                    ),
                    'defaults'    => array(
                        'controller' => 'Application\Controller\Manager',
                        'action'     => 'opt-alert-user',
                    ),
                ),
            ),
        ),
    ),
    'service_manager'    => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories'          => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
        ),
    ),
    'translator'         => array(
        'locale'                    => 'zh_CN',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
        'translation_files'         => array(
            array(
                'type'     => 'phparray',
                'filename' => __DIR__
                    . '/../../../vendor/zendframework/zendframework/resources/languages/zh/Zend_Validate.php',
            ),
            array(
                'type'     => 'phparray',
                'filename' => __DIR__
                    . '/../../../vendor/zendframework/zendframework/resources/languages/zh/Zend_Captcha.php',
            ),
        ),
    ),
    'controllers'        => array(
        'invokables' => array(
            'Application\Controller\Index'       => Controller\IndexController::class,
            'Application\Controller\Manager'     => Controller\ManagerController::class,
            'Application\Controller\Corporation' => Controller\CorporationController::class,
            'Application\Controller\Monitor'     => Controller\MonitorController::class,
            'Application\Controller\QiuQiu'     => Controller\QiuQiuController::class,
        ),
    ),
    'view_manager'       => array(
        'doctype'             => 'HTML5',
        'not_found_template'  => 'error/404',
        'exception_template'  => 'error/index',
        'template_map'        => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'ui' => 'Application\Plugin\Ui',
        )
    ),
    // Placeholder for console routes
    'console'            => array(
        'router' => array(
            'routes' => array(),
        ),
    ),
);
