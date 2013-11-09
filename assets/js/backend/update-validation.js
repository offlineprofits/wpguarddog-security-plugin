jQuery(function(){
	jQuery("input[value='Save Changes']").on('click',function() {
		if( jQuery("input[name=accesstoken]").val() == "" ) {
			alert("Please enter an Access Token");
			return false;
		}
		if( jQuery("input[name=organizerkey]").val() == "" ) {
			alert("Please enter an Organizer Key");
			return false;
		}
		if(jQuery("#infint").is(':checked')) {
			if(jQuery("input[name='formid']").val() == "") {
				alert("Please enter the form id");
				return false;
			}
			if(jQuery("input[name='formversion']").val() == "") {
				alert("Please enter the form version");
				return false;
			}
			if(jQuery("input[name='formname']").val() == "") {
				alert("Please enter the form name");
				return false;
			}
			if(jQuery("input[name='ipaddress']").val() == "") {
				alert("Please enter the ipaddress");
				return false;
			}
		}
		
	})
	
	//for infusion integration
	jQuery("#infint").click(function() {
		if(jQuery(this).is(':checked')) {
	  		jQuery(".rowinfusion").fadeIn("slow");
	  	}
	  	else {
	  		jQuery(".rowinfusion").fadeOut("slow");
	  	}
	})
	  
	if(jQuery("#infint").is(':checked')) {
		jQuery(".rowinfusion").show();
	} 
		
})
