<?php

/**
 * Class to generate the fen string.
 */

namespace Elioair\ChessCaptcha;

class FenGenerator
{

  public $fenStringOut;
  public $fenNoJs;

  // Simple mode fen codes
  private $givenFen = [
    '8/8/2N5/2n2k2/2K5/2r1B3/8/8',
    '8/8/2N5/2n2k2/8/K3B3/8/8',
    '8/2k5/2N5/8/8/K3B3/8/8',
    '8/2k5/8/8/8/K7/8/8',
    'k2rn3/8/8/8/8/4K3/8/1Q1R4',
    '8/p1r3p1/1p2p2p/8/PK1PPk2/7P/1R4P1/8',
    '2n4k/4P2p/5P1P/7K/2p2B2/2Pr4/8/6R1',
    '8/1k1q4/8/6p1/1Q6/3B4/K4N2/8',
    '8/8/k2q4/8/8/2N5/1K2B3/8',
  ];

  // Mate in one fen codes. Format:
  // ['problem_fen', 'checkmated_solution_fen','w or b side to play']
  private $givenMateInOne = [
    ['6k1/7r/8/8/8/Q7/8/4KR2', '5Qk1/7r/8/8/8/8/8/4KR2', 'w'],
    ['kb2N1r1/p7/8/8/8/8/B6Q/5K2', 'kb2N1r1/p7/8/3B4/8/8/7Q/5K2', 'w'],
    ['7k/8/1Q1N2K1/8/b6B/8/8/5R2', '5R1k/8/1Q1N2K1/8/b6B/8/8/8', 'w'],
    ['8/p7/2k1K3/8/8/3R4/r7/1R4Q1', '8/p7/2k1K3/8/8/2R5/r7/1R4Q1', 'w'],
    ['8/8/8/k1N5/2K2B2/8/2R5/6Q1', '8/2B5/8/k1N5/2K5/8/2R5/6Q1', 'w'],
    ['R7/7k/pR6/5P1K/Qb6/3B4/7P/2r5', 'R7/7k/pR3P2/7K/Qb6/3B4/7P/2r5', 'w'],
    ['N6R/1p6/n1kp4/7B/2K5/8/8/3Q4', 'N3B2R/1p6/n1kp4/8/2K5/8/8/3Q4', 'w'],
    ['2rk3q/B7/5P2/8/B2N4/1QP5/2K5/8', '2rk3q/B7/4NP2/8/B7/1QP5/2K5/8', 'w'],
    ['3q4/8/6pr/R1B3k1/4K1p1/2Q5/8/8', '3q4/4B3/6pr/R5k1/4K1p1/2Q5/8/8', 'w'],
    ['5kr1/R4n2/2BP4/6Nb/4K3/8/8/8', '5kr1/R4n2/2BPN3/7b/4K3/8/8/8', 'w'],
    ['1B1bbkrR/5p2/2Q4P/4N3/4K3/8/8/5R2', '1B1bbkrR/5p2/2Q3NP/8/4K3/8/8/5R2', 'w'],
    ['6nk/R6p/8/1pp5/5Q2/1P3P1P/P1B5/3K4', '6nk/7R/8/1pp5/5Q2/1P3P1P/P1B5/3K4', 'w'],
    ['7r/8/2k3p1/B7/5q2/2N5/Q3R3/K7', '7r/8/2k3p1/B2Q4/5q2/2N5/4R3/K7', 'w'],
    ['r7/1p4B1/N2k3p/n5pq/8/1Q3P2/P5P1/K3R3', 'r7/1p4B1/N2kQ2p/n5pq/8/5P2/P5P1/K3R3', 'w'],
    ['3kr3/5PN1/1P3q2/R7/8/6p1/PP3p2/K7', '3kQ3/6N1/1P3q2/R7/8/6p1/PP3p2/K7', 'w'],
    ['3k4/2npp3/1B4q1/8/8/8/2PPP3/R2K3R', 'R2k4/2npp3/1B4q1/8/8/8/2PPP3/3K3R', 'w'],
    ['8/b6r/kp3Rnq/8/1PP1N1Bp/3Q4/8/1K6', '8/b6r/kp3Rnq/2N5/1PP3Bp/3Q4/8/1K6', 'w'],
    ['3N2bk/7p/r5PP/8/5Qn1/8/PPP5/K7', '3N2bk/6Pp/r6P/8/5Qn1/8/PPP5/K7', 'w'],
    ['rkb3RQ/p7/4N3/8/8/8/P1P5/1K6', 'rkb3R1/p7/4N3/8/8/8/PQP5/1K6', 'w'],
    ['Rnkr3R/n7/4P3/1Q6/8/6b1/8/K7', 'Rnkr3R/n2Q4/4P3/8/8/6b1/8/K7', 'w'],
  ];

