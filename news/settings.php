<?php
/************************************************************************
 * @project Gutuma Newsletter Managment
 * @author Rowan Seymour
 * @copyright This source is distributed under the GPL
 * @file The settings page
 * @modifications Cyril Maguire
 */
/* Gutama plugin package
 * @version 1.6
 * @date	01/10/2013
 * @author	Cyril MAGUIRE
*/


include_once 'inc/gutuma.php';
include_once 'inc/mailer.php';

if ($_SESSION['profil'] != PROFIL_ADMIN){
	header('Location:compose.php');
	exit();
}
gu_init();

$section = is_get_var('section') ? get_get_var('section') : 'general';
$section_titles['general'] = t("General settings");
$section_titles['transport'] = t("Transport settings");
$section_titles['messages'] = t("Message settings");

// Save settings
if (is_post_var('save_settings')) {
	if ($section == 'general') {
		gu_config::set('collective_name', get_post_var('collective_name'));
		gu_config::set('application_name', get_post_var('application_name'));
		gu_config::set('admin_name', get_post_var('admin_name'));
		gu_config::set('admin_email', get_post_var('admin_email'));
		gu_config::set('theme_name', get_post_var('theme_name'));
		
		/*gu_config::set('admin_username', get_post_var('admin_username'));
		$pass1 = get_post_var('admin_password');
		$pass2 = get_post_var('admin_password_retype');
		if ($pass1 != '') {
			if ($pass1 == $pass2)
				gu_config::set('admin_password', $pass1);
			else
				gu_error(t('Typed passwords did not match'));
		}*/
	}
	elseif ($section == 'transport') {
		gu_config::set('use_smtp', is_post_var('use_smtp'));	
		gu_config::set('smtp_server', get_post_var('smtp_server'));
		gu_config::set('smtp_port', (int)get_post_var('smtp_port'));
		gu_config::set('smtp_encryption', get_post_var('smtp_encryption'));
		gu_config::set('smtp_username', get_post_var('smtp_username'));
		gu_config::set('smtp_password', get_post_var('smtp_password'));
		gu_config::set('use_sendmail', is_post_var('use_sendmail'));
		gu_config::set('use_phpmail', is_post_var('use_phpmail'));
		gu_config::set('batch_max_size', (int)get_post_var('batch_max_size'));
		gu_config::set('batch_time_limit', (int)get_post_var('batch_time_limit'));		
	}
	elseif ($section == 'messages') {
		gu_config::set('msg_prefix_subject', is_post_var('msg_prefix_subject'));
		gu_config::set('msg_coll_name_on_multilist', is_post_var('msg_coll_name_on_multilist'));
		gu_config::set('msg_append_signature', is_post_var('msg_append_signature'));
		gu_config::set('msg_admin_copy', is_post_var('msg_admin_copy'));
		gu_config::set('list_send_welcome', is_post_var('list_send_welcome'));
		gu_config::set('list_send_goodbye', is_post_var('list_send_goodbye'));
		gu_config::set('list_subscribe_notify', is_post_var('list_subscribe_notify'));
		gu_config::set('list_unsubscribe_notify', is_post_var('list_unsubscribe_notify'));
	}		
			
	if (gu_config::save() )
		gu_success(t('Settings successfully saved'));
		
}
// Send test message
elseif (is_post_var('test_settings')) {
	// Don't bother if there is no admin email
	if (gu_config::get('admin_email') != '') 
		gu_sender_test();
	else
		gu_error(t('Administrator email must be set before mail can be sent'));
}

function gu_sender_test()
{
	// Get current settings, which may not have been saved
	$use_smtp = is_post_var('use_smtp');
	$smtp_server = get_post_var('smtp_server');
	$smtp_port = (int)get_post_var('smtp_port');
	$smtp_encryption = get_post_var('smtp_encryption');
	$smtp_username = get_post_var('smtp_username');
	$smtp_password = get_post_var('smtp_password');	
	$use_sendmail = is_post_var('use_sendmail');
	$use_phpmail = is_post_var('use_phpmail');
	
	if (!($use_smtp || $use_sendmail || $use_phpmail))
		return gu_error(t('No method of mail transportation has been configured'));
	
	$test_msg = t('If you have received this email then your settings clearly work!');
	
	// Test SMTP settings first
	if ($use_smtp) {
		$mailer = new gu_mailer();
		if ($mailer->init(TRUE, $smtp_server, $smtp_port, $smtp_encryption, $smtp_username, $smtp_password, FALSE, FALSE)) {
			if (!$mailer->send_admin_mail('['.gu_config::get('collective_name').'] Testing SMTP', $test_msg))
				return gu_error(t('Unable to send test message using SMTP'));
		}
		else
			return gu_error(t('Unable to initialize mailer with the SMTP settings'));
			
		$mailer->disconnect();
	}
	// Test Sendmail next
	if ($use_sendmail) {
		$mailer = new gu_mailer();
		if ($mailer->init(FALSE, '', '', '', '', '', FALSE, FALSE)) {
			if (!$mailer->send_admin_mail('['.gu_config::get('collective_name').'] Testing Sendmail', $test_msg))
				return gu_error(t('Unable to send test message using Sendmail'));
		}
		else
			return gu_error(t('Unable to initialize mailer with Sendmail'));
			
		$mailer->disconnect();		
	}
	// Test PHP mail next
	if ($use_phpmail) {
		$mailer = new gu_mailer();
		if ($mailer->init(FALSE, '', '', '', '', '', FALSE, TRUE)) {
			if (!$mailer->send_admin_mail('['.gu_config::get('collective_name').'] Testing PHP mail', $test_msg))
				return gu_error(t('Unable to send test message using PHP mail'));
		}
		else
			return gu_error(t('Unable to initialize mailer with PHP mail'));
			
		$mailer->disconnect();
	}
	
	gu_success(t('Test messages sent to <br><i>%</i></b>',array(gu_config::get('admin_email'))));
}

gu_theme_start();

//Body
include_once 'themes/'.gu_config::get('theme_name').'/_settings.php';

gu_theme_end();
?>
