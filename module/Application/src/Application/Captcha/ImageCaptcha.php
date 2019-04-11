<?php

namespace Application\Captcha;

use Zend\Captcha\Exception\NoFontProvidedException;
use Zend\Captcha\Image;


/**
 * Class ImageCaptch啊
 *
 * @package Account\Captcha
 * @author  Xuman
 * @version $Id$
 */
class ImageCaptcha extends Image
{
    protected $width = 130;
    protected $height = 40;
    protected $wordlen = 5;
    protected $code;
    protected $img;
    protected $fonts = array();

    //生成背景
    private function createBg()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color     = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    public function getFont()
    {
        return $this->fonts[array_rand($this->fonts)];
    }

    //生成文字
    private function createFont($word)
    {
        $_x = $this->width / $this->wordlen;
        for ($i = 0; $i < $this->wordlen; $i++) {
            $fcolor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext(
                $this->img, $this->fsize, mt_rand(-30, 30), $_x * $i + mt_rand(1, 5), $this->height / 1.4,
                $fcolor, $this->getFont(), $word[$i]
            );
        }
    }

    //生成线条、雪花
    private function createLine()
    {
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline(
                $this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width),
                mt_rand(0, $this->height), $color
            );
        }
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    protected function generateImage($id, $word)
    {
        if (empty($this->fonts)) {
            throw new NoFontProvidedException('Image CAPTCHA requires font');
        }

        $imgFile = $this->getImgDir() . $id . $this->getSuffix();

        $this->createBg();
        $this->createLine();
        $this->createFont($word);

        imagepng($this->img, $imgFile);
        imagedestroy($this->img);
    }

    public function isValid($value, $context = null)
    {
        $bool = parent::isValid($value, $context);
        $this->setWord($this->generateWord());
        return $bool;
    }

} 