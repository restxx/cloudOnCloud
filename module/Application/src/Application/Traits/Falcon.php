<?php
/**
 *
 * User: lijun1
 * Date: 16-4-8
 * Time: 下午2:14
 */

namespace Application\Traits;


trait Falcon
{
    private $domain = "http://222.73.243.55";
    private $firstAlertPort = 1234;
    private $secondAlertPort = 5050;

    private $user_prefix = "coc_";
    private $team_prefix = "coc_team_";

    public function initUser($username)
    {
        $cookie_file = getcwd() . "/data/{$username}.falcon_cookie.tmp";

        unlink($cookie_file);

        $this->loginAlert($cookie_file);

        //3 创建组 已创建就不管
        $url = "{$this->domain}:{$this->secondAlertPort}/group/create";

        $data = [
            "grp_name" => "{$this->user_prefix}" . $username
        ];

        $this->curl_post_by_cookie($url, $data, $cookie_file);

        //4 获取通用模板id
        $url = "{$this->domain}:{$this->secondAlertPort}/templates?q={$this->user_prefix}base&mine=0";

        $ret = $this->curl_get_by_cookie($url, $cookie_file);

        if ($ret) {
            preg_match("/\/template\/view\/(\d+)/", $ret, $match);
        }

        $base_tid = $match[1];

        //5 获取用户模板id
        $url = "{$this->domain}:{$this->secondAlertPort}/templates?q={$this->user_prefix}{$username}_t&mine=0";

        $ret = $this->curl_get_by_cookie($url, $cookie_file);

        if ($ret) {
            preg_match("/\/template\/view\/(\d+)/", $ret, $match);

            if ($match) {
                $user_tid = $match[1];
            } else {

                //6 创建用户模板
                $url = "{$this->domain}:{$this->secondAlertPort}/template/create";

                $data = array(
                    "name" => "{$this->user_prefix}{$username}_t",
                );
                $ret  = json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

                $user_tid = $ret["id"];
            }

            //7 用户模板继承通用模板
            $url = "{$this->domain}:{$this->secondAlertPort}/template/rename/{$user_tid}";

            $data = array(
                "name"      => "{$this->user_prefix}{$username}_t",
                "parent_id" => $base_tid
            );
            json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

        } else return false;

        //8 获取组id
        $url = "{$this->domain}:{$this->secondAlertPort}/?q={$this->user_prefix}{$username}&mine=0";

        $ret = $this->curl_get_by_cookie($url, $cookie_file);

        if ($ret) {
            preg_match("/\/group\/templates\/(\d+)/", $ret, $match);

            if ($match) {
                $grp_id = $match[1];

                //9 将用户组与用户模板绑定
                $url =
                    "{$this->domain}:{$this->secondAlertPort}/group/bind/template?grp_id={$grp_id}&tpl_id={$user_tid}";

                $this->curl_get_by_cookie($url, $cookie_file);
            }
        } else return false;

        return $grp_id;

    }

    public function addHost($ips, $gid)
    {
        $cookie_file = getcwd() . "/data/falcon_cookie.tmp";

        $url = "{$this->domain}:{$this->firstAlertPort}/auth/login";

        $data = array(
            "name"     => "{$this->user_prefix}account",
            "password" => "X@my2Qsx1",
            "ldap"     => "",
            "sig"      => "",
            "callback" => ""
        );

        $this->curl_post_by_cookie($url, $data, $cookie_file);

        $url = "{$this->domain}:{$this->secondAlertPort}/host/add";

        $data = [
            "group_id" => $gid,
            "hosts"    => $ips
        ];
        $ret  = json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

        return $ret;
    }

    public function curl_post_by_cookie($url, $data, $cookie_file = "../data/falcon_cookie.tmp")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    public function getAlertGroup($username)
    {
        $cookie_file = getcwd() . "/data/{$username}.falcon_cookie.tmp";

        unlink($cookie_file);

        //1 判断是否有注册
        $url = "{$this->domain}:{$this->firstAlertPort}/auth/register";

        $data = array(
            "name"            => "{$this->user_prefix}account",
            "password"        => "X@my2Qsx1",
            "repeat_password" => "X@my2Qsx1",
        );

        $ret = json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

        //2 已注册的话就登陆
        if (isset($ret["msg"]) && $ret["msg"] == "name is already existent") {

            $url = "{$this->domain}:{$this->firstAlertPort}/auth/login";

            $data = array(
                "name"     => "{$this->user_prefix}account",
                "password" => "X@my2Qsx1",
                "ldap"     => "",
                "sig"      => "",
                "callback" => ""
            );
            json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);
        }

        //3 获取通用模板id
        $url = "{$this->domain}:{$this->firstAlertPort}/team/users?name={$this->team_prefix}{$username}";

        $ret = json_decode($this->curl_get_by_cookie($url, $cookie_file), true);

        $users = [];
        foreach ($ret["users"] as $user) {

            list($temp, $user["name"]) = explode("{$this->user_prefix}" . $username . "_", $user["name"]);
            $users[] = $user;
        }

