<?php

/**
* Generates the image showing the board with the position.
*
* Based on ChessImager: https://code.google.com/p/chessimager/
*
* NOTE: When testing from another directory remember to change the $pieceImageDir
*/

namespace Elioair\ChessCaptcha;

class BoardImageCreator{

  // Board Style Properties
  protected $lightSquareCol     = "(240,217,181)";  // in RGB without spaces
  protected $darkSquareCol      = "(181,136,99)";   // in RGB without spaces
  protected $boardBorderColor   = "(64,64,64)";     // in RGB without spaces
  protected $boardDirection     = "normal";
  protected $borderWidth        = 2;
  protected $enableCoordinates  = false;
  protected $squareSize         = 26;     // in px, max 150
  protected $pieceStyle         = "wikipedia";  // default option here
  protected $pieceImageDir      = "../assets/img/pieces/";
  protected $base64ImageOut     = true;
  public    $boardOut;

  function __construct($fenString, $whiteSquareRGB = "(240,217,181)", $blackSquareRGB = "(181,136,99)", $pieceStyle= "wikipedia")
  {
    if($whiteSquareRGB){
      $this->lightSquareCol = $whiteSquareRGB;
    }

    if($blackSquareRGB){
      $this->darkSquareCol = $blackSquareRGB;
    }

    $this->pieceStyle = $pieceStyle;

    $this->boardOut = $this->makeBoardImage($this->boardDirection);

    $pieceArray = $this->parseFenString($fenString);

    for ($square = 0; $square < 64; $square++) {
      $this->mergePiece($this->boardOut, $pieceArray[$square], $square, $this->boardDirection);
    }

  }

  protected function sendErrorImageAndDie($cerr) {
    $new = imageCreate(600, 30);
    $bgc = imageColorAllocate($new,255,255,255);
    $tc  = imageColorAllocate($new,0,0,0);
    imageFilledRectangle($new,0,0,150,30,$bgc);
    imageString($new,5,5,5,"Error: $cerr", $tc);
    $this->sendImage($new);
    die;
  }

  public function sendImage($img) {
    if (! $img) {
      $this->sendErrorImageAndDie("Invalid image object");
    }
    else {
      if($this->base64ImageOut){
        ob_start ();
        imagePNG ($img);
        $image_data = ob_get_contents ();
        ob_end_clean ();
        // return the base64 encoded image,
        // this way it doesn't send an image/png header
        // and simply returns the string
        return $image_data_base64 = base64_encode ($image_data);
      }else{
        // The entire page that instantiates the class becomes an image
        header("Content-type: image/png");
        imagePNG($img);
        imageDestroy($img);
      }
    }
  }

  protected function loadPNG($image_name) {
    $im = imageCreateFromPNG($image_name);
    if (! $im) {
      $this->sendErrorImageAndDie("Could not load piece image: $image_name");
    }
    return($im);
  }

  // Square Colors
  protected function parseColorString($str, &$red, &$green, &$blue) {
    preg_match("/\(?(\d+),(\d+),(\d+)\)?/", $str, $array);
    if (strlen($array[0]) > 0) {
      $red   = $array[1];
      $green = $array[2];
      $blue  = $array[3];
    }
  }

  protected function getDarkSquareColor($im) {
    $this->parseColorString($this->darkSquareCol, $red, $green, $blue);
    return imageColorAllocate($im, $red, $green, $blue);
  }

  protected function getLightSquareColor($im) {
    $this->parseColorString($this->lightSquareCol, $red, $green, $blue);
    return imageColorAllocate($im, $red, $green, $blue);
  }

  // Outline Colors
  protected function getOutlineColor($im) {
    $this->parseColorString($this->boardBorderColor, $red, $green, $blue);
    return imageColorAllocate($im, $red, $green, $blue);
  }

  protected function getBorderWidth() {
    $border_width_string = $this->borderWidth;
    if (strlen($border_width_string) > 0) {
      return $border_width_string;
    }
    else {
      return 1;
    }
  }

  // Coordinates
  protected function isCoordinatesEnabled() {
    return $this->enableCoordinates;
  }

  protected function getCoordinateWidth() {
    if ($this->isCoordinatesEnabled()) {
      $width = max(imageFontWidth($this->getCoordinateFont()),
                   imageFontHeight($this->getCoordinateFont())) * 1.5;
    }
    else {
      $width = 0;
    }
    return($width);
  }

  protected function getDecorationWidth() {
    if ($this->isCoordinatesEnabled()) {
      $width = $this->getBorderWidth() + $this->getCoordinateWidth() + 1;
    }
    else {
      $width = $this->getBorderWidth();
    }

    return($width);
  }

  protected function getCoordinateFont() {
    if (1.5 * max(imageFontWidth(4), imageFontHeight(4)) <= $this->squareSize) {
      return(4);
    }
    else if (1.5 * max(imageFontWidth(2), imageFontHeight(2)) <= $this->squareSize) {
      return(2);
    }
    else {
      return(1);
    }
  }

