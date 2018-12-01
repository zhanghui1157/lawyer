<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Org\Wechat;

/**
 * Description of WXUser
 *
 * @author 陈近荣
 */
class WXUser {
    /*
     * 获取用户信息
     */
    public static function getuserinfo() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . APPID . "&secret=" . APPSECRET;
        $content = file_get_contents($url);
        $ret = json_decode($content, true);
        if (array_key_exists('errcode', $ret)) {
            return false;
        } else {
            return $ret;
        }
    }
}
