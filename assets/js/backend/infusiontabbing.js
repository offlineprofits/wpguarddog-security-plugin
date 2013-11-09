jQuery(function() {
	
	jQuery("#settingsview").fadeIn("slow");
	jQuery("#feedsview").hide();
	jQuery("#addinfview").hide();
	jQuery("#settings").css("color","black");
	jQuery("#feeds").css("color","");
	jQuery("#addinf").css("color","");
	jQuery("#settings").click(function() {
		jQuery("#settingsview").fadeIn("slow");
		jQuery("#feedsview").hide();
		jQuery("#addinfview").hide();
		jQuery(this).css("color","black");
		jQuery("#feeds").css("color","");
		jQuery("#addinf").css("color","");
		
	})
	jQuery("#feeds").click(function() {
		jQuery("#feedsview").fadeIn("slow");
		jQuery("#settingsview").hide();
		jQuery("#addinfview").hide();
		jQuery(this).css("color","black");
		jQuery("#addinf").css("color","");
		jQuery("#settings").css("color","");
	})
	jQuery("#addinf").click(function() {
		jQuery("#addinfview").fadeIn("slow");
		jQuery("#settingsview").hide();
		jQuery("#feedsview").hide();
		jQuery(this).css("color","black");
		jQuery("#settings").css("color","");
		jQuery("#feeds").css("color","");
	})
})
