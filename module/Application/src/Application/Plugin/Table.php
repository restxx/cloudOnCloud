<?php

namespace Application\Plugin;


/**
 * Class Table
 *
 * @package Application\Plugin
 * @author  Lijun
 * @version $Id$
 */
class Table
{
    public function parseSort($cols, $sort)
    {
        $cols = array_values($cols);
       
        $newSort = [];
        foreach ($sort as $key => $val) {
            $newSort[$cols[$key]] = $val;
        }

        return $newSort;
    }

    public function parseWhere($cols, $where, $like = [], $date = [])
    {
        $newWhere = [];
        $newLike  = [];
        $newDate  = [];

        $cols = array_values($cols);

        if (!empty($where)) {
            foreach ($where as $key => $val) {

                if (strlen($val) > 0) {
                    if (in_array($cols[$key], $like)) {
                        $newLike[$cols[$key]] = "%$val%";
                    } else if (in_array($cols[$key], $date)) {
                        $newDate = json_decode($val, true);
                    } else {
                        $newWhere[$cols[$key]] = $val;
                    }
                } else break;
            }
        }

        return [
            'where' => $newWhere,
            'like'  => $newLike,
            'date'  => $newDate,
        ];
    }

    public static function sqlSafeString($str)
    {
        if (is_array($str)) {
            throw new \Exception("参数异常", -100);
        }

        if (!get_magic_quotes_gpc()) {
            $str = addslashes($str);
        }
        $str = str_replace("%", "\%", $str);
        return $str;
    }

    public static function stringToInt($val, $default = 0)
    {
        $val = trim($val);

        if (is_int($val)) return intval($val);
        if (is_numeric($val)) {
            if (preg_match("/^[-+]?\d+$/", $val)) {
                return intval($val);
            }
        }
        return $default;
    }

    public static function stringToUInt($val, $default = 0)
    {
        $r = self::stringToInt($val, $default);
        return $r < 0 ? $default : $r;
    }

    public static function stringToNumeric($val, $default = 0)
    {
        $val = trim($val);
        return is_numeric($val) ? ($val + 0) : $default;
    }

    public static function stringToDecimal($val, $decimal_point = 2, $default = "0.0")
    {
        $val = trim($val);
        if (is_numeric($val) && ((string)(float)$val === (string)$val)) {
            return (float)number_format($val, $decimal_point, '', '');
        }
        return $default;
    }
} 