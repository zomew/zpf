<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/03/01
 * Time: 9:38
 * File: Cls_ExifReader.php
 */

namespace ZF;

include_once(ZF_ROOT.'libs/amd_jpegmetadata.class.inc.php');
include_once(JPEG_METADATA_DIR."Common/L10n.class.php");
include_once(JPEG_METADATA_DIR."TagDefinitions/XmpTags.class.php");

class ExifReader {
    private static $_data, $js;

    public static $title_s = '<ttitle>';
    public static $title_e = '</ttitle>';
    public static $value_s = '<value>';
    public static $value_e = '</value>';

    public static $html = <<<EOF
<div id='%divid%'>
%Make%  %Modle%  %DateTime%<br>
%ExposureTime%  %FNumber% %ISOSpeedRatings%  %ExposureProgram%  %ExposureBiasValue%<br>
%FocalLength%  %FocalLengthIn35mmFilm%  %MeteringMode%  %Flash%<br>
%SerialNumber%  %Balance%  %ShutterCount%  %PixelXDimension%  %PixelYDimension%<br>
%LensType%
</div>
EOF;
    
    public static $tags = array(
        'Make'  =>  array('exif.tiff.Make'),        //厂商
        'Modle' =>  array('exif.tiff.Model'),       //型号
        //'Software'  =>  array('exif.tiff.Software'),    //软件
        'DateTime'  =>  array('exif.tiff.DateTime'),    //拍摄时间
        //'DateTimeOriginal'  =>  array('exif.exif.DateTimeOriginal'),  //生成日期
        //'DateTimeDigitized' =>  array('exif.exif.DateTimeDigitized'),   //数字化时间
        'ExposureTime'  =>  array('exif.exif.ExposureTime'),    //快门时间
        'FNumber'  =>  array('exif.exif.FNumber'),  //光圈
        'ExposureBiasValue'  =>  array('exif.exif.ExposureBiasValue'),  //曝光补偿
        'ExposureProgram'  =>  array('exif.exif.ExposureProgram'),  //曝光程序
        'MeteringMode'  =>  array('exif.exif.MeteringMode'),    //测光模式
        'Flash'  =>  array('exif.exif.Flash'),  //闪光灯
        'ISOSpeedRatings'  =>  array('exif.exif.ISOSpeedRatings'),  //感光度
        'MaxApertureValue'  =>  array('exif.exif.MaxApertureValue'),  //最大光圈
        'FocalLength'  =>  array('exif.exif.FocalLength'),  //焦距
        'PixelXDimension'  =>  array('exif.exif.PixelXDimension'),  //X
        'PixelYDimension'  =>  array('exif.exif.PixelYDimension'),  //Y
        'Balance'  =>  array('exif.exif.Balance'),  //白平衡
        //'ColorSpace'    =>  array('exif.exif.ColorSpace'),  //色彩模式
        //'ComponentsConfiguration' =>  array('exif.exif.ComponentsConfiguration'), //颜色
        'FocalLengthIn35mmFilm'  =>  array('exif.exif.FocalLengthIn35mmFilm'),  //35mm
        'SerialNumber'  =>  array('exif.maker.Nikon.SerialNumber','exif.maker.Canon.InternalSerialNumber','exif.maker.Pentax.SerialNumber'),  //
        'ShutterCount'  =>  array('exif.maker.Nikon.ShutterCount'),  //快门数
        'LensType'  =>  array('exif.maker.Nikon.LensData','exif.maker.Canon.CanonCameraSettings.LensType'),
    );

    public static function GetExifTagsData($file = '', $options = array()) {
        $ret = array();
        if ($file && is_string($file) && file_exists($file)) {
            if (!$options) $options = array(
                'filter' => \AMD_JpegMetaData::TAGFILTER_IMPLEMENTED,
                'optimizeIptcDateTime' => true,
                'exif' => true,
                'iptc' => true,
                'xmp' => true,
                'magic' => true,
            );
            $exif = new \AMD_JpegMetaData($file,$options);
            if ($exif) {
                foreach (self::$tags as $k => $v) {
                    foreach ($v as $l) {
                        $val = $exif->getTag($l);
                        if ($val) {
                            $d = $val->getLabel();
                            $value = $val->getValue();
                            if (!is_object($d)) {
                                if (is_array($d)) {
                                    $label = $d['computed'];
                                }else{
                                    $label = $d;
                                }
                            }else{
                                $label = $d->format('Y-m-d H:i:s');
                            }
                            $ret['Label'][$k] = $label;
                            $ret['Value'][$k] = $value;
                            break;
                        }
                    }
                }
                if ($ret) self::$_data = $ret;
            }
        }
        return $ret;
    }

    public static function getLang($data = array(), $lang = 'zh_cn') {
        $r = array();
        $c = '\Lang_' . $lang;
        $f = ZF_ROOT . 'lang/ExifReader_' . $lang . '.php';

        if (!$data) {
            if (self::$_data) {
                $data = self::$_data;
            }else{
                return false;
            }
        }

        if (file_exists($f)) {
            include_once($f);
            $lang = new $c;
            foreach ($data['Label'] as $k => $v) {
                if (isset($lang::$lang[$k])) {
                    $r[$k] = self::$title_s . $lang::$lang[$k] . self::$title_e . ' : ';
                }else{
                    $r[$k] = self::$title_s . $k . self::$title_e . ' : ';
                }

                if (isset($lang::$value[$k])) {
                    $val = $data['Value'][$k];
                    if (is_int($val)) {
                        if (isset($lang::$value[$k][$val])) {
                            $r[$k] .= self::$value_s . $lang::$value[$k][$val] . self::$value_e;
                        }else{
                            $r[$k] .= self::$value_s . $v . self::$value_e;
                        }
                    }else{
                        $r[$k] .= self::$value_s . $v . self::$value_e;
                    }
                }else{
                    $r[$k] .= self::$value_s . $v . self::$value_e;;
                }
            }
        }else{
            //没有语言文件，返回源数据
            return $data['Label'];
        }
        return $r;
    }

    public static function buildhtml($data = array(), $divid = '',$tid = '',$vid = '',$lang = 'zh_cn') {
        $da = self::getLang($data, $lang);
        $r = self::$html;
        if ($divid) {
            $r = str_replace('%divid%',$divid,$r);
        }else{
            $r = str_replace('<div id=\'%divid%\'>','<div>',$r);
        }
        foreach ($da as $k => $v) {
            $r = str_replace("%{$k}%",$v,$r);
        }
        if ($tid) {
            $r = str_replace(self::$title_s,"<label class='{$tid}'>",$r);
            $r = str_replace(self::$title_e,'</label>',$r);
        }else{
            $r = str_replace(self::$title_s,'',$r);
            $r = str_replace(self::$title_e,'',$r);
        }
        if ($vid) {
            $r = str_replace(self::$value_s,"<label class='{$vid}'>",$r);
            $r = str_replace(self::$value_e,'</label>',$r);
        }else{
            $r = str_replace(self::$value_s,'',$r);
            $r = str_replace(self::$value_e,'',$r);
        }

        $r = preg_replace('/%[^\s]*?%/im','',$r);
        $m = explode("\r\n",$r);
        $js = '[';
        foreach ($m as $v) {
            $x = trim($v,"\r\n");
            $js .= "\"{$x}\",\r\n";
        }
        $js .= "].join('\\n')";
        self::$js = $js;
        return $r;
    }
}

