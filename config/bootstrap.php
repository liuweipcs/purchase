<?php
Yii::setAlias('phpexcel', dirname(__DIR__) . '/vendor/phpoffice/phpexcel');



/**
 * 打印所有变量
 */
function vd(){
    $params = func_get_args();
    $numArgs = func_num_args();
    foreach ($params as $v) {
        if (is_array($v) || is_object($v)) {
            $template = php_sapi_name() !== 'cli' ? '<pre>%s</pre>' : "\n%s\n" ;
            printf( $template, print_r( $v, true ) ) ;
        } else {
            var_dump($v);
        }
        if ($numArgs > 1) echo '<hr>';
    }
    exit;
}

function getDivContent($content, $width = 100, $style = '') {
    return '<div style="width:'.$width.'px;word-wrap:break-word; word-break:break-all;'.$style.'">'.$content.'</div>';
}


function jsonReturn($code = 1, $message = 'success', $data = []) {
    return json_encode(['code'=>$code, 'message'=>$message, 'data'=>$data]);
}

function getStringToArray($string) {
    $string = str_replace(' ', ',', trim($string));
    $string = str_replace('，', ',', trim($string));
    return array_unique(explode(',', $string));
}