        return $users;

    }

    public function optAlertUser($username, $info, $type)
    {
        $cookie_file = getcwd() . "/data/{$username}.falcon_cookie.tmp";

        unlink($cookie_file);

        $this->loginAlert($cookie_file);

        //创建报警组
        $url = "{$this->domain}:{$this->firstAlertPort}/me/team/c";

        $data = [
            "name"   => "{$this->team_prefix}" . $username,
            "resume" => "云上云用户{$username}的报警组"
        ];

        $this->curl_post_by_cookie($url, $data, $cookie_file);

        if ($type == 1) {
            //新增报警用户
            $url = "{$this->domain}:{$this->firstAlertPort}/me/user/c";

            $data = array(
                "name"     => "{$this->user_prefix}{$username}_{$info["name"]}",
                "cnname"   => $info["cname"],
                "email"    => $info["email"],
                "phone"    => $info["phone"],
                "qq"       => $info["qq"],
                "password" => md5("{$this->user_prefix}{$username}_{$info["name"]}.user"),
            );

            $ret = json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

            if (isset($ret["msg"]) && $ret["msg"] == "name is already existent") {
                return ["code" => -1, "msg" => "操作失败,请确保用户名没有重复"];
            }

            //获取报警用户id
            $url =
                "{$this->domain}:{$this->firstAlertPort}/user/query?query={$this->user_prefix}{$username}_{$info["name"]}&limit=1&_=" . time(
                );

            $ret = json_decode($this->curl_get_by_cookie($url, $cookie_file), true);

            if (isset($ret["users"]) && count($ret["users"]) > 0) {
                $user    = current($ret["users"]);
                $user_id = $user["id"];
            } else {
                return ["code" => -2, "msg" => "获取用户信息失败"];
            }

            //获取更新用用户id列表
            $url = "{$this->domain}:{$this->firstAlertPort}/team/users?name={$this->team_prefix}{$username}";

            $ret = json_decode($this->curl_get_by_cookie($url, $cookie_file), true);

            if (isset($ret["users"]) && count($ret["users"]) > 0) {
                $origin_users = array_map("array_shift", $ret["users"]);
                array_push($origin_users, $user_id);
            } else {
                $origin_users = $user_id;
            }

            //获取报警组id
            $url = "{$this->domain}:{$this->firstAlertPort}/me/teams?query={$this->team_prefix}{$username}";

            $ret = $this->curl_get_by_cookie($url, $cookie_file);

            if ($ret) {
                preg_match("/\/target-team\/edit\?id=(\d+)/", $ret, $match);
            } else return ["code" => -4, "msg" => "获取报警组信息失败"];

            $team_id = $match[1];

            //添加用户到报警组中
            $url = "{$this->domain}:{$this->firstAlertPort}/target-team/edit";

            $data = array(
                "resume" => "云上云用户{$username}的报警组",
                "users"  => is_array($origin_users) ? implode(",", $origin_users) : $origin_users,
                "id"     => $team_id,
            );

            $this->curl_post_by_cookie($url, $data, $cookie_file);

            return ["code" => 0, "msg" => "添加成功"];

        } else if ($type == 2) {
            //编辑报警用户

            $url = "{$this->domain}:{$this->firstAlertPort}/auth/login";

            $data = array(
                "name"     => "{$this->user_prefix}{$username}_{$info["name"]}",
                "password" => md5("{$this->user_prefix}{$username}_{$info["name"]}.user"),
                "ldap"     => "",
                "sig"      => "",
                "callback" => ""
            );

            $ret = json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

            $url = "{$this->domain}:{$this->firstAlertPort}/me/profile";

            $data = array(
                "cnname" => $info["cname"],
                "email"  => $info["email"],
                "phone"  => $info["phone"],
                "qq"     => $info["qq"],
            );

            json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

            return ["code" => 0, "msg" => "编辑成功"];

        } else return ["code" => -5, "msg" => "操作失败"];
    }

    public function loginAlert($cookie_file)
    {
        //1 判断是否有注册
        $url = "{$this->domain}:{$this->firstAlertPort}/auth/register";

        $data = array(
            "name"            => "{$this->user_prefix}account",
            "password"        => "X@my2Qsx1",
            "repeat_password" => "X@my2Qsx1",
        );

        $ret = json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);

        //2 已注册的话就登陆
        if (isset($ret["msg"]) && $ret["msg"] == "name is already existent") {

            $url = "{$this->domain}:{$this->firstAlertPort}/auth/login";

            $data = array(
                "name"     => "{$this->user_prefix}account",
                "password" => "X@my2Qsx1",
                "ldap"     => "",
                "sig"      => "",
                "callback" => ""
            );
            json_decode($this->curl_post_by_cookie($url, $data, $cookie_file), true);
        }
    }

    public function curl_get_by_cookie($url, $cookie_file = "../data/falcon_cookie.tmp")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
} 