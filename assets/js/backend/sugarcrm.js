
jQuery("#testcon").click(function() {
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action'   : 'sugartestaccess',
		    'url'      : jQuery("#url").val(),
		    'username' : jQuery("#username").val(),
		    'password' : jQuery("#password").val(),
		    'check'    : "testaccess",
		},
		success:function(data) {
			if (data.indexOf("Success") != -1) {
				jQuery("#test_result").css("fontWeight","bold").css("color","green").html("Connection successful");
			} else {
				jQuery("#test_result").css("fontWeight","bold").css("color","green").html("Credentials are wrong");
			}
		},
		error: function(errorThrown){
		    console.log(errorThrown);
		}
	});
})
