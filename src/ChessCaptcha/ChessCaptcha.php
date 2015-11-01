<?php

/**
 * The main class that initializes the challenge
 */

namespace Elioair\ChessCaptcha;

use \Elioair\ChessCaptcha\BoardImageCreator;
use \Elioair\ChessCaptcha\FenGenerator;

class ChessCaptcha
{
  public $chessCaptchaChallenge;  // the base64 string of the image
  public $chessCaptchaNoJsChallenge;  // the base64 string of the image with js disabled
  public $chessCaptchaFenCode;
  public $chessCaptchaFenNoJs;
  public $chessCaptchaPieceStyle;
  protected $pieceNames = ['adventurer','alfonso','alpha','cases',
                            'condal','harlequin','kingdom','leipzig',
                            'line','lucena','magnetic','mark','marroquin',
                            'maya','mediaeval','merida','motif','uscf','wikipedia'];

  function __construct($whiteSquare = '#f0d9b5', $blackSquare = '#b58863', $mate = 'no', $noJsFallback = 'yes', $pieceStyle = 'wikipedia')
  {
    $whiteSquareRGB = $this->hexToRgb($whiteSquare);
    $blackSquareRGB = $this->hexToRgb($blackSquare);

    // Generate the random fen and store it in session
    $fen = new FenGenerator($mate, $noJsFallback);
    $fenCode = $fen->fenStringOut;
    $this->chessCaptchaFenCode  = $fenCode; // string if simple mode; array if mate mode
    $this->chessCaptchaFenNoJs  = $fen->fenNoJs;

    if($pieceStyle === 'random'){
      $pieceStyle = $this->pieceNames[mt_rand(0, (count($this->pieceNames)-1))];
      $this->chessCaptchaPieceStyle = $pieceStyle;
    }else{
      $this->chessCaptchaPieceStyle = $pieceStyle;
    }

    if($mate == 'no'){
      // use the above fen as input and create a new board object
      $bd = new BoardImageCreator($fenCode, $whiteSquareRGB, $blackSquareRGB, $pieceStyle);

      // now create the board image in base64 encoding containing the position
      $this->chessCaptchaChallenge = $bd->sendImage($bd->boardOut);
    }

    if($noJsFallback == 'yes'){
      // No js fallback
      $noJsBd = new BoardImageCreator($this->chessCaptchaFenNoJs, $whiteSquareRGB, $blackSquareRGB);

      // now create the board image in base64 encoding containing the position
      $this->chessCaptchaNoJsChallenge = $noJsBd->sendImage($noJsBd->boardOut);
    }

  }

  /**
   * Compares the image fen code to the user input fen code and returns true if they match.
   * @param  string $inputFen       The fen code from the user response
   * @param  boolean $noJs           Enable or disable the display of a captcha in case of no javascript
   * @param  boolean $colorTolerance If set to true the comparison becomes color agnostic
   * @return boolean                 If it validates
   */
  public static function validate($inputFen, $noJs = false, $colorTolerance = false)
  {
    if (session_status() === PHP_SESSION_NONE){
      session_start();
    }
    isset($_SESSION['fenchallenge']) ? $sessFen = $_SESSION['fenchallenge'] : $sessFen = null; //die('Error: No position!');
    isset($_SESSION['piecenojs']) ? $sessPiece = $_SESSION['piecenojs'] : $sessPiece = null;

    if($sessFen == null){
      return false;
    }

    // When js is disabled
    if($noJs && $sessPiece){
      if(ctype_upper($sessPiece)){ // uppercase = white
        $sessPiece = 'w'.strtolower($sessPiece);  // make the session piece in a form similar to the input
      }else{ // black
        $sessPiece = 'b'.strtolower($sessPiece);
      }

      if($inputFen == $sessPiece){ // In this case the fen is replaced by the piece
        return true;
      }else{
        return false;
      }
    }

    // check if color tolerance is enabled and make them both lowercase to compare
    if($colorTolerance){
      $inputFen = strtolower($inputFen);
      $sessFen  = strtolower($sessFen);
    }

    // Perform the comparison
    if($inputFen == $sessFen){
      return true;
    }else{
      return false;
    }
  }


  /**
   * Converts the hex color from the params to rgb
   * @param  string $hex the color in hex
   * @return string      a string with the r,g,b value
   */
	private function hexToRgb($hex){
		list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
		return "($r,$g,$b)";
	}

   /**
    * Returns the html used when no js fallback is enabled and shown when js is disabled
    * @param  string $pieceImgDirectory  the directory of the piece image files. Format: dirname/
    * @return string                    the html code
    */
  public function noJsHtml($pieceImgDirectory){

    $html = '<div id="chesscaptchaNojsFallback">'
            .'<img style="display:block;" src="data:image/jpeg;base64,'.$this->chessCaptchaNoJsChallenge.'"/>'
            .'<div>'
            .'<br>'
            .'<div style="padding:5px; display:inline-block; border:1px solid #ccc; margin-bottom:10px;">'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="wk"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/wK.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="wq"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/wQ.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="wb"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/wB.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="wn"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/wN.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="wr"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/wR.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="wp"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/wP.png"/>&nbsp;'
            .'</div>'
            .'<br>'
            .'<div style="padding:5px; display:inline-block; border:1px solid #ccc;">'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="bk"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/bK.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="bq"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/bQ.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="bb"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/bB.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="bn"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/bN.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="br"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/bR.png"/>&nbsp;|&nbsp;'
            .'<input  style="-webkit-transform: scale(1.5); -moz-transform: scale(1.5); -ms-transform: scale(1.5); -o-transform: scale(1.5); transform: scale(1.5);" type="radio" name="ccnojschallenge" value="bp"/><img style="width:25px; height:25px;" src="'.$pieceImgDirectory.'img/pieces/wikipedia/bP.png"/>&nbsp;'
            .'</div>'
            .'<div style="clear:both;"></div>'
            .'</div>'
            .'</div>';

    return $html;
  }

}
