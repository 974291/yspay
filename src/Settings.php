<?php
/**
 * @Created by Aler.gl <974291@qq.com>.
 * User: Aler.gl
 * Date: 2020/9/28
 * Time: 13:47
 */
namespace Aler\Yspay;

interface Settings{

    /**
     * 正式环境请求地址
     */
    const PAYURL = 'https://qrcode.ysepay.com/gateway.do';

    /**
     * 默认编码
     */
    const CHARSET = 'UTF-8';

    /**
     * 默认加密方式RSA
     */
    const SIGN_TYPE = 'RSA';

    /**
     * 接口版本
     */
    const VERSION = '3.0';


    public function setConfig($config);
}