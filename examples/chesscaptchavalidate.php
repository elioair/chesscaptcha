<?php

require_once("../vendor/autoload.php");

// Example only. Always sanitize user input!
isset($_POST['chesscaptchaposition']) ? $inputFen = filter_var($_POST['chesscaptchaposition'], FILTER_SANITIZE_STRING) : $inputFen = null;
isset($_POST['ccnojschallenge']) ? $respNoJs = $inputFen = filter_var($_POST['ccnojschallenge'], FILTER_SANITIZE_STRING) : $respNoJs = null;

$noJs = false;
$colorTolerance = false; // Config param: Change this to true if you want to enable color tolerance

// when Js Is disabled
if($respNoJs && $noJs == true){  // no js input only
  $inputFen = $respNoJs;
}

$validate = false;  // init
if($inputFen){
  $validate = \Elioair\ChessCaptcha\ChessCaptcha::validate($inputFen, $noJs, $colorTolerance);
}


if($validate){
  echo '{"valid":true}';
}else{
  echo '{"valid":false}';
}
