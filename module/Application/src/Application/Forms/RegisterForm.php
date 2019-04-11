<?php

namespace Application\Forms;

use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\Element;

/**
 * Class Register
 *
 * @package Account\Forms
 * @author  Xuman
 * @version $Id$
 */
class RegisterForm extends Form
{
    protected $captcha;

    public function __construct(CaptchaAdapter $captcha)
    {

        parent::__construct();

        $this->captcha = $captcha;

// add() can take either an Element/Fieldset instance,
// or a specification, from which the appropriate object
// will be built.

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Captcha',
                'name'       => 'captcha',
                'options'    => array(
                    'label'   => '输入验证码',
                    'captcha' => $this->captcha,
                ),
                'attributes' =>
                    [
                        'class'       => 'form-control',
                        'id'          => 'register-captcha',
                        'placeholder' => '输入验证码',
                        'maxLength'   => 4,
                        'autoComplete'=> 'off',
                        'style'         =>  'width:200px;float:left;margin-right:23px;'
                    ]
            )
        );
        $this->add(new Element\Csrf('security'));

// We could also define the input filter here, or
// lazy-create it in the getInputFilter() method.
    }
}