  /**
   * Selects a random array element containing the position fen code and or the
   * checkmate problem and solution positions. After they are selected they are
   * stored in session.
   * @param string $mate         'yes' if the mate-in-one is enabled or 'no'
   * @param string $noJsFallback 'yes' to show radio buttons when js is disabled or 'no'
   */
  function __construct($mate = 'no', $noJsFallback = 'yes')
  {
    if($mate == 'yes'){
      $matefen = $this->fenSelect($this->givenMateInOne); // problem fen array
      $this->fenStringOut = $matefen; // the array containing the problem, solution and side to play
      $this->fenInSession($matefen[1], 'fenchallenge'); // store the mate solution in session
    }else{
      $this->fenStringOut = $this->fenSelect($this->givenFen); // the fen code string
      $this->fenInSession($this->fenStringOut, 'fenchallenge'); // store the position in session
    }

    if($noJsFallback == 'yes'){
      $fNoJs = $this->randomFenNoJs();
      $this->fenNoJs = $fNoJs[0];
      $pieceNojs = $fNoJs[1];
      $this->fenInSession($this->fenNoJs, 'fennojs'); // fen code
      $this->fenInSession($pieceNojs, 'piecenojs'); // the piece
    }

  }

  /**
   * Select randomly a string from the array or an element
   * @param  [type] $fenArray  The array containing the fen code
   * @return [type]           The fencode string or array if mate mode is on
   */
  private function fenSelect($fenArray)
  {
    $random = mt_rand(0, (count($fenArray)-1));

    return $fenArray[$random];
  }

  /**
   * Store the fen code in session for the validation check
   * @param  [type] $fenCode    The value to be stored in session
   * @param  [type] $sessionVar The name of the session entry
   * @return [type]             [description]
   */
  protected function fenInSession($fenCode, $sessionVar)
  {
    if (session_status() === PHP_SESSION_NONE){
      session_start();
    }

    if(isset($_SESSION[$sessionVar])){
      unset($_SESSION[$sessionVar]);
    }else{
      $_SESSION[$sessionVar] = $fenCode;
    }
    
  }

  /**
   * In case no js is enabled this will create a random board fen containing one piece
   * @return array containing the fen code for the board and the piece on the board
   */
  protected function randomFenNoJs()
  {
    $pieces = ['r','n','b','q','k','p','P','R','N','B','Q','K'];
    $piece  = $pieces[mt_rand(0,11)];
    $pieceSquaresLeft  = mt_rand(1,8);
    $pieceSquaresRight = 8-$pieceSquaresLeft;
    $pieceRow = mt_rand(1,8);

    if($pieceSquaresRight == 1){
      $piecePosition = $pieceSquaresLeft.$piece;
    }else{
      $piecePosition = $pieceSquaresLeft.$piece.$pieceSquaresRight;
    }

    $fen = '';
    for($i = 1; $i < 9; $i++){
      if($i == $pieceRow){
        $fen .= $piecePosition;
      }else{
        $fen .= '8';
      }

      if($i<8){
        $fen .= '/';
      }
    }

    $out = [$fen,$piece];

    return $out;
  }
}
