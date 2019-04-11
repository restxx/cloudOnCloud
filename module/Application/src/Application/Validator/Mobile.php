<?php

namespace Application\Validator;

use Zend\Validator\AbstractValidator;


/**
 * Class Mobile
 *
 * @package Gzfextra\Validator
 * @author  Xiemaomao
 * @version $Id$
 */
class Mobile extends AbstractValidator
{
    const INVALID_MOBILE = 'invalidMobile';

    protected $messageTemplates = array(
        self::INVALID_MOBILE => '手机号码格式不正确',
    );

    public function isValid($value)
    {
        if (!preg_match('/^1\d{10}$/', $value)) {
            $this->error(self::INVALID_MOBILE);
            return false;
        }

        return true;
    }
} 