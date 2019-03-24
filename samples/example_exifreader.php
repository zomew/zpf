<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/03/01
 * Time: 11:04
 * File: example_exifreader.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require '../src/__INIT.php';

$IMG = './';
if ((!array_key_exists('f', $_GET)) || ($_GET['f'] == "")) {
    $filename = 'DSC_3169.jpg';
} else {
    $filename = $_GET['f'];
    // Sanitize the filename to remove any hack attempts
    if (0 == preg_match('/^\.?\/?([_A-Za-z0-9]+\.jpe?g)$/i', $filename) || !file_exists($IMG . $filename)) {
        echo "<title>Bad image filename defined or file is not exists!</title>\n";
        echo "</head>\n";
        echo "<body>\n";
        echo "<p>Bad image filename defined - Must be jpg or jpeg<br>or<br>file isn't exists!</p>\n";
        echo "</body>\n";
        exit();
    }
}
$f = $IMG . $filename;

$d = \ZF\ExifReader::getExifTagsData($f);
$o = \ZF\ExifReader::buildhtml($d, '', 'ct');

echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
echo <<<EOT
<style type="text/css">
.content-width {MARGIN: auto;WIDTH: 600px;}
.content-width img{MAX-WIDTH: 100%!important;HEIGHT: auto!important;width:expression(this.width > 600 ? "600px" : this.width)!important;}
.ct {
  color: #FFFF00;
  //font-weight: bold;
}
.cv {
}
</style>

<script type="text/javascript" src="js/jquery-2.2.0.min.js"></script>
<script type="text/javascript" src="js/jquery.powertip.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.powertip.css" />
    
<div class="content-width" id='show'>
<a href='{$f}' target='_blank'><img src='{$f}' data-powertip="{$o}"></a>
<br><br>
</div>


<script type="text/javascript">
    $(function() {
        $('img').powerTip({followMouse: true});

    });
    
    var o_a = [];
    function trig_tips(id) {
        if (typeof(o_a[id]) === 'undefined') {
            o_a[id]=false;
        }

        o_a[id] = ! o_a[id];
        
        if (o_a[id]) {
            $.powerTip.show($('#'+id));
        }else{
            $.powerTip.hide($('#'+id));
        }
    }

</script>

EOT;
