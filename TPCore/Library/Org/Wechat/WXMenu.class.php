<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Org\Wechat;

/**
 * Description of WXMenu
 *
 * @author 陈近荣
 */
class WXMenu {
    private $_ACCESS_TOKEN;
    public function __construct($accesstoken) {
        $this->_ACCESS_TOKEN = $accesstoken;
    }
    /**
     * create the menu
     * @return true or false
     */
    public function createMenu($menu) {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->_ACCESS_TOKEN;
        $content = curl_post($url, $menu);
        $ret = json_decode($content, true);
        return $ret;
    }
    /**
     * get the menu
     * @return menu in json,or false
     */
    public function getMenu() {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $this->_ACCESS_TOKEN;
        $content = file_get_contents($url);
        if (strpos($content, 'errcode') === false) {
            return $content;
        } else {
            return false;
        }
    }
    /**
     * delete the menu
     * @return true or false
     */
    public function deleteMenu() {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" .$this->_ACCESS_TOKEN;
        $content = file_get_contents($url);
        $ret = json_decode($content, true);
        if ($ret['errcode'] == 0) {
            return true;
        } else {
            return false;
        }
    }
}
