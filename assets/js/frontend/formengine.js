/*jQuery(document).ready(function ($) {
	$("form#formengine").validationEngine({promptPosition : "bottomLeft"});
	$('#myTab').css("display", "none");
	$('.datepick').datepicker({format:'dd/mm/yyyy',weekStart:1});
	$('.timepick').timepicker({template : 'dropdown', showInputs: true});
   
$(function() {

if($("#myTab li").length < 2)
{
   $('.btnNext').text('Submit');
   $('.captcha').css("display", "");
}

$('#myTab li a').on('click', function() { 
	if($("form#formengine").validationEngine('validate') == false) {
		return false;
	}
});

  $('.btnNext').on('click', function() {        
    if(isLastTab()) {
		if($("form#formengine").validationEngine('validate') == true) {
			$('form#formengine').submit();
		}
    } else {
      nextTab(); }
  });
  
  $('.btnPrev').on('click', function() {        
    if(isFirstTab()) 
      $('.btnPrev').css('display') == 'none'; 
    else 
      prevTab();  
  });
  
  $('a[data-toggle="tab"]').on('shown', function (e) {
    isLastTab();
  });

});

function nextTab() {
  if($("form#formengine").validationEngine('validate') == true) {
	var e = $('#myTab li.active').next().find('a[data-toggle="tab"]');  
	if(e.length > 0) e.click();  
	isFirstTab();
	isLastTab();
  }
}

function prevTab() {
  if($("form#formengine").validationEngine('validate') == true) {
    var e = $('#myTab li.active').prev().find('a[data-toggle="tab"]');  
    if(e.length > 0) e.click(); 
    isFirstTab();
    isLastTab();
  } 
}

function isFirstTab() {
  var e = $('#myTab li:first').hasClass('active'); 
  if( e ) $('.btnPrev').css("display", "none");
    else $('.btnPrev').css("display", "");
}

function isLastTab() {
  var e = $('#myTab li:last').hasClass('active'); 
  if( e ) { $('.btnNext').text('Submit'); $('.captcha').css("display", ""); }
  else { $('.btnNext').text('Next'); $('.captcha').css("display", "none"); }
  return e;
}
	
});*/