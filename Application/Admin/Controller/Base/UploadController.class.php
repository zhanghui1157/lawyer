<?php
namespace Admin\Controller\Base;
class UploadController extends \Admin\Controller\CommonController {
    //protected $_ajaxmessage=array('code'=>0,'msg'=>'');
    public function uploadifyflv() {
        $this->_ajaxmessage['error'] = 0;
        $this->_ajaxmessage['message'] = '';
        $this->_ajaxmessage['url'] = null;
        $monthdir = strval(date('Ym', time()));
        //图片上传设置
        $config = array(
            'maxSize' => 104857600, // 设置附件上传大小100M
            'rootPath' => C('uploaddir'),
            'savePath' => '/UploadFlv/' . $monthdir . '/',
            'saveName' => 'getGUID',
            'exts' => array('flv'),
            'autoSub' => false,
            'subName' => array('date', 'Ymd')
        );
        $upload = new \Think\Upload($config); //实例化上传类
        $images = $upload->upload();
        //print_r($images);
        //判断是否有图
        if ($images) {
            //$info=$images['Filedata']['savename'];
            foreach ($images as $key => $value) {
                $this->_ajaxmessage['url'] = '/' . C('uploaddir') . $value['savepath'] . $value['savename'];
            }
        } else {
            $this->_ajaxmessage['error'] = 1;
            $this->_ajaxmessage['message'] = $upload->getError();
        }
        $this->ajaxReturn($this->_ajaxmessage, 'json');
    }
    public function uploadifythumb() {
        $this->_ajaxmessage['error'] = 0;
        $this->_ajaxmessage['message'] = '';
        $this->_ajaxmessage['url'] = null;
        $monthdir = strval(date('Ym', time()));
        //图片上传设置
        $config = array(
            'maxSize' => 1048576, // 设置附件上传大小1M
            'rootPath' => C('uploaddir'),
            'savePath' => '/UploadPic/' . $monthdir . '/',
            'saveName' => 'getGUID',
            'autoSub' => false,
            'subName' => array('date', 'Ymd')
        );
        $upload = new \Think\Upload($config); //实例化上传类
        $images = $upload->upload();
        //判断是否有图
        if ($images) {
            //$info=$images['Filedata']['savename'];
            foreach ($images as $key => $value) {
                $this->_ajaxmessage['url'] = '/' . C('uploaddir') . $value['savepath'] . $value['savename'];
            }
        } else {
            $this->_ajaxmessage['error'] = 1;
            $this->_ajaxmessage['message'] = $upload->getError();
        }
        $this->ajaxReturn($this->_ajaxmessage, 'json');
    }
    /*
     * 批量上传图标
     */
    public function uploadifyico() {
        $this->_ajaxmessage['error'] = 0;
        $this->_ajaxmessage['message'] = '';
        $this->_ajaxmessage['url'] = null;
        $monthdir = strval(date('Ym', time()));
        //图片上传设置
        $config = array(
            'maxSize' => 1048576, // 设置附件上传大小1M
            'rootPath' => C('uploaddir'),
            'savePath' => '/UploadIco/' . $monthdir . '/',
            'saveName' => 'getGUID',
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub' => false,
            'subName' => array('date', 'Ymd')
        );
        $upload = new \Think\Upload($config); //实例化上传类
        $images = $upload->upload();
        //判断是否有图
        if ($images) {
            //$info=$images['Filedata']['savename'];
            foreach ($images as $key => $value) {
                $this->_ajaxmessage['url'] = '/' . C('uploaddir') . $value['savepath'] . $value['savename'];
            }
        } else {
            $this->_ajaxmessage['error'] = 1;
            $this->_ajaxmessage['message'] = $upload->getError();
        }
        $this->ajaxReturn($this->_ajaxmessage, 'json');
    }
    /*
     * 批量上传商品图片
     */
    public function uploadifypic() {
        $result = [];
        $result['error'] = 0;
        $result['message'] = '';
        $result['url'] = null;
        if(I('get.goods_id')!=0)
        {
            $monthdir = strval(date('Ym', time()));
            //图片上传设置
            $config = array(
                'maxSize' => 2048576, // 设置附件上传大小2M
                'rootPath' => C('uploaddir'),
                'savePath' => '/UploadPic/' . $monthdir . '/',
                'saveName' => 'getGUID',
                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                'autoSub' => false,
                'subName' => array('date', 'Ymd')
            );
            $upload = new \Think\Upload($config); //实例化上传类
            $images = $upload->upload();
            $Img = new \Think\Image();//实例化图片类对象 
            //判断是否有图
            if ($images) {
                foreach ($images as $key => $value) {
                    $result['url'] = '/' . C('uploaddir') . $value['savepath'] . $value['savename'];
                    $result['message'] = '已上传';
                    //生成缩略图
                    $bigImgUrl = $result['url'];
                    $thumbImgUrl = getImagesThumb($bigImgUrl);
                    $bigImgUrl = '.'.$bigImgUrl;
                    $Img->open($bigImgUrl);
                    $Img->thumb(400, 300)->save('.'.$thumbImgUrl);
                    //保存到数据库
                    $goodsid = I('get.goods_id');
                    M('goods_pic')->add(['goods_id'=>$goodsid,'thumb'=>$thumbImgUrl,'bigpic'=>$result['url'],'addtime'=>time(),'listorder'=>0]);
                }
            } else {
                $result['error'] = 1;
                $result['message'] = $upload->getError();
            }
        }
        else
        {
            $result['error'] = 1;
            $result['message'] = '请提交后再通过修改商品上传图片';
        }
        $this->ajaxReturn($result, 'json');
    }
    /*
     * 上传管理员头像等
     */
    public function uploadifyother() {
        $this->_ajaxmessage['error'] = 0;
        $this->_ajaxmessage['message'] = '';
        $this->_ajaxmessage['url'] = null;
        $monthdir = strval(date('Ym', time()));
        //图片上传设置
        $config = array(
            'maxSize' => 1048576, // 设置附件上传大小1M
            'rootPath' => C('uploaddir'),
            'savePath' => '/ManagerPic/' . $monthdir . '/',
            'saveName' => 'getGUID',
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub' => false,
            'subName' => array('date', 'Ymd')
        );
        $upload = new \Think\Upload($config); //实例化上传类
        $images = $upload->upload();
        //判断是否有图
        if ($images) {
            foreach ($images as $key => $value) {
                $this->_ajaxmessage['url'] = '/' . C('uploaddir') . $value['savepath'] . $value['savename'];
            }
        } else {
            $this->_ajaxmessage['error'] = 1;
            $this->_ajaxmessage['message'] = $upload->getError();
        }
        $this->ajaxReturn($this->_ajaxmessage, 'json');
    }
    /*
     * 下载内容上传
     */
    public function uploadifyfile() {
        $this->_ajaxmessage['error'] = 0;
        $this->_ajaxmessage['message'] = '';
        $this->_ajaxmessage['url'] = null;
        $monthdir = strval(date('Ym', time()));
        //图片上传设置
        $config = array(
            'maxSize' => 104857600, // 设置附件上传大小100M
            'rootPath' => C('uploaddir'),
            'savePath' => '/download/' . $monthdir . '/',
            'saveName' => 'getGUID',
            'exts' => array('zip','rar','doc','xls'),
            'autoSub' => false,
            'subName' => array('date', 'Ymd')
        );
        $upload = new \Think\Upload($config); //实例化上传类
        $images = $upload->upload();
        //判断是否有
        if ($images) {
            foreach ($images as $key => $value) {
                $this->_ajaxmessage['url'] = '/' . C('uploaddir') . $value['savepath'] . $value['savename'];
            }
        } else {
            $this->_ajaxmessage['error'] = 1;
            $this->_ajaxmessage['message'] = $upload->getError();
        }
        $this->ajaxReturn($this->_ajaxmessage, 'json');
    }
}
