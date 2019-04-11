<?php

namespace Application\Forms;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/**
 * Class FinancialProductGroupPricesForm
 *
 * @package Application\Forms
 * @author  Lijun
 * @version $Id$
 */
class FinancialProductGroupPricesForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('financial-product-group-prices');
        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'group_id',
            )
        );

        $this->add(
            array(
                'name' => 'jgf',
            )
        );

        $this->add(
            array(
                'name' => 'zhfwcb',
            )
        );

        $this->add(
            array(
                'name' => 'syshbgp',
            )
        );

        $this->add(
            array(
                'name' => 'sybjbgp',
            )
        );
        $this->add(
            array(
                'name' => 'sysh',
            )
        );

        $this->add(
            array(
                'name' => 'sybj',
            )
        );

        $this->add(
            array(
                'name' => 'sytj',
            )
        );

        $this->add(
            array(
                'name' => 'syltsh',
            )
        );

        $this->add(
            array(
                'name' => 'syltbj',
            )
        );

        $this->add(
            array(
                'name' => 'sylttj',
            )
        );

        $this->add(
            array(
                'name' => 'cdn',
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
                    'name'       => 'jgf',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '机柜费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value) && $value < 0) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '机柜费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'zhfwcb',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '综合服务成本不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value) && $value < 0) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '综合服务成本必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'syshbgp',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '上海BGP带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '上海BGP带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'sybjbgp',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '北京BGP带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '北京BGP带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'sysh',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '上海带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '上海带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'sybj',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '北京带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '北京带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'sytj',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '天津带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '天津带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'syltsh',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '上海联通带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '上海联通带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'syltbj',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '北京联通带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '北京联通带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'sylttj',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => '天津联通带宽费不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => '天津联通带宽费必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'       => 'cdn',
                    'required'   => true,
                    'filters'    => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        ['name' => 'NotEmpty', 'options' => ['message' => 'CDN费用不能为空']],
                        [
                            'name'    => 'callback',
                            'options' => array(
                                'callback' => function ($value) {
                                        if (!is_numeric($value)) {
                                            return false;
                                        }
                                        return true;
                                    },
                                'message'  => 'CDN费用必须为大于0数字'
                            ),
                        ],
                    ),
                )
            )
        );

        $this->setInputFilter($inputFilter);
    }
} 