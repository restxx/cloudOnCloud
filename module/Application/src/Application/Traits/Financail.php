<?php
/**
 *
 * User: lijun1
 * Date: 16-5-18
 * Time: 下午5:22
 *
 * modify: DongGuanghua
 * Time:   2019-4-11 17:00
 */

namespace Application\Traits;

use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;
use PHPExcel_RichText;

trait Financail
{
    private $ctitle = "上海巨人统平网络科技有限公司";
    private $mapping = [
        'cdn费用'  => 'cdn',
        '机柜费'    => 'jgf',
        '物理机综合服务成本' => 'zhfwcb',
        '私有云综合服务成本' => 'vmzhfwcb',
        'B'      => 'syshbgp',
        'E'      => 'sysh',
        'H'      => 'syltbj',
        'K'      => 'sybjbgp',
        'N'      => 'sylttj'
    ];

    private $excelTitle = [
        '上海BGP' => 'syshbgp',
        '上海单线'  => 'sysh',
        '北京联通'  => 'syltbj',
        '北京BGP' => 'sybjbgp',
        '天津联通'  => 'sylttj',
    ];

    public function format_excel2array($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            die('file not exists');
        }
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        $PHPExcel     = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet($sheet);
        $allColumn    = $currentSheet->getHighestColumn();
        $allRow       = $currentSheet->getHighestRow();
        $data         = array();
        for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {
            for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getCalculatedValue();
                if ($cell instanceof PHPExcel_RichText) {
                    $cell = $cell->__toString();
                }
                $data[$rowIndex][$colIndex] = $cell;
            }
        }
        return $data;
    }

    // 获取cdn 带宽费
    public function cdnReport($cdate, $products = null, $public_group = null)
    {
        $datas = [];

        $api = "/cdn_api/get_price";

        $host = "http://newapicloudcdn.ztgame.com.cn:5000";

        $url = $host . $api . '/' . date("Y-m", strtotime("-1 month"));
        $result = json_decode($this->get($url), true);

        $mapping = $this->mapping;

        if ($result['code'] == 200) {
            $details = $result['detail'];

            array_shift($details);

            foreach ($details as $detail) {
                $temp               = [];
                $temp['product_id'] = $detail['domain'];
                $temp['name']       = $detail['project_name'];
                $temp['quantity']   = sprintf('%.2f', $detail['price']);
                $type               = "CDN费用";

                $product = current(
                    array_filter(
                        $products, function ($val) use ($temp, $mapping) {
                            return $val['product_id'] == $temp['product_id'];
                        }
                    )
                );

                $groups = array_filter(
                    $public_group, function ($val) use ($temp) {
                        return $val['product_id'] == $temp['product_id'];
                    }
                );

                if ($groups) {

                    foreach ($groups as $group) {

                        $temp2    = [];
                        $product2 = current(
                            array_filter(
                                $products, function ($val) use ($group) {
                                    return $val['product_id'] == $group['product_id2'];
                                }
                            )
                        );

                        $price2 = $product2[$mapping[strtolower($type)]];

                        $temp2['type']       = $type;
                        $temp2['title']      = $this->ctitle;
                        $temp2['date']       = $cdate;
                        $temp2['price']      = sprintf('%.2f', $price2);
                        $temp2['product_id'] = $product2['product_id'];
                        $temp2['name']       = $product2['name'];
                        $temp2['quantity']   = sprintf('%.2f', bcmul($temp['quantity'], $group['percent'] / 100, 2));
                        $temp2['total']      = sprintf('%.2f', bcmul($temp2['quantity'], $price2, 2));

                        $datas[] = $temp2;
                    }
                } else {
                    $temp['type']  = $type;
                    $temp['title'] = $this->ctitle;
                    $temp['date']  = $cdate;
                    $price         = $product[$mapping[strtolower($type)]];
                    $temp['price'] = sprintf('%.2f', $price);
                    $temp['total'] = sprintf('%.2f', bcmul($temp['quantity'], $price, 2));
                    $datas[]       = $temp;
                }
            }
        }

        return $datas;
    }

    //获取手游带宽费
    public function netReport($cdate, $products = null, $public_group = null)
    {
        $datas = [];

        $filename = "mobile_net.xlsx";
        $filePath =
            PHP_OS == "WINNT" ? "data/" . $filename : "/tmp/" . $filename;

        $targetFile = "data/report/mobile_net." . date("Y.m", strtotime("-1 month")) . ".xlsx";

        if (!is_file($targetFile)) {
            copy($filePath, $targetFile);
            $lines = $this->format_excel2array($filePath);
        } else {
            $lines = $this->format_excel2array($targetFile);
        }
        $mapping = $this->mapping;
        foreach ($lines as $line) {
            $temp = [];
            if ($line['A'] == "项目" || !$line['A']) {
                continue;
            }

            $name = $line['A'];
            $type = '手游带宽费';

            $product = current(
                array_filter(
                    $products, function ($val) use ($name) {
                        return $val['name'] == $name;
                    }
                )
            );

            $lineB_price = sprintf('%.2f', bcmul($line['B'], $product[$mapping["B"]] / 100, 2));
            $lineE_price = sprintf('%.2f', bcmul($line['E'], $product[$mapping["E"]] / 100, 2));
            $lineH_price = sprintf('%.2f', bcmul($line['H'], $product[$mapping["H"]] / 100, 2));
            $lineK_price = sprintf('%.2f', bcmul($line['K'], $product[$mapping["K"]] / 100, 2));
            $lineN_price = sprintf('%.2f', bcmul($line['N'], $product[$mapping["N"]] / 100, 2));

            $temp['type']       = $type;
            $temp['title']      = $this->ctitle;
            $temp['date']       = $cdate;
            $temp['price']      = "";
            $temp['product_id'] = $product['product_id'];
            $temp['name']       = $product['name'];
            $temp['quantity']   = "";
            $temp['total']      =
                sprintf('%.2f', $lineB_price + $lineE_price + $lineH_price + $lineK_price + $lineN_price);
            $datas[]            = $temp;
        }

        return $datas;
    }

    //获取机柜费
    public function jgReport($cdate, $products = null, $public_group = null)
    {
        $datas = [];

        $filename = "maximo_project_info.txt";
        $filePath =
            PHP_OS == "WINNT" ? "data/" . $filename : "/tmp/" . $filename;
        $targetFile = "data/report/maximo_project_info." . date("Y.m", strtotime("-1 month")) . ".txt";
        if (!is_file($targetFile)) {
            copy($filePath, $targetFile);
            $lines = file($filePath);
        } else {
            $lines = file($targetFile);
        }

        $mapping = $this->mapping;

        foreach ($lines as $line) {
            $line = rtrim($line);
            $temp = [];

            list($temp['name'], $temp['product_id'], $quantity) = explode("\t", $line);

            $temp['quantity'] = sprintf('%.2f', $quantity);

            if (strtolower($temp['product_id']) == "project" && strtolower($temp['name']) == "conf_18") continue;
            if (strtolower($temp['product_id']) == "conf_18" && strtolower($temp['name']) == "project") continue;
            if (strtolower($temp['product_id']) == "none" && strtolower($temp['name']) == "none") continue;

            $type = '机柜费';

            $product = current(
                array_filter(
                    $products, function ($val) use ($temp) {
                        return $val['product_id'] == $temp['product_id'] || $val['name'] == $temp['name'];
                    }
                )
            );

            if ($product) {
                $temp['name']       = $product['name'];
                $temp['product_id'] = $product['product_id'];
            }

            $groups = array_filter(
                $public_group, function ($val) use ($temp) {
                    return $val['product_id'] == $temp['product_id'];
                }
            );

            if ($groups) {

                foreach ($groups as $group) {

                    $temp2    = [];
                    $product2 = current(
                        array_filter(
                            $products, function ($val) use ($group) {
                                return $val['product_id'] == $group['product_id2'];
                            }
                        )
                    );

                    $price2 = $product2[$mapping[strtolower($type)]];

                    $temp2['type']       = $type;
                    $temp2['title']      = $this->ctitle;
                    $temp2['date']       = $cdate;
                    $temp2['price']      = sprintf('%.2f', $price2);
                    $temp2['product_id'] = $product2['product_id'];
                    $temp2['name']       = $product2['name'];
                    $temp2['quantity']   = sprintf('%.2f', bcmul($temp['quantity'], $group['percent'] / 100, 2));
                    $temp2['total']      = sprintf('%.2f', bcmul($temp2['quantity'], $price2, 2));

                    $datas[] = $temp2;
                }
            } else {
                $temp['title'] = $this->ctitle;
                $temp['date']  = $cdate;
                $temp['type']  = $type;
                $price         = $product[$mapping[strtolower($type)]];
                $temp['price'] = sprintf('%.2f', $price);
                $temp['total'] = sprintf('%.2f', bcmul($temp['quantity'], $price, 2));
                $datas[]       = $temp;
            }
        }

        return $datas;
    }

    //获取综合服务成本
    public function zhfwcbReport($cdate, $products = null, $public_group = null)
    {
        $datas = [];

        $filename = "maximo_project_info.txt";
        $filePath =
            PHP_OS == "WINNT" ? "data/" . $filename : "/tmp/" . $filename;

        $targetFile = "data/report/maximo_project_info." . date("Y.m", strtotime("-1 month")) . ".txt";
        if (!is_file($targetFile)) {
            copy($filePath, $targetFile);
            $lines = file($filePath);
        } else {
            $lines = file($targetFile);
        }

        $mapping = $this->mapping;

        foreach ($lines as $line) {
            $line = rtrim($line);
            $temp = [];

            list($temp['name'], $temp['product_id'], $quantity) = explode("\t", $line);

            $temp['quantity'] = sprintf('%.2f', $quantity);

            if (strtolower($temp['product_id']) == "project" && strtolower($temp['name']) == "conf_18") continue;
            if (strtolower($temp['product_id']) == "conf_18" && strtolower($temp['name']) == "project") continue;
            if (strtolower($temp['product_id']) == "none" && strtolower($temp['name']) == "none") continue;

            $type = "物理机综合服务成本";

            $product = current(
                array_filter(
                    $products, function ($val) use ($temp) {
                        return $val['product_id'] == $temp['product_id'] || $val['name'] == $temp['name'];
                    }
                )
            );

            if ($product) {
                $temp['name']       = $product['name'];
                $temp['product_id'] = $product['product_id'];
            }

            $groups = array_filter(
                $public_group, function ($val) use ($temp) {
                    return $val['product_id'] == $temp['product_id'];
                }
            );

            if ($groups) {

                foreach ($groups as $group) {

                    $temp2    = [];
                    $product2 = current(
                        array_filter(
                            $products, function ($val) use ($group) {
                                return $val['product_id'] == $group['product_id2'];
                            }
                        )
                    );

                    $price2 = $product2[$mapping[strtolower($type)]];

                    $temp2['title']      = $this->ctitle;
                    $temp2['date']       = $cdate;
                    $temp2['type']       = $type;
                    $temp2['price']      = sprintf('%.2f', $price2);
                    $temp2['product_id'] = $product2['product_id'];
                    $temp2['name']       = $product2['name'];
                    $temp2['quantity']   = sprintf('%.2f', bcmul($temp['quantity'], $group['percent'] / 100, 2));
                    $temp2['total']      = sprintf('%.2f', bcmul($temp2['quantity'], $price2, 2));

                    $datas[] = $temp2;
                }
            } else {
                $temp['title'] = $this->ctitle;
                $temp['date']  = $cdate;
                $temp['type']  = $type;
                $price         = $product[$mapping[strtolower($type)]];
                $temp['price'] = sprintf('%.2f', $price);
                $temp['total'] = sprintf('%.2f', bcmul($temp['quantity'], $price, 2));
                $datas[]       = $temp;
            }
        }

        return $datas;
    }

    //星云费用
    public function scloudNetReport($cdate, $products = null, $public_group = null)
    {
        $datas = [];

        //获取星云费用
        $filename = "cloud_bill_info." . date("Y.m", strtotime("-1 month")) . ".log";
        $filePath =
            PHP_OS == "WINNT" ? "data/" . $filename : "/tmp/" . $filename;

        $targetFile = "data/report/cloud_bill_info." . date("Y.m", strtotime("-1 month")) . ".log";
        if (!is_file($targetFile)) {
            if (!is_file($filePath)) {
                throw new \Exception('获取星云物理机数据失败', -2);
            }
            copy($filePath, $targetFile);
            $lines = file($filePath);
        } else {
            $lines = file($targetFile);
        }

        foreach ($lines as $line) {
            if (PHP_OS != "WINNT") {
                $line = mb_convert_encoding(trim($line), "UTF-8", "GBK");
            }
            $temp = [];

            list($project_name, $quantity, $total) = explode(",", $line);

            preg_match('/(\d+)-(.*)/', $project_name, $match);

            if ($match) {
                $temp['product_id'] = $match[1];
                $temp['name']       = $match[2];
            } else {
                continue;
            }

            $type = '星云费用';

            preg_match('/:(\d+)/', $quantity, $match);

            if ($match) {
                $temp['quantity'] = sprintf('%.2f', $match[1]);
            }

            preg_match('/:(\d+)/', $total, $match);

		//修改星云计费比例

            if ($match) {
                $temp['total'] = sprintf('%.2f', $match[1]*0.9);
            }

            //获取分成项目公共信息
            $groups = array_filter(
                $public_group, function ($val) use ($temp) {
                    return $val['product_id'] == $temp['product_id'];
                }
            );

            if ($groups) {

                foreach ($groups as $group) {

                    $temp2 = [];

                    //获取项目信息
                    $product2 = current(
                        array_filter(
                            $products, function ($val) use ($group) {
                                return $val['product_id'] == $group['product_id2'];
                            }
                        )
                    );

                    $temp2['title']      = $this->ctitle;
                    $temp2['date']       = $cdate;
                    $temp2['type']       = $type;
                    $temp2['product_id'] = $product2['product_id'];
                    $temp2['name']       = $product2['name'];
                    $temp2['quantity']   = sprintf('%.2f', bcmul($temp['quantity'], $group['percent'] / 100, 2));
                    $temp2['total']      = sprintf('%.2f', bcmul($temp['total'], $group['percent'] / 100, 2));

                    $datas[] = $temp2;
                }
            } else {
                $temp['title'] = $this->ctitle;
                $temp['date']  = $cdate;
                $temp['type']  = $type;
                $temp['price'] = '';
                $datas[]       = $temp;
            }
        }

        return $datas;
    }

    //私有云综合成本
    public function vmzhfwcbReport($cdate, $products = null, $public_group = null)
    {
        $datas = [];

        //私有云综合服务成本
        $filename = "cloud_bill_info." . date("Y.m", strtotime("-1 month")) . ".log";
        $filePath =
            PHP_OS == "WINNT" ? "data/" . $filename : "/tmp/" . $filename;

        $targetFile = getcwd()."/data/report/cloud_bill_info." . date("Y.m", strtotime("-1 month")) . ".log";
        if (!is_file($targetFile)) {
            if (!is_file($filePath)) {
                throw new \Exception('获取星云物理机数据失败', -2);
            }
            copy($filePath, $targetFile);
            $lines = file($filePath);
        } else {
            $lines = file($targetFile);
        }

        foreach ($lines as $line) {
            if (PHP_OS != "WINNT") {
                $line = mb_convert_encoding(trim($line), "UTF-8", "GBK");
            }
            $temp = [];

            list($project_name, $quantity, $total) = explode(",", $line);

            preg_match('/(\d+)-(.*)/', $project_name, $match);

            if ($match) {
                $temp['product_id'] = $match[1];
                $temp['name']       = $match[2];
            } else {
                continue;
            }

            $type = '私有云综合服务成本';

            preg_match('/:(\d+)/', $quantity, $match);

            if ($match) {
                $temp['quantity'] = sprintf('%.2f', $match[1]);
            }

            preg_match('/:(\d+)/', $total, $match);

            if ($match) {
                $temp['total'] = sprintf('%.2f', $match[1]);
            }

            //获取项目价格信息
            $product = current(
                array_filter(
                    $products, function ($val) use ($temp) {
                    return $val['product_id'] == $temp['product_id'] || $val['name'] == $temp['name'];
                }
                )
            );

            if ($product) {
                $temp['name']       = $product['name'];
                $temp['product_id'] = $product['product_id'];
            }

            $mapping = $this->mapping;

            //获取分成项目公共信息
            $groups = array_filter(
                $public_group, function ($val) use ($temp) {
                return $val['product_id'] == $temp['product_id'];
            }
            );

            if ($groups) {

                foreach ($groups as $group) {

                    $temp2 = [];

                    //获取项目信息
                    $product2 = current(
                        array_filter(
                            $products, function ($val) use ($group) {
                            return $val['product_id'] == $group['product_id2'];
                        }
                        )
                    );

                    $temp2['title']      = $this->ctitle;
                    $temp2['date']       = $cdate;
                    $temp2['type']       = $type;
                    $temp2['product_id'] = $product2['product_id'];
                    $temp2['name']       = $product2['name'];
                    $temp2['quantity']   = sprintf('%.2f', bcmul($temp['quantity'], $group['percent'] / 100, 2));
                    $price         = $product[$mapping[strtolower($type)]];
                    $temp2['price'] = sprintf('%.2f', $price);
                    $temp2['total'] = sprintf('%.2f', bcmul($temp2['quantity'], $price, 2));

                    $datas[] = $temp2;
                }
            } else {
                $temp['title'] = $this->ctitle;
                $temp['date']  = $cdate;
                $temp['type']  = $type;

                $price         = $product[$mapping[strtolower($type)]];
                $temp['price'] = sprintf('%.2f', $price);
                $temp['total'] = sprintf('%.2f', bcmul($temp['quantity'], $price, 2));

                $datas[]       = $temp;
            }
        }

        return $datas;
    }

} 
