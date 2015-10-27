$("#submitform").on('click', function(e){
  var chesscaptchaChallengePosition = $( "#chesscaptchaposition" ).val();
  e.preventDefault();
  $.ajax({
    url: "/chesscaptcha/examples/chesscaptchavalidate.php",
    method: "POST",
    data: { chesscaptchaposition : chesscaptchaChallengePosition}
  })
  .done(function( msg ) {
    var res = jQuery.parseJSON(msg);
    //alert(res.valid);
    if(res.valid === true){
      $('.chesscaptcha-error').hide();
      $('.chesscaptcha-success').fadeIn();
      //$('#testform').submit(); // uncomment to submit the form
    }else{
      $('.chesscaptcha-success').hide();
      $('.chesscaptcha-error').fadeIn();
    }
  });
});
