
jQuery("#formlist").change(function() {
	fid = jQuery(this).val(); 
	if(fid == "Select a Form") {
		return false;
	}
	else {
		jQuery("#formlist_load").show();
		data = {
			action : 'formchange',
			fid : fid  
			};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#formlist_load").hide();
			response = JSON.parse(response.trim())
			optionHtml = "<option>Don't Map</option>";
			jQuery.each(response, function(key,value){
				optionHtml = optionHtml+"<option>"+value+"</option>"; 
			});
			jQuery("#optHtml").val(optionHtml)
		});
	}
})

jQuery("#infform").change(function() {
	if(jQuery("#formlist").val() == "Select a Form") {
		alert("Please select a Jump form first");
		return false;
	}
	
	infid = jQuery(this).val(); 
	if(infid == "Select a Form") {
		return false;
	}
	else {
		jQuery("#inflist_load").show();
		optHtml = jQuery("#optHtml").val();
		data = {
			action : 'infselect',
			inffid : infid  
			};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#inflist_load").hide();
			optHtml = jQuery("#optHtml").val();
			content = "WebForm Link<input type='text' name='link' id='link' /><br /><table><tr><td>Infusion fields</td><td>Form Fields</td></tr>"
			vname = "";
			jQuery.each(response, function(key, value){
				vname = vname+value+","
				content = content+"<tr><td>"+value+"</td><td><select id="+value+"_dp name="+value+"_dp >"+optHtml+"</select></td></tr>";			
			})
			vname = vname.substring(0, vname.length-1);
			jQuery("#hiddenvname").val(vname);
			content = content + "</table>"
			jQuery("#listings").append(content);
		},"json");
	}
})

jQuery("#addengine").click(function() {
	if(jQuery("#formlist").val() == "Select a Form") {
		alert("Please select a jump form");
		return false;
	}
	else if(jQuery("#infform").val() == "Select a Form") {
		alert("Please select a infusionsoft form");
		return false;
	}
	
	alert("If you have previous feed for the same form, it will be updated");
});
