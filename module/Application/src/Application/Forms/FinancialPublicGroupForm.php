<?php

namespace Application\Forms;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Class FinancialPublicGroupForm
 *
 * @package Application\Forms
 * @author  Lijun
 * @version $Id$
 */
class FinancialPublicGroupForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('financial-public-group');
        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'product_id',
            )
        );

        $this->add(
            array(
                'name' => 'product_id2',
            )
        );

        $this->add(
            array(
                'name' => 'percent',
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
                        ['name' => 'NotEmpty', 'options' => ['message' => '公共项目ID不能为空']],
                        ['name' => 'Digits', 'options' => ['message' => '公共项目ID必须为数字']],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'product_id2',
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
                    'name'       => 'percent',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '占比不能为空']],
                        ['name' => 'Digits', 'options' => ['message' => '占比必须为数字']],
                    ),
                )
            )
        );


        $this->setInputFilter($inputFilter);
    }
} 