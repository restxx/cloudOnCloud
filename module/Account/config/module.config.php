<?php
use Account\Controller\IndexController;
use Gzfextra\Db\TableGateway\TableGatewayAbstractServiceFactory;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;

return array(

    'controllers' => array(
        'invokables' => array(
            'Account\Controller\Index' => IndexController::class
        ),
    ),
    'router' => array(
        'routes' => array(
            'login-index' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login-index',
                    'defaults' => array(
                        'controller' => 'Account\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'register-index' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/register-index',
                    'defaults' => array(
                        'controller' => 'Account\Controller\Index',
                        'action'     => 'register',
                    ),
                ),
            ),
            'login-out' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login-out',
                    'defaults' => array(
                        'controller' => 'Account\Controller\Index',
                        'action'     => 'login-out',
                    ),
                ),
            ),
            'change-password' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/change-password',
                    'defaults' => array(
                        'controller' => 'Account\Controller\Index',
                        'action'     => 'change-password',
                    ),
                ),
            ),

            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'account' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/account',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Account\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            //'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'account/index/index' => __DIR__ . '/../view/account/index/index.phtml',
            //'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array('ViewJsonStrategy')
    ),
    'service_manager' => [
        'invokables' => [
            'Zend\Authentication\AuthenticationService' => 'Zend\Authentication\AuthenticationService'
        ],
        'aliases' => [
            'auth' => 'Zend\Authentication\AuthenticationService'
        ],
        'abstract_factories' => array(
            AdapterAbstractServiceFactory::class
        ),
        'factories' => [
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ]
    ]
);