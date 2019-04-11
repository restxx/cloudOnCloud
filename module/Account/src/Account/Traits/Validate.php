<?php
/**
 *
 * User: xuman
 * Date: 16-4-29
 * Time: 下午5:30
 */

namespace Account\Traits;


trait Validate
{
    public function isSafePassword($account, $password)
    {
        if ($account == $password) //密码不能和帐号相同
        {
            return false;
        }
        if (strlen($password) < 8) //密码长度不能小于8位
        {
            return false;
        }

        if (!preg_match('/[A-Za-z0-9_\W]{8,}/', $password)
            || !preg_match('/[A-Z]{1}/', $password)
            || !preg_match('/[a-z]{1}/', $password)
            || !preg_match('/[0-9]{1}/', $password)
            || !preg_match('/[\W_]{1}/', $password)
        ) {
            return false;
        }
        if (preg_match('/^(m)*ztgame.{1}123$/i', $password)) {
            return false;
        }

        return true;
    }
} 