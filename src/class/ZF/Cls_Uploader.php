<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2018/12/19
 * Time: 14:38
 * File: Cls_Uploader.php
 */

namespace ZF;

/**
 * 文件上传类
 *
 * @package ZF
 * @author  Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since   2018.12.19
 */
class Uploader
{
    /**
     * 缩略图最大宽度
     * 
     * @var int
     */
    private static $_thumb_max_width = 220;

    /**
     * 缩略图最大高度
     * 
     * @var int
     */
    private static $_thumb_max_height = 360;

    /**
     * 上传目录（相对于Document_Root）
     * 
     * @var string
     */
    private static $_upload_dir = '/uploads';

    /**
     * 缩略图目录（相对于Document_Root）
     * 
     * @var string
     */
    private static $_thumb_dir = '/uploads/thumb';

    /**
     * 允许上传的文件类型
     * 
     * @var array
     */
    private static $_filetype = array('jpeg', 'jpg', 'png', 'gif', 'bmp',);

    /**
     * 最大允许上传的文件大小
     * 
     * @var float|int
     */
    private static $_sizelimit = 20*1024*1024;

    /**
     * 用于处理海报文件上传处理
     *
     * @param string $tag         类别（用于创建子目录）
     * @param bool   $createthumb 是否创建缩略图
     * @param bool   $return      是否返回json数据/false 直接输出
     * 
     * @return string
     * @since  2018.12.11
     */
    public static function uploadFiles($tag = '', $createthumb = false, 
        $return = false
    ) {
        $list = array();
        $code = 1;
        $msg = '请选择需要上传的文件';
        $date = date('Ymd');
        $th = $_SERVER['DOCUMENT_ROOT'] . self::$_thumb_dir . '/' . 
            ($tag ? "{$tag}/{$date}/" : '');
        if (!file_exists($th)) {
            @mkdir($th, 0777, true);
        }
        $dir = $_SERVER['DOCUMENT_ROOT'] . self::$_upload_dir . '/' . 
            ($tag ? "{$tag}/{$date}/" : '');
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (isset($_FILES) && $_FILES && count($_FILES) > 0) {
            foreach ($_FILES as $v) {
                $type = strtolower(self::isimage($v['tmp_name']));
                if (in_array($type, self::$_filetype)) {
                    if ($type == 'jpeg') {
                        $type = 'jpg';
                    }
                    if (self::$_sizelimit <= 0 || $v['size'] <= self::$_sizelimit) {
                        $filename = md5_file($v['tmp_name']) . ".{$type}";
                        if (!file_exists($dir . $filename)) {
                            move_uploaded_file($v['tmp_name'], $dir . $filename);
                        }
                        if (file_exists($dir . $filename)) {
                            if ($createthumb) {
                                $thumb = md5("__{$filename}") . ".jpg";
                                if (!file_exists($th . $thumb)) {
                                    self::createThumb(
                                        $dir . $filename, 
                                        $th . $thumb,
                                        $type
                                    );
                                }
                                $list[] = array(
                                        self::$_upload_dir . '/' . 
                                            ($tag ? "{$tag}/{$date}/" : '') .
                                            $filename,
                                        self::$_thumb_dir . '/' . 
                                            ($tag ? "{$tag}/{$date}/" : '') . $thumb,
                                    );
                                $code = 0;
                                $msg = 'success';
                            } else {
                                $list[] = self::$_upload_dir . '/' . 
                                    ($tag ? "{$tag}/{$date}/" : '') . $filename;
                                $code = 0;
                                $msg = 'success';
                            }
                        } else {
                            $code = 4;
                            $msg = '文件上传失败，请重新尝试';
                        }
                    } else {
                        $code = 3;
                        $msg = '选择的文件太大，超过服务器的限制';
                    }
                } else {
                    $msg = '选择的文件不允许上传';
                    $code = 2;
                }
                if (file_exists($v['tmp_name'])) {
                    @unlink($v['tmp_name']);
                }
            }
        }

        $ret = array('code' => $code, 'msg' => $msg,);
        if ($list) {
            $ret['list'] = $list;
        }
        if ($return) {
            return json_encode($ret);
        }
        exit(Common::JsonP($ret));
    }

    /**
     * 根据原图比例创建缩略图
     *
     * @param string $src  源图路径
     * @param string $dest 缩略图路径
     * @param string $type 源图格式
     * @param int    $tw   目标最大宽
     * @param int    $th   目标最大高
     * 
     * @return bool
     * @since  2018.12.11
     */
    public static function createThumb($src = '', $dest = '',
        $type = '', $tw = 0, $th = 0
    ) {
        $ret = false;
        if ($src && file_exists($src)) {
            if ($type == '') {
                $type = self::isimage($src);
                if ($type == 'jpeg') {
                    $type = 'jpg';
                }
            }

            switch($type) {
            case 'jpg':
                $im = imagecreatefromjpeg($src);
                break;
            case 'gif':
                $im = imagecreatefromgif($src);
                break;
            case 'png':
                $im = imagecreatefrompng($src);
                break;
            case 'bmp':
                $im = imagecreatefrombmp($src);
                break;
            default:
                $im = null;
                break;
            }
            if ($im) {
                $w = imagesx($im);
                $h = imagesy($im);

                if ($tw == 0) {
                    $tw = self::$_thumb_max_width;
                }
                if ($th == 0) {
                    $th = self::$_thumb_max_height;
                }
                $r = $w / $h;
                if ($r >= $tw / $th) {
                    //原始宽高比大于等行目标宽高比，以宽为主
                    $nw = $tw;
                    $nh = round($tw / $r);
                } else {
                    //以高为主
                    $nh = $th;
                    $nw = round($th * $r);
                }
                $nim = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($nim, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagejpeg($nim, $dest);
                imagedestroy($nim);
                imagedestroy($im);
                if (file_exists($dest)) {
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * 检测文件是否是图形文件
     *
     * @param string $file
     *
     * @return bool|string
     * @since  2018.11.28
     */
    public static function isimage($file)
    {
        $ret = false;
        $ax = @getimagesize($file);
        if ($ax) {
            $ret = @image_type_to_extension($ax[2], false);
        }
        return $ret;
    }

    /**
     * 检测是否是图形文件内容
     *
     * @param string $str
     *
     * @return bool|string
     * @since  2018.11.28
     */
    public static function isimagestr($str)
    {
        $ret = false;
        $ax = @getimagesizefromstring($str);
        if ($ax) {
            $ret = @image_type_to_extension($ax[2], false);
        }
        return $ret;
    }
}