<?php
/**
 * To run this test, first change the image path in BoardImageCreator::pieceImageDir
 * around line 24 to the absolute path of the images folder. 
 */

use Elioair\ChessCaptcha\ChessCaptcha;
 
class ChessCaptchaTestImageGenerated extends PHPUnit_Framework_TestCase {

	protected $cc;

  public function setUp(){

  	// Suppress session error
  	@session_start();

  	// Init the session vars with random values
  	$_SESSION['fenchallenge']  = "8/8/2N5/2n2k2/2K5/2r1B3/8/8";
  	$_SESSION['fennojs'] 			 = "r7/8/8/8/8/8/8/8";
  	$_SESSION['piecenojs'] 		 = "p";

    $this->cc = new ChessCaptcha;

  	parent::setUp();
  }
 
  public function testChessCaptchaImageIsBase64String()
  {
    $pattern = '@^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{4})$@'; // Base64
    $this->assertRegExp($pattern, $this->cc->chessCaptchaChallenge);
  }

  public function testIfReturnsFalseOnWrongAnswer()
  {
		$_SESSION['fenchallenge']  = "8/8/2N5/2n2k2/2K5/2r1B3/8/8"; // Store the correct answer in session
  	$noJs = false;
		$colorTolerance = false;
		$inputFen = 'k2rn3/8/8/8/8/4K3/8/1Q1R4'; //  The wrong answer

  	$validate = ChessCaptcha::validate($inputFen, $noJs, $colorTolerance); // Must be false

  	$this->assertFalse($validate);
  }
 
}