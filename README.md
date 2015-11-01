Chess Captcha
===

##### Requirements: php >= 5.4, jquery > 1.8 | Latest Version 2.0.0

This is a captcha system where the user either recreates the position of the pieces on the board - *non chess savvy users* - or she solves a mate-in-one puzzle by putting the piece on the square where it gives the checkmate - *chess savvy users only*. There is also a no js fallback that exists mostly as a placeholder for future iterations; don't use it. Chesscaptcha was built for fun, it is a work in progress so feel free to make pull requests and contribute!

**View demo [here](http://elioair.github.io/chesscaptcha/).**

Created by @elioair | twitter: [@elioair](http://twitter.com/elioair)

##### Libraries used:
+ [chessboardjs](http://chessboardjs.com/)
+ [ChessImager](https://code.google.com/p/chessimager/)
+ [Jquery](http://jquery.com)


Implementation:
===

#### Through Composer
```
composer require elioair/chesscaptcha dev-master
```
After composer finishes importing the package:
+ You may remove the `/vendor/elioair/chesscaptcha/examples` directory
+ Place the `/vendor/elioair/chesscaptcha/assets` directory or it's contents where it suits your project. **Note** that the image files are used both by the `js` client and the `php` server side.
+ Fix the image paths in the `/vendor/elioair/chesscaptcha/src/ChessCaptcha/BoardImageCreator.php`

#### Directly cloning the repo 
Clone the repository and see immediately how it works by going to the `/examples` folder. You might need to `require_once` the files of the classes involved in the class files in the `src/ChessCaptcha` folder.

*Quick tip: run `composer install` and then `composer dump-autoload --optimize` and include the loader.*

**Working Example:** `/examples/chesscaptcha-example.php`

### The php configuration

```php
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
    'pieceStyle'=>'wikipedia', // the name of the piece style to use or 'random', default is 'wikipedia'
  ];

  require_once("../src/ChessCaptcha/ChessCaptcha.php");
  $chesscaptcha = new \ChessCaptcha\ChessCaptcha($config['whitesquare'], $config['blacksquare'], $config['matemode'], $config['nojsfallback'], $config['pieceStyle']);
?>
```

### The HTML
```php
<form ...>
  ...
  <!-- The #chesscaptcha div where the captcha will be rendered. If needed you can change the # in the $config array -->
  <div id="chesscaptcha"><?php if($config['nojsfallback'] == 'yes'){ echo $chesscaptcha->noJsHtml($config['pieceImages']);}?></div>

  <input type="submit" id="submitform" value="Submit">
</form>
```

### The javascript + js options object
```html
<script>
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
    cc_pathtoimg: '../assets/img/pieces',
    cc_piecestyle: '<?php echo $chesscaptcha->chessCaptchaPieceStyle;?>',
};
</script>
<script type="text/javascript" src="../assets/js/chesscaptcha.js"></script>
```

##### Add this at the end and configure it if you want ajax validation

```html
<script type="text/javascript" src="../assets/js/chesscaptcha-ajax-validation.js"></script>
```

### Validation
Grab the `chesscaptchaposition` from the POST request and feed it into the validation method. `$validate = \ChessCaptcha\ChessCaptcha::validate($inputFen, $noJs, $colorTolerance);` see the `src/chesscaptchavalidate.php` file for more.

### Further Configuration:
Look into the `boardimagecreator.php` for settings on the image.

Issues
===
+ The position when set to Start from the button is not updated on purpose. **Avoid having an initial position as a fen string in the fen array**.

+ On some tablets, when zoomed-in the pieces are not placed correctly.

TODO
===
+ Add support for more than one instances per page.

FAQ
===
#### 1. How do I change the positions displayed?
Edit the array `$givenFen` in `src/fengenerator.php` for the simple mode and the `$givenMateInOne` for the mate mode.
#### 2. Pieces are not showing up or no board image is shown.
Make sure that you have the paths to the image files of the pieces right. E.g. `$pieceImageDir` property in `boardimagecreator.php` and `$config['$pieceImageDir']`.
#### 3. How do I make the check ignore the piece color?
Pass `true` as the third argument of the `Chesscaptcha::validate` method to make the comparison color agnostic.

License
===
Distributed under the MIT license
