<?php

namespace Application\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;


/**
 * Class QQ
 *
 * @package Admin\Validator
 * @author  Lijun
 * @version $Id$
 */
class QQ extends AbstractValidator
{
    const INVALID_QQ = 'invalidQQ';

    protected $messageTemplates = array(
        self::INVALID_QQ => 'QQ号码格式不正确',
    );

    public function isValid($value)
    {
        if (!preg_match('/^[1-9]\\d{4,10}$/', $value)) {
            $this->error(self::INVALID_QQ);
            return false;
        }

        return true;
    }
}