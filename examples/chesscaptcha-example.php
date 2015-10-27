<!doctype html>
<html>
<head>
  <title>ChessCaptcha Example</title>
</head>
<body>

  <?php

  // The array that contains the configuration for the php side
  $config = [
    'divId'=>'chesscaptcha',
    'whitesquare'=>'#f0d9b5',
    'blacksquare'=>'#b58863',
    'matemode'=>'no', // yes or no
    'nojsfallback'=>'no', // yes or no; activate the fallback in case js is disabled
    'titleoverride'=>'Copy the position below', // text override
    'titlemateoverride'=>'Mate-In-One', // text override
    'helpoverride'=>'Drag the pieces into the board to copy the given position. To remove a piece drag it out of the board.', // text override
    'startoverride'=>'Start', // text override
    'clearoverride'=>'Clear', // text override
    'pieceImages'=>'../assets/', // the path to images for the js part
    'pieceStyle'=>'wikipedia', // the name of the piece style to use or 'random', default is 'wikipedia'
  ];
    // Use composer's autoloader or create yours or include the files
    require_once("../vendor/autoload.php");
    $chesscaptcha = new \Elioair\ChessCaptcha\ChessCaptcha($config['whitesquare'], $config['blacksquare'], $config['matemode'], $config['nojsfallback'], $config['pieceStyle']);
  ?>

  <form id="testform" class="chesscaptcha-form" action="chesscaptchavalidate.php" method="post">
    <input type="text" id="firstname" name="firstname" value=""><br><br>
    <input type="text" id="lastname" name="lastname" value=""><br><br>
    <br><br>
    <!-- The #chesscaptcha div where the captcha will be rendered. If needed you can change the # in the $config array -->
    <div id="chesscaptcha"><?php if($config['nojsfallback'] == 'yes'){ echo $chesscaptcha->noJsHtml($config['pieceImages']);}?></div>
    <input type="submit" id="submitform" value="Submit">
  </form>

  <script type="text/javascript" src="../assets/js/jquery-1.10.1.min.js"></script>
  <script type="text/javascript">
  // The object containing the configuration for the js side
  var chessCaptchaParams = {
      cc_divId: '<?php echo $config['divId'];?>',
      cc_mateMode: '<?php echo $config['matemode']?>' === 'no' ? false : true,
      cc_whiteSquare: '<?php echo $config['whitesquare'];?>',
      cc_blackSquare: '<?php echo $config['blacksquare'];?>',
      cc_titleOverride: '<?php echo $config['titleoverride'];?>',
      cc_mateTitleOverride: '<?php echo $config['titlemateoverride'];?>',
      cc_helpOverride: '<?php echo $config['helpoverride'];?>',
      cc_startOverride: '<?php echo $config['startoverride'];?>',
      cc_clearOverride: '<?php echo $config['clearoverride'];?>',
      cc_sideToPlay: '<?php echo $chesscaptcha->chessCaptchaFenCode[2]; ?>',
      cc_challenge: '<?php echo $chesscaptcha->chessCaptchaChallenge; ?>',  // The image of the position
      cc_matechallenge: '<?php echo $chesscaptcha->chessCaptchaFenCode[0];?>',  // The fen code of matemode position
      cc_pathtoimg: '<?php echo $config['pieceImages'];?>',
      cc_piecestyle: '<?php echo $chesscaptcha->chessCaptchaPieceStyle;?>',
  };
  </script>
  <script type="text/javascript" src="../assets/js/chesscaptcha.js"></script>
  <!-- Optional Ajax Validation -->
  <script type="text/javascript" src="../assets/js/chesscaptcha-ajax-validation.js"></script>

</body>
</html>
