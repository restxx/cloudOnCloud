<?php

namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;


/**
 * Class Ui
 *
 * @package Application\Plugin
 * @author  Lijun
 * @version $Id$
 */
class Ui extends AbstractPlugin
{
    private $data = [];

    public function __invoke()
    {
        $post       = $_POST;
        $sortCols   = [];
        $searchCols = [];
        foreach ($post as $key => $val) {

            $val = Table::sqlSafeString($val);

            if (substr($key, 0, 8) == 'iSortCol') {
                $index          = substr($key, 9);
                $sortCols[$val] = $post['sSortDir_' . $index];
            }

            if (substr($key, 0, 11) == 'bSearchable') {

                $index = substr($key, 12);

                if (strlen($post['sSearch_' . $index]) > 0) {

                    $searchCols[$index] = $post['sSearch_' . $index];
                };
            }
        }

        $this->data['where']  = $searchCols;
        $this->data['sort']   = $sortCols;
        $this->data['offset'] = intval($post['iDisplayStart']);
        $this->data['limit']  = intval($post['iDisplayLength']);

        return $this;

    }

    public function getData()
    {
        return $this->data;
    }

    public function getSort()
    {
        return $this->data['sort'];
    }

    public function getWhere()
    {
        return $this->data['where'];
    }

    public function getOffset()
    {
        return $this->data['offset'];
    }

    public function getLimit()
    {
        return $this->data['limit'];
    }
} 