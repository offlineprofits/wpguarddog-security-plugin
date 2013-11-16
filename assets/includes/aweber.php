<?php



 

?>


<div id="tdmfw">
	<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
		<ul id="tdmfw_crumbs">
			<li><a href="?page=formengine_dashboard">JumpForms</a></li>
			<li><a class="current"><?php _e('Aweber','formengine'); ?></a></li>
			
		</ul>
	
	<div id="tdmfw_content">
		<div class="tdmfw_box" style="margin-top:0;">
			<p class="tdmfw_box_title" style="margin-top:0;">	
			</p>
			<div class="tdmfw_box_content">
				<div id="settingsview" style="display: none;">
					<form method="post">
						API Key    <input type="text" id="apikey" name="apikey" style="width: 300px;" value="<?php ?>" /><br /><br /><br />
						<input type="submit" class="btn btn-primary" value="Save Changes" name="aweber_save" />

						<!--Sub Domain <input type="text" id="subdomain" name="subdomain" value="<?php  ?>" /><br /><br />
						<input type="submit" value="Save Changes" name="settings_submit" class="btn btn-info" />-->
					</form>
				</div>	
			</div>
		</div>
	</div>
</div>