  protected function addCoordinates($im, $direction) {
    if (! $this->isCoordinatesEnabled()) {
      return;
    }

    $decorationWidth = $this->getDecorationWidth();
    $squareSize = $this->squareSize;
    $font = $this->getCoordinateFont();

    $x_left_numbers = ($decorationWidth - imageFontWidth($font)) / 2;
    $x_right_numbers = $x_left_numbers + 8 * $squareSize + $decorationWidth;
    $y1 = $decorationWidth + ($squareSize - imageFontHeight($font)) / 2;
    if ($direction == 'normal') {
      $deltaY = $squareSize;
    }
    else {
      $y1 = $y1 + (7 * $squareSize);
      $deltaY = -$squareSize;
    }

    $black = imageColorAllocate($im, 0, 0, 0);

    $y = $y1;
    for ($k = 8; $k >= 1; $k--) {
      imageString($im, $font, $x_left_numbers, $y, $k, $black);
      imageString($im, $font, $x_right_numbers, $y, $k, $black);
      $y += $deltaY;
    }

    $files = 'abcdefgh';
    $file = substr($files, $k - 1, 1);
    $x1 = $decorationWidth + ($squareSize - imageFontWidth($font)) / 2;
    $y_top_letters = ($decorationWidth - imageFontHeight($font)) / 2;
    $y_bottom_letters = $y_top_letters + 8 * $squareSize + $decorationWidth;

    if ($direction == 'normal') {
      $deltaX = $squareSize;
    }
    else {
      $x1 = $x1 + (7 * $squareSize);
      $deltaX = -$squareSize;
    }

    $x = $x1;
    for ($k = 0; $k < 8; $k++) {
      $file = substr($files, $k, 1);
      imageString($im, $font, $x, $y_top_letters, $file, $black);
      imageString($im, $font, $x, $y_bottom_letters, $file, $black);
      $x += $deltaX;
    }
  }

  protected function makeBoardImage($direction) {
    $squareSize = $this->squareSize;
    $decorationWidth = $this->getDecorationWidth();
    $coordinateWidth = $this->getCoordinateWidth();
    $borderWidth = $this->getBorderWidth();
    $numRows = 8 * $squareSize + 2 * $decorationWidth;
    $numCols = $numRows;

    $im = imageCreateTruecolor($numRows, $numCols);
    imageAlphaBlending($im, 1);

    $dark_square_color = $this->getDarkSquareColor($im);
    $light_square_color = $this->getLightSquareColor($im);
    $outline_color = $this->getOutlineColor($im);
    $white = imageColorAllocate($im, 255, 255, 255);

    imageFilledRectangle($im, 0, 0, $numRows - 1, $numCols - 1, $outline_color);
    if ($this->isCoordinatesEnabled()) {
      imageFilledRectangle($im, $borderWidth, $borderWidth, $numRows - $borderWidth - 1,
        $numCols - $borderWidth - 1, $white);
      imageFilledRectangle($im, $borderWidth + $coordinateWidth,
        $borderWidth + $coordinateWidth,
        $numRows - $borderWidth - $coordinateWidth - 1,
        $numCols - $borderWidth - $coordinateWidth - 1, $outline_color);
    }

    for ($rank = 0; $rank < 8; $rank++)
    {
        for ($file = 0; $file < 8; $file++)
        {
            $square_color = ($rank + $file) % 2 ? $dark_square_color : $light_square_color;
            $x1 = $file * $squareSize + $decorationWidth;
            $y1 = $rank * $squareSize + $decorationWidth;
            $x2 = $x1 + $squareSize - 1;
            $y2 = $y1 + $squareSize - 1;
            imageFilledRectangle($im, $x1, $y1, $x2, $y2, $square_color);
        }
    }

    $this->addCoordinates($im, $direction);

    return($im);
  }


  protected function parseFenString($str)
  {
    $count = 0;
    for ($k = 0; $k < strlen($str); $k++) {
      $char = substr($str, $k, 1);
      if ($char == "/") {
        continue;
      }

      else if (preg_match("/[prnbqkPRNBQK]/", $char)) {
        $out[$count++] = $char;
      }

      else if (preg_match("/[1-8]/", $char)) {
        for ($c = 0; $c < $char; $c++) {
          $out[$count++] = " ";
        }
      }

      else {
        // Invalid FEN character; bail
        break;
      }

      if ($count >= 64) {
        // array is full; bail
        break;
      }
    }

    $out = array_pad($out, 64, " ");

    return $out;
  }


  protected function pieceFilename($piece)
  {
    static $map = array( "p" => "bP",
                         "r" => "bR",
                         "n" => "bN",
                         "b" => "bB",
                         "q" => "bQ",
                         "k" => "bK",
                         "P" => "wP",
                         "R" => "wR",
                         "N" => "wN",
                         "B" => "wB",
                         "Q" => "wQ",
                         "K" => "wK"   );

    return $this->pieceImageDir . $this->pieceStyle . "/" . $map[$piece] . ".png";
  }


  protected function mergePiece($board, $piece, $square, $direction) {
    if ($piece == " ") {
      return;
    }

    $file = $square % 8;
    $rank = ($square - $file) / 8;

    $numCols = imagesx($board);
    $squareSize = $this->squareSize;
    $decorationWidth = ($numCols - 8 * $squareSize) / 2;

    if ($direction == 'normal') {
      $x = $decorationWidth + $file * $squareSize;
      $y = $decorationWidth + $rank * $squareSize;
    }
    else {
      $x = $decorationWidth + (7 - $file) * $squareSize;
      $y = $decorationWidth + (7 - $rank) * $squareSize;
    }

    $pieceImage = $this->loadPNG($this->pieceFilename($piece));
    $pieceSize = imageSx($pieceImage);
    if (! imageCopyResampled($board, $pieceImage, $x, $y, 0, 0, $squareSize,
          $squareSize, $pieceSize, $pieceSize)) {
      $this->sendErrorImageAndDie("imageCopy returned false");
    }

    imageDestroy($pieceImage);
  }

}
