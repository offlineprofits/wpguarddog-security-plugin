jQuery(function(){ 
	
  jQuery("#addform").click(function(){
  	jQuery(".small_message_box").show();
  });
  jQuery("#close_msg").click(function(){
    jQuery(".small_message_box").hide();
  });
  jQuery(".formadd").click(function() {
  	val = jQuery(this).data("value");
  	//jQuery("#content_ifr").contents().find("#tinymce").text("[formengine id="+val+"]");
  	jQuery("#content").append("[formengine id="+val+"]");
  })
  
  
  
});