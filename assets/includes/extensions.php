<div id="tdmfw">
<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.formengine_version();?></span></h1></div>
<ul id="tdmfw_crumbs">
	<li><a href="?page=formengine_dashboard">JumpForms</a></li>
	<li><a class="current"><?php _e('Extensions','formengine'); ?></a></li>
</ul>
		
<div id="tdmfw_content">
<div class="tdmfw_box" style="margin-top:0;">
<p class="tdmfw_box_title" style="margin-top:0;">Extensions</p>
<div class="tdmfw_box_content">			
			
		<table class="tdmfw_table"> 
			
			<!--<thead>
				<tr valign="top">
					<th style="width:50%;"><?php _e('Extension','formengine'); ?></th>
					<th style="width:50%;"><?php _e('Status','formengine'); ?></th>
				</tr>
			</thead>-->
			
			<tbody>
			
				<!--<tr>
					<td><?php if(is_plugin_active('formengine_paypal/index.php')) { echo '<a href="?page=formengine_paypal">'; _e('PayPal','formengine'); echo '</a>' ; } else { _e('PayPal','formengine'); } ?></td>
					<td>
					<?php if(file_exists('../wp-content/plugins/formengine_paypal/index.php')) {
						if(is_plugin_active('formengine_paypal/index.php')) { _e('Enabled','formengine'); } else { _e('Disabled','formengine'); }
					} else { _e('Not Installed','formengine'); } ?>
					</td>
				</tr>-->
				<tr>
					<td colspan="2"><?php _e('There are no extensions available yet.','formengine');?></td>
				</tr>
			
			</tbody>
		</table>
			
</div>
</div>
</div>
</div>