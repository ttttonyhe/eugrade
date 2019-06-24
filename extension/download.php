<?php
$file=$_GET['filename'];
forceDownload($file);
function forceDownload($filename){

    if(false==file_exists($filename)){
        returnfalse;
    }

    header('Content-Type:application-x/force-download');
    header('Content-Disposition:attachment;filename="'.basename($filename).'"');
    header('Content-length:'.filesize($filename));

    // for IE6
    if(false===strpos($_SERVER['HTTP_USER_AGENT'],'MSIE6')){
        header('Cache-Control:no-cache,must-revalidate');
    }
    header('Pragma:no-cache');

    return readfile($filename);
}
?>