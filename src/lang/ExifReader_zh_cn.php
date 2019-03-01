<?php

class Lang_zh_cn {
    public static $name = 'zh_cn';

    public static $lang = array(
        'Make' => '厂商',        //厂商
        'Modle' => '相机型号',       //型号
        'Software' => '软件版本',    //软件
        'DateTime' => '拍摄时间',    //拍摄时间
        'ExposureTime' => '快门',    //快门时间
        'FNumber' => '光圈',  //光圈
        'ExposureBiasValue' => '曝光补偿',  //曝光补偿
        'ExposureProgram' => '曝光程序',  //曝光程序
        'MeteringMode' => '测光模式',    //测光模式
        'Flash' => '闪光灯',  //闪光灯
        'ISOSpeedRatings' => '感光度',  //感光度
        'DateTimeOriginal' => '原始日期',  //生成日期
        'DateTimeDigitized' => '生成日期',   //数字化时间
        'MaxApertureValue' => '最大光圈',  //最大光圈
        'FocalLength' => '焦距',  //焦距
        'PixelXDimension' => '宽',  //X
        'PixelYDimension' => '高',  //Y
        'Balance' => '白平衡',  //白平衡
        'ColorSpace' => '色彩模式', //色彩模式
        'ComponentsConfiguration' => '色彩空间', //空间
        'FocalLengthIn35mmFilm' => '等效焦距',  //35mm
        'SerialNumber' => '序列号',  //
        'ShutterCount' => '快门数',  //快门数
        'LensType' => '拍摄镜头',  //
    );

    public static $value = array(
        'ExposureProgram' => array(0 => '', 1 => '手动模式', 2 => '程序模式', 3 => '光圈优先', 4 => '快门优先', 5 => '景深优先', 6 => '运动模式', 7 => '人像模式', 8 => '风景模式'),
        'MeteringMode' => array(0 => '', 1 => '平均', 2 => '中央重点', 3 => '点测', 4 => '多点', 5 => '矩阵', 6 => '区域', 255 => '其他'),
        'Flash' => array(
            0x00 => "关闭",
            0x01 => "开启",
            0x05 => "打开(不探测返回光线)",
            0x07 => "打开(探测返回光线)",
            0x09 => "打开(强制)",
            0x0D => "打开(强制/不探测返回光线)",
            0x0F => "打开(强制/探测返回光线)",
            0x10 => "关闭(强制)",
            0x18 => "关闭(自动)",
            0x19 => "打开(自动)",
            0x1D => "打开(自动/不探测返回光线)",
            0x1F => "打开(自动/探测返回光线)",
            0x20 => "没有闪光功能",
            0x41 => "打开(防红眼)",
            0x45 => "打开(防红眼/不探测返回光线)",
            0x47 => "打开(防红眼/探测返回光线)",
            0x49 => "打开(强制/防红眼)",
            0x4D => "打开(强制/防红眼/不探测返回光线)",
            0x4F => "打开(强制/防红眼/探测返回光线)",
            0x59 => "打开(自动/防红眼)",
            0x5D => "打开(自动/防红眼/不探测返回光线)",
            0x5F => "打开(自动/防红眼/探测返回光线)"),
        'Balance' => array(0 => '自动', 1 => '手动',),
    );
}
