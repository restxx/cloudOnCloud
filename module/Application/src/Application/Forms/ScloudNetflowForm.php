<?php

namespace Application\Forms;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Class ScloudNetflowForm
 *
 * @package Application\Forms
 * @author  Lijun
 * @version $Id$
 */
class ScloudNetflowForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('scloud-netflow');
        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'net_project',
            )
        );

        $this->add(
            array(
                'name' => 'net_idc',
            )
        );

        $this->add(
            array(
                'name' => 'net_month',
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
                    'name'       => 'net_project',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目不能为空']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'net_idc',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '机房不能为空']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'net_month',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '月份不能为空']],
                    ),
                )
            )
        );

        $this->setInputFilter($inputFilter);
    }
} 