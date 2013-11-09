jQuery(function() {
	i = 0;
	jQuery("select[name=f2_label]").change(function() {
		
		if(i == 0) {
			jQuery(this).after("<div id='startend'></div>")
		}
		i++;
		id = jQuery(this).val();
		if(id == "Select a Webinar") {
				jQuery("#startend").html("");
      			return false;
      		}
      		else {
      			jQuery("#loading").show();
      			jQuery.post("http://localhost/vishnurs/Dev/wp-content/plugins/formengine/ajax.php", {
           		webinarid : id, 
           		action : "fetchwebinar"
           		},function(data){
           			jQuery("#loading").hide();
           			response = jQuery.parseJSON(data);
           			char1 = response.times[0].startTime.split('T');
           			char2 = response.times[0].endTime.split('T');
           			jQuery("#startend").html("<br />Name : "+response.subject+"<br /> Description : "+response.description)
           			
           		})
      		}
			
	})
})
