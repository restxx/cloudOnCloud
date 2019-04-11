<?php

namespace Application\Model;

use Account\Model\AbstractTableGateway;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;


/**
 * Class ProjectsTable
 *
 * @package Application\Model
 * @author  Xuman
 */
class ProjectsTable extends AbstractTableGateway
{
    public function add($name, $username)
    {
        try{
            $affects = $this->insert(['name' => $name, 'username' => $username]);
        } catch( \Exception $e) {
            $affects = 0;
        }
        return $affects;

    }

    public function del($id, $username){
        if($ret = $this->delete(['id' => $id, 'username' => $username]))
        {
            UsersProjectsTable::getInstance()->delByPid($id);
        }
        return $ret;
    }

    public function bind($id, $mid, $username)
    {
        return $this->update(['mid' => $mid],['id' => $id, 'username' => $username]);
    }

    public function setBalance($id, $balance, $username)
    {
        return $this->update(['balance' => $balance], ['id' => $id, new Expression('mid is not null'), 'username' => $username]);
    }

    public function getByUsername($username,$start=0, $length=0)
    {

        $counts = $this->select(['username' => $username])->count();
        if($counts > 0)
        {
            $select = new Select();
            $select->from($this->table);
            $select->where(['username' => $username])->order('id desc');
            if($length)
            {
                $select->limit($length)->offset($start);
            }
            return [$counts, $this->selectWith($select)->toArray()];
        }
        return [0,[]];
    }

    public function getAllByUsername($username,$check=false) {
        if($check) {
            return $this->select(['username' => $username, new Expression('mid is not null')])->toArray();
        }
        return $this->select(['username' => $username])->toArray();
    }

    public function getNamesByIds($idList)
    {
        if(!$idList) return [];
        $result = $this->select([new Expression('`id` in (' . implode(',', $idList) . ')')]);
        $list = [];
        foreach($result as $item)
        {
            $list[$item['id']] = $item['name'];
        }
        return $list;
    }

    public function bindProjects($id, $fid, $fpid, $name,$username)
    {
        return $this->update(['mid' => $fpid, 'mfid' => $fid, 'mname' => $name], ['id' => $id, 'username' => $username]);
    }

    public function getFPidByPid($pList)
    {
        $ret = $this->select(['id' => $pList])->toArray();
        $list = [];
        foreach($ret as $item)
        {
            $list[$item['mfid']][] = $item['mid'];
        }
        return $list;
    }

    public function getMappedProjectInfo($id, $username)
    {
        $ret = $this->select(['id' => $id, 'username' => $username])->toArray();
        if($ret) return current($ret);
        return $ret;
    }
} 