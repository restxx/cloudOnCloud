<?php

namespace Account\Model;

use Application\Model\CcUserTable;
use Application\Model\UsersProjects;
use Application\Model\UsersProjectsTable;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;


/**
 * Class UsersTable
 *
 * @package Account\Model
 * @author Xuman
 * @version $Id$
 */
class UsersTable extends AbstractTableGateway
{
    public function getByUsername($username)
    {
        $user = $this->select(['username' => $username])->current();
        return $user;
    }

    public function addUser($username, $password)
    {
        if ($this->getByUsername($username)) {
            return false;
        }
        return $this->insert(['username' => $username, 'password' => md5($password), 'bpassword' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function genUser($username, $password)
    {
        if ( ($user = $this->getByUsername($username)) != false) {
            return $user;
        }
        if ($this->insert(['username' => $username, 'password' => md5($password), 'bpassword' => password_hash($password, PASSWORD_DEFAULT)])){
            return $this->getByUsername($username);
        }
        return false;
    }

    public function changePassword($username, $oldPassword, $password)
    {
        $bpass = password_hash($password, PASSWORD_DEFAULT);
        $ret = $this->update(
            ['password' => md5($password), 'bpassword' => $bpass],
            ['username' => $username, 'password' => md5($oldPassword)]
        );

        if($ret)
        {
            CcUserTable::getInstance()->changePassword($username, $bpass);
        }
        return $ret;
    }

    public function getSubUsers($uid, $start = 0, $length = 0)
    {
        $select = new Select();
        $select->from($this->table);
        $select->where(['puid' => $uid])->order('uid desc');
        if ($length) {
            $select->limit($length)->offset($start);
        }
        return $this->selectWith($select)->toArray();
    }

    public function getSubUsersCount($uid)
    {
        return $this->select(['puid' => $uid])->count();
    }

    public function addSubUser($data)
    {
        if ($this->select(['username' => $data['username']])->count() > 0) {
            return 0;
        }
        return $this->insert($data);
    }

    public function delSubUser($uid, $subUid)
    {
        if ($ret = $this->delete(['puid' => $uid, 'uid' => $subUid])) {
            UsersProjectsTable::getInstance()->delByPUid($subUid);
        }
        return $ret;
    }

    public function costMoney($uid, $money)
    {
        if ($this->select(['uid' => $uid])->count() == 0) {
            return 0;
        }
        return $this->update(['money' => new Expression("`money`-" . $money)],['uid' => $uid]);
    }

    public function addMoney($uid, $money)
    {
        if ($this->select(['uid' => $uid])->count() == 0) {
            return 0;
        }
        return $this->update(['money' => new Expression("`money`+" . $money)],['uid' => $uid]);
    }
} 