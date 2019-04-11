<?php

namespace Application\Forms;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Class FinancialProductGroupForm
 *
 * @package Application\Forms
 * @author  Lijun
 * @version $Id$
 */
class FinancialProductGroupForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('financial-product-group');
        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'group_id',
            )
        );

        $this->add(
            array(
                'name' => 'name',
            )
        );

        $this->add(
            array(
                'name' => 'desc',
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
                    'name'       => 'group_id',
                    'required'   => false,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目组ID不能为空']],
                        ['name' => 'Digits', 'options' => ['message' => '项目组ID必须为数字']],
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
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目组名称不能为空']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'desc',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目组描述不能为空']],
                    ),
                )
            )
        );

        $this->setInputFilter($inputFilter);
    }
} 