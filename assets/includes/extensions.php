<div id="tdmfw">
<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.jumpforms_version();?></span></h1></div>
<ul id="tdmfw_crumbs">
	<li><a href="?page=jumpforms_dashboard">JumpForms</a></li>
	<li><a class="current"><?php _e('Extensions','jumpforms'); ?></a></li>
</ul>
		
<div id="tdmfw_content">
<div class="tdmfw_box" style="margin-top:0;">
<p class="tdmfw_box_title" style="margin-top:0;">Extensions</p>
<div class="tdmfw_box_content">			
			
		<table class="tdmfw_table"> 
			
			<!--<thead>
				<tr valign="top">
					<th style="width:50%;"><?php _e('Extension','jumpforms'); ?></th>
					<th style="width:50%;"><?php _e('Status','jumpforms'); ?></th>
				</tr>
			</thead>-->
			
			<tbody>
			
				<!--<tr>
					<td><?php if(is_plugin_active('jumpforms_paypal/index.php')) { echo '<a href="?page=jumpforms_paypal">'; _e('PayPal','jumpforms'); echo '</a>' ; } else { _e('PayPal','jumpforms'); } ?></td>
					<td>
					<?php if(file_exists('../wp-content/plugins/jumpforms_paypal/index.php')) {
						if(is_plugin_active('jumpforms_paypal/index.php')) { _e('Enabled','jumpforms'); } else { _e('Disabled','jumpforms'); }
					} else { _e('Not Installed','jumpforms'); } ?>
					</td>
				</tr>-->
				<tr>
					<td colspan="2"><?php _e('There are no extensions available yet.','jumpforms');?></td>
				</tr>
			
			</tbody>
		</table>
			
</div>
</div>
</div>
</div>