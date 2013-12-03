<div id="tdmfw">
<div id="tdmfw_header"><h1>JumpForms<span style="float:right;"><?php echo 'v'.jumpforms_version();?></span></h1></div>
<ul id="tdmfw_crumbs">
	<li><a href="?page=jumpforms_dashboard">JumpForms</a></li>
	<li><a class="current"><?php _e('Documentation','jumpforms'); ?></a></li>
</ul>
		
<div id="tdmfw_content">

<div class="tdmfw_box" style="margin-top:0;">
<p class="tdmfw_box_title" style="margin-top:0;">
<?php $docsmax = 12; if(isset($_GET['did']) && ($_GET['did'] <= $docsmax)) { $did = $_GET['did']; } else { $did = 1; } ?>


<?php
	// SET TITLE
	if($did == '1') { _e('Documentation','jumpforms'); }
	elseif($did == '2') { _e('Getting Started','jumpforms'); }
	elseif($did == '3') { _e('The Dashboard','jumpforms'); }
	elseif($did == '4') { _e('Create New Form','jumpforms'); }
	elseif($did == '5') { _e('Form Configuration','jumpforms'); }
	elseif($did == '6') { _e('Notifications','jumpforms'); }
	elseif($did == '7') { _e('The Form Builder','jumpforms'); }
	elseif($did == '8') { _e('Responses','jumpforms'); }
	elseif($did == '9') { _e('Import/Export','jumpforms'); }
	elseif($did == '10') { _e('Statistics','jumpforms'); }
	elseif($did == '11') { _e('Custom CSS','jumpforms'); }
	elseif($did == '12') { _e('Further Support','jumpforms'); }
?>

