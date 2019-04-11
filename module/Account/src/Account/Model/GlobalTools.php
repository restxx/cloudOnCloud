<?php

namespace Account\Model;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 * Class GlobalTools
 * @package Account
 * @author Xuman
 * @version $Id$
 */
class GlobalTools
{
    static protected $serviceLocator = null;

    /**
     * @return null|ServiceLocatorInterface
     */
    static public function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    static public function setServiceLocator(ServiceLocatorInterface $serviceManager)
    {
        self::$serviceLocator = $serviceManager;
    }

    static function encrypt($str, $key='o0o00o00')
    {
        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        return base64_encode(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));
    }
    static function decrypt($str, $key='o0o00o00')
    {
        $str = base64_decode($str);
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = ord($str[($len = strlen($str)) - 1]);
        return substr($str, 0, strlen($str) - $pad);
    }
}