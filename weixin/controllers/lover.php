<?php
/**
 * 
 * @copyright(c) 2015-2-10
 * @author zxx
 * @version Id:lover.php
 */

class Lover extends WX_Controller
{

    /*
     * zxx
     * 专题 情人节 页
     */
    public function lover_activity(){
        $url = 'http://'.DOMAIN_URL.'/lover/lover_activity';
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        $signPackage = array(
            "appId"     => 'wx4ef656a26d27e9df',
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
        );


        $this->smarty->assign('num',0);
        $this->smarty->assign('signPackage',$signPackage);
        $this->smarty->view('lover_activity');
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}