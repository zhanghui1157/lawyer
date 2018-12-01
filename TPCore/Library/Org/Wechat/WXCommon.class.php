<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Org\Wechat;

/**
 * Description of WXCommon
 *
 * @author 陈近荣
 */
class WXCommon {

    /**
     * get the Token��
     * @return {"access_token":"ACCESS_TOKEN","expires_in":7200} or false
     */
    public static function getToken() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . APPID . "&secret=" . APPSECRET;
        $content = file_get_contents($url);
        return WX_formatresult(json_decode($content, true));
    }
}
