<?php
namespace Account;

use Account\Model\GlobalTools;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e) {
        $serviceManager = $e->getApplication()->getServiceManager();
        \Zend\Validator\AbstractValidator::setDefaultTranslator($serviceManager->get('translator'));
        GlobalTools::setServiceLocator($serviceManager);
    }
    public function getConfig()
    {
        $configs = include __DIR__ . '/config/module.config.php';
        $configs += include __DIR__ . '/config/table.config.php';
        return $configs;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
