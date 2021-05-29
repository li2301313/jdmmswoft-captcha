<?php
/**
 * Created by PhpStorm.
 * Desc:
 * User: ffx
 * Date: 2020/7/3
 * Time: 10:32
 */

namespace Jdmm\Captcha;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Redis\Pool;

/**
 * Class Captcha
 * @Bean()
 */
class Captcha

{
    /**
     * @Inject()
     * @var Pool
     */
    private $redis;

    /**
     * 生成验证码使用GD
     * @param int $verifyCodeLength 验证码长度
     * @param $img_width
     * @param $img_height
     * @return array 返回验证码标识 和 base64的验证码图片
     */
    public function verifyCodeGd($verifyCodeLength = 4, $img_width = 80, $img_height = 30)
    {
        $code = strtoupper($this->string($verifyCodeLength));
        $aimg = imageCreate($img_width, $img_height);       //生成图片
        ImageColorAllocate($aimg, 255, 255, 255);            //图片底色，ImageColorAllocate第1次定义颜色PHP就认为是底色了
        for ($i = 1; $i <= 128; $i++) {
            imageString($aimg, 1, mt_rand(1, $img_width), mt_rand(1, $img_height), "*",
                imageColorAllocate($aimg, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255)));
        }
        for ($i = 0; $i < strlen($code); $i++) {
            imageString($aimg, mt_rand(8, 12), $i * $img_width / 4 + mt_rand(1, 8), mt_rand(1, $img_height / 4),
                $code[$i],
                imageColorAllocate($aimg, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200)));
        }
        ob_start();
        ImagePng($aimg);
        $data = ob_get_clean();
        ImageDestroy($aimg);
        $id = uniqid();
        $key = $this->buildRedisKey($id);
        $this->redis->set($key, $code, 120);
//        $resp = context()->getResponse();
//        return $resp->withHeader('Content-Length', strlen($data))->withData($data)->withContentType('image/png');
//        return response($data, 200, ['Content-Length' => strlen($data)])->contentType('image/png');

        $type = getimagesizefromstring($data)['mime']; //获取二进制流图片格式
        $base64String = 'data:' . $type . ';base64,' . chunk_split(base64_encode($data));
        return ['image' => $base64String, 'id' => $id];
    }

    /**
     * 校验验证码
     * @param string $id 验证码标识
     * @param string $code 验证码
     * @return bool
     * @author ffx
     */
    public function check($id, $code)
    {
        $key = $this->buildRedisKey($id);
        $verifyCode = $this->redis->get($key);
        if(strtoupper($code) === $verifyCode){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 随机生成一个字符串
     * @param $length
     * @param bool $number 只添加数字
     * @param array $ignore 忽略某些字符串
     * @return string
     */
    private function string($length = 8, $number = true, $ignore = array('0', 'o', 'l', '1', 'i'))
    {
        $strings = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';  //字符池
        $numbers = '123456789';                    //数字池
        if ($ignore and is_array($ignore))
        {
            $strings = str_replace($ignore, '', $strings);
            $numbers = str_replace($ignore, '', $numbers);
        }

        $pattern = $strings . $numbers;
        $max = strlen($pattern) - 1;
        $key = '';
        for ($i = 0; $i < $length; $i++)
        {
            $key .= $pattern[mt_rand(0, $max)];    //生成php随机数
        }
        return $key;
    }

    /**
     * @param $key
     * @return string
     * @author ffx
     */
    function buildRedisKey($key){
        return 'captcha_' . $key;
    }
}
