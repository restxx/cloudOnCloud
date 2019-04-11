<?php

namespace Application\Forms;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Class FinancialProductForm
 *
 * @package Application\Forms
 * @author  Lijun
 * @version $Id$
 */
class FinancialProductForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('financial-product');
        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'product_id',
            )
        );

        $this->add(
            array(
                'name' => 'name',
            )
        );
        $this->add(
            array(
                'name' => 'alias',
            )
        );
        $this->add(
            array(
                'name' => 'type',
            )
        );
        $this->add(
            array(
                'name' => 'desc',
            )
        );

        $this->add(
            array(
                'name' => 'group_id',
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
                    'name'       => 'product_id',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目ID不能为空']],
                        ['name' => 'Digits', 'options' => ['message' => '项目ID必须为数字']],
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
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目名称不能为空']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'alias',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StringToLower'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目别名不能为空']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'type',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StringToLower'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目类型不能为空']],
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
                        ['name' => 'NotEmpty', 'options' => ['message' => '项目描述不能为空']],
                    ),
                )
            )
        );
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'group_id',
                    'required'   => true,
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

        $this->setInputFilter($inputFilter);
    }
} 