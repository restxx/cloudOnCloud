<?php

namespace Application\Forms;

use Application\Validator\Mobile;
use Application\Validator\QQ;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Class AlertUserForm
 *
 * @package Application\Forms
 * @author  Lijun
 * @version $Id$
 */
class OptAlertUserForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('opt-alert-user');
        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'id',
            )
        );

        $this->add(
            array(
                'name' => 'name',
            )
        );
        $this->add(
            array(
                'name' => 'cname',
            )
        );

        $this->add(
            array(
                'name' => 'email',
            )
        );
        $this->add(
            array(
                'name' => 'phone',
            )
        );

        $this->add(
            array(
                'name' => 'qq',
            )
        );
    }

    public function loadInputFilter()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'qq',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        new QQ()
                    ),
                )
            )
        );
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'phone',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StringToLower'),
                    ),
                    'validators' => array(
                        new Mobile()
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'email',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '邮箱不能为空']],
                        [
                            'name'    => 'EmailAddress',
                            'options' => [
                                'message' => '邮箱格式不正确'
                            ]
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'name',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '用户名不能为空']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'cname',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '中文名不能为空']],
                    ),
                )
            )
        );

        $this->setInputFilter($inputFilter);
    }
} 