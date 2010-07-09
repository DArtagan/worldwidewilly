<?php
/*
Simple:Press Forum
New Users Email - SPF Option
$LastChangedDate: 2009-04-28 00:35:09 +0100 (Tue, 28 Apr 2009) $
$Rev: 1813 $
*/


# = NEW USER EMAIL REPLACEMENT ================
if(!function_exists('wp_new_user_notification')):
function wp_new_user_notification($user_id, $user_pass='')
{
	$user = new WP_User($user_id);

	$eol = "\r\n";
	$message='';

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	$message .= sprintf(__('New user registration on your blog %s:', "sforum"), get_option('blogname')).$eol.$eol;
	$message .= sprintf(__('Username: %s', "sforum"), $user_login).$eol;
	$message .= sprintf(__('E-mail: %s', "sforum"), $user_email).$eol;
	$message .= sprintf(__('Registration IP: %s', "sforum"), $_SERVER['REMOTE_ADDR']).$eol;

	sf_send_email(get_option('admin_email'), sprintf(__('[%s] New User Registration', "sforum"), get_option('blogname')), $message);

	if ( empty($user_pass) )
		return;

	$mailoptions = get_option('sfnewusermail');
	$subject = stripslashes($mailoptions['sfnewusersubject']);
	$body = stripslashes($mailoptions['sfnewusertext']);
	if((empty($subject)) || (empty($body)))
	{
		$subject = sprintf(__('[%s] Your username and password', "sforum"), get_option('blogname')).$eol.$eol;
		$body = sprintf(__('Username: %s', "sforum"), $user_login).$eol;
		$body.= sprintf(__('Password: %s', "sforum"), $user_pass).$eol.$eol;
		$body.= SFLOGINEMAIL.$eol;
	} else {
		$blogname = get_bloginfo('name');
		$subject = str_replace('%USERNAME%', $user_login, $subject);
		$subject = str_replace('%PASSWORD%', $user_pass, $subject);
		$subject = str_replace('%BLOGNAME%', $blogname, $subject);
		$subject = str_replace('%SITEURL%', SFURL, $subject);
		$subject = str_replace('%LOGINURL%', SFLOGINEMAIL, $subject);
		$body = str_replace('%USERNAME%', $user_login, $body);
		$body = str_replace('%PASSWORD%', $user_pass, $body);
		$body = str_replace('%BLOGNAME%', $blogname, $body);
		$body = str_replace('%SITEURL%', SFURL, $body);
		$body = str_replace('%LOGINURL%', SFLOGINEMAIL, $body);
		$body = str_replace('%NEWLINE%', $eol, $body);
	}
	str_replace('<br />', $eol, $body);

	sf_send_email($user_email, $subject, $body);
	return;
}
endif;

?>