<div class="tdmfw_box_content">			
			


	<?php if($did == '1') { ?>

		<table class="tdmfw_table"> 
			<thead>
				<tr valign="top">
					<th><?php _e('Topic','jumpforms'); ?></th>
				</tr>
			</thead>
			<tbody>
			
			<tr><td><a href="?page=jumpforms_documentation&did=2"><?php _e('Getting Started','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=3"><?php _e('The Dashboard','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=4"><?php _e('Create New Form','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=5"><?php _e('Form Configuration','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=6"><?php _e('Notifications','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=7"><?php _e('The Form Builder','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=8"><?php _e('Responses','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=9"><?php _e('Import/Export','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=10"><?php _e('Statistics','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=11"><?php _e('Custom CSS','jumpforms'); ?></a></td></tr>
			<tr><td><a href="?page=jumpforms_documentation&did=12"><?php _e('Further Support','jumpforms'); ?></a></td></tr>

			</tbody>
		</table>


				
	<?php } ?>
	
	<?php if($did == '2') { ?>
	Welcome to JumpForms.<br/><br/>
	So what is JumpForms? JumpForms makes it easy to build forms for your WordPress site. It isn't the first form builder for WordPress and it certainly will not be the last. We believe, however, that JumpForms has amazing potential.<br/><br/>
	JumpForms focuses on keeping things simple, but it has the potential to be extremely powerful. It has been downloaded hundreds of times and is updated regularly.<br/><br/>
	We hope you enjoy using JumpForms &mdash; and it's great to have you on board! If you have any questions please <a href="http://www.wpfrogs.com">get in touch</a>.
	<?php } ?>
	
	
	
	<?php if($did == '3') { ?>
	When you first activate JumpForms, you'll notice a small icon is added to your WordPress administrative menu &mdash; this is a link to the JumpForms Dashboard.<br/><br/>
	The JumpForms Dashboard is your starting point and is the place you go to create new forms, analyse form statistics and review user responses. It will look rather empty at first, but this will soon change as you start to build forms and your website visitors begin interacting with them.<br/><br/>
	At the top of the dashboard (and all other JumpForms pages) you will see breadcrumb links &mdash; these simply help to let you know where you are and they can also be used to jump back and forward between links.<br/><br/>
	When a JumpForms update is available to download, a notification will be displayed.<br/><br/>
	Below the breadcrumb links is the news bar &mdash; the latest JumpForms news will be displayed here.<br/><br/>
	Also visible on the dashboard are the three latest form responses and a list of all your forms. You cannot see these until a form has been setup.<br/><br/>
	At the very bottom of the dashboard is a button to create a new form.
	<?php } ?>
	
	
	
	<?php if($did == '4') { ?>
	On the new form page you can choose from a number of different templates. These templates help speed up creating basic setups such as standard contact forms.<br/><br/>
	You can also import other JumpForms forms. To do this you will need the export code which is viewable when you have a form setup.
	<?php } ?>
	
	
	
	<?php if($did == '5') { ?>
	Provided that you have more than one form setup, your JumpForms dashboard will begin to change and a list of all of your forms will be available. Each form has a settings page which can be access by clicking the form name. From the dashboard you can also get a quick overview of the number of views and responses each of your forms has had.<br/><br/>
	The form settings page is split into various sections.<br/><br/>
	<strong>Configuration</strong><br/><br/>
		&bull; Progress Bars &dash; Choose whether or not to display a progress bar above your form<br/>
		&bull; Thank You Page &dash; Choose the page where users are redirected to after completing a form<br/>
		&bull; Error Page &dash; Choose the page where users are redirected to if there is an error<br/>
		&bull; CAPTCHA &dash; Choose whether or not to display a CAPTCHA field at the end of your form<br/>
		&bull; Modal Text &dash; Enter the text you would like to display on the popup form button<br/><br/>
		To quickly create a page and embed your form in it, click the 'Create Form Page' button at the bottom of the page<br/><br/>
		To embed your form manually, create a new page and enter the following short [jumpforms id=X] where X is the ID number of your form<br/><br/>
		To embed a popup form, create a new page and enter the following short [jumpforms_modal id=X] where X is the ID number of your form
	<?php } ?>
	
	
	
	<?php if($did == '6') { ?>
	Provided that you have more than one form setup, your JumpForms dashboard will begin to change and a list of all of your forms will be available. Each form has a settings page which can be access by clicking the form name. From the dashboard you can also get a quick overview of the number of views and responses each of your forms has had.<br/><br/>
	The form settings page is split into various sections.<br/><br/>
	<strong>Notifications</strong><br/><br/>
		&bull; Notifications &dash; Choose who receives notifications; admin, user or both<br/>
		&bull; Notifications Type &dash; Choose whether or not to include response data in notification emails<br/>
		&bull; Email Address &dash; Enter the admin email address where notification emails are sent<br/>
		&bull; Email Subject &dash; Enter the subject line for notification emails<br/>
		&bull; Email Message &dash; Enter the body text for notification emails<br/>
	<?php } ?>
	
	
	
	<?php if($did == '7') { ?>
	Provided that you have more than one form setup, your JumpForms dashboard will begin to change and a list of all of your forms will be available. Each form has a settings page which can be access by clicking the form name. From the dashboard you can also get a quick overview of the number of views and responses each of your forms has had.<br/><br/>
	The form settings page is split into various sections.<br/><br/>
	<strong>Form Builder</strong><br/><br/>
	
	There are a range of different types of fields you can have in your form.<br/><br/>
	
	&bull; Single Line Text &dash; Displays a standard input box<br/>
	&bull; Paragraph Text &dash; Displays a larger text box for longer text input e.g. comments]<br/>
	&bull; Email Address &dash; Displays a standard input box and requires a valid email address<br/>
	&bull; Password &dash; Displays a standard password box and masks the text<br/>
	&bull; Date Picker &dash; Displays a popup calendar to choose a date<br/>
	&bull; Time Picker &dash; Displays a popup form to choose a time<br/>
	&bull; Checkboxes &dash; Displays a set of checkboxes<br/>
	&bull; Dropdown Menu &dash; Displays a dropdown box<br/>
	&bull; Multiple Choice &dash; Displays a set of option buttons<br/>
	&bull; Inline Multiple Choice &dash; Displays a set of option buttons in a row<br/>
	&bull; File Upload &dash; Allows users to upload a file to attach a file<br/>
	&bull; Text &dash; Displays text in Value field to break up the form<br/>
	&bull; Acceptance &dash; Displays a checkbox which has to be ticked e.g. for terms and conditions<br/><br/>
	
	There are also a selection of special fields.<br/><br/>

	&bull; Counties &dash; Dropdown box containing all countries<br/>
	&bull; Counties, UK &dash; Dropdown box containing all UK counties<br/>
	&bull; States, USA &dash; Dropdown box containing all American states<br/>
	&bull; States, Canada &dash; Dropdown box containing all Canadian states<br/>
	&bull; States, Australia &dash; Dropdown box containing all Australian states<br/><br/>
	
	For Single Line Text and Paragraph Text fields, you can specify a default value in the Value field.<br/><br/>
	
	Note: When using either of the following...<br/><br/>
	
	&bull; Checkboxes<br/>
	&bull; Dropdown Menu<br/>
	&bull; Multiple Choice<br/><br/>
	
	You set the options in the Value field separated by commas, for example Red, Blue, Green, Yellow.<br/><br/>
	
	You can set any field to be mandatory by ticking the Required checkbox.<br/><br/>
	
	There are also Section Start and Section End fields. Your form will only work properly if you it begins and ends with these. If you want multiple steps make sure a Section End field is immediately followed by another Section Start field.

	<?php } ?>
	
	
	
	<?php if($did == '8') { ?>
	Provided that you have more than one form setup, your JumpForms dashboard will begin to change and a list of all of your forms will be available. Each form has a settings page which can be access by clicking the form name. From the dashboard you can also get a quick overview of the number of views and responses each of your forms has had.<br/><br/>
	The form settings page is split into various sections.<br/><br/>
	<strong>Responses</strong><br/><br/>
	You can view a list of responses in this section. Each response can be clicked to see further details.
	<?php } ?>
	
	
	
	<?php if($did == '9') { ?>
	Provided that you have more than one form setup, your JumpForms dashboard will begin to change and a list of all of your forms will be available. Each form has a settings page which can be access by clicking the form name. From the dashboard you can also get a quick overview of the number of views and responses each of your forms has had.<br/><br/>
	The form settings page is split into various sections.<br/><br/>
	<strong>Import/Export</strong><br/><br/>
	JumpForms form structure and data can be imported and exported with ease.<br/><br/>
	
	&bull; Import data into form &dash; Upload a CSV file of responses into your form<br/>
	&bull; Export form &dash; Displays export code required to re-import and/or share your form<br/>
	&bull; Export data to .CSV &dash; Generates a .CSV file of form responses<br/>
	&bull; Export data to .CSV &dash; Generates a .TXT file of form responses
	
	<?php } ?>
	
	
	
	<?php if($did == '10') { ?>
	Provided that you have more than one form setup, your JumpForms dashboard will begin to change and a list of all of your forms will be available. Each form has a settings page which can be access by clicking the form name. From the dashboard you can also get a quick overview of the number of views and responses each of your forms has had.<br/><br/>
	The form settings page is split into various sections.<br/><br/>
	<strong>Statistics</strong><br/><br/>
	You can view basic statistics for each form.<br/><br/>
	
	&bull; Views &dash; The number of times your form has been viewed by non-administrators<br/>
	&bull; Completed &dash; The number of times your form has been completed<br/>
	&bull; Convertion &dash; The percentage of visitors that are completing your form<br/>
	
	<?php } ?>




	<?php if($did == '11') { ?>
	You can customise the look and feel of your JumpForms forms without ever having to touch the underlying code.<br/><br/>
	
	<strong>How to target all forms</strong><br/><br/>
	If you want to write a CSS rule that will target all of your forms, make sure you prefix each CSS rule with #jumpforms.<br/><br/>
	
	Example:<br/><br/>
	
	<span style="font-family:courier">
	&#35;jumpforms &#123;<br/>
	&nbsp;&nbsp;&nbsp;background-color:&#35;EEE;<br/>
	&#125;<br/><br/>
	</span>
	
	<strong>How to target a specific form</strong><br/><br/>
	
	If you want to write a CSS rule that will affect only one form, make sure you prefix each CSS rule with #jumpforms.jumpforms_form_ID where ID is the ID number of the form.<br/><br/>

	Example:<br/><br/>
	
	<span style="font-family:courier">
	&#35;jumpforms.jumpforms_form_1 label &#123;<br/>
	&nbsp;&nbsp;&nbsp;font-weight:bold;<br/>
	&#125;
	</span>

	<?php } ?>
	
	
	
	<?php if($did == '12') { ?>
	JumpForms is in active development and will continue to receive regular updates. If you have any questions, suggestions or complaints please do not hesitate to <a href="http://www.wpfrogs.com">get in touch</a>.<br/><br/>
	We are also available for additional work &mdash; so if you would like a custom add-on, extension or modification to JumpForms then please <a href="http://www.wpfrogs.com">get in touch</a> and we'll be happy to discuss this with you.<br/><br/>
	Thank you for using JumpForms.<br/><br/>
	Best regards &mdash; wpfrogs
	<?php } ?>
	
	
			
</div>
</div>

<?php if($did-1 > 0) { ?><a style="float:left;margin-top:20px;" class="button-secondary" href="?page=jumpforms_documentation&did=<?php echo $did-1?>"><?php _e('Previous Topic','jumpforms'); ?></a> <?php } ?>
<?php if($did+1 <= $docsmax) { ?><a style="float:right;margin-top:20px;" class="button-secondary" href="?page=jumpforms_documentation&did=<?php echo $did+1;?>"><?php _e('Next Topic','jumpforms'); ?></a> <?php } ?>

</div>
</div>