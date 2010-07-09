<?php
/*
Simple:Press Forum
In Line Login
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_render_inline_login_form()
{
	$user_login = '';
	$user_pass = '';
	$using_cookie = false;
	
	$redirect_to=SFURL;

	$out = '<div id="sfloginform"><fieldset>'."\n";
	$out.= '<form name="loginform" id="loginform" action="'.SFLOGIN.'" method="post">'."\n";

	$out.= '<p class="sfalignright"><label for="log">'.__("Username:", "sforum").'<input type="text" class="sfcontrol" tabindex="100" name="log" id="log" value="'.wp_specialchars(stripslashes($user_login), 1).'" size="11" /></label>'."\n";
	$out.= '<label for="pwd">'.__("Password:", "sforum").'<input type="password" class="sfcontrol" tabindex="101" name="pwd" id="login_password" value="" size="11"  /></label></p>'."\n";

	$out.= '<div class="sfclear"></div>';

	$out.= '<p class="sfalignright"><input type="checkbox" tabindex="102" id="sfrememberme" name="rememberme" value="forever" /><label for="sfrememberme">'.__("Remember me", "sforum").'</label></p>';

	$out.= '<div class="sfclear"></div>';

	$out.= '<p class="sfalignright"><input type="submit" class="sfcontrol" name="submit" id="submit" value="'.__("Login", "sforum").'" tabindex="103" /></p>'."\n";
	$out.= '<input type="hidden" name="redirect_to" value="'.wp_specialchars($redirect_to).'" />'."\n";
	$out.= '</form>'."\n";
	
	$out.= '<div class="sfclear"></div>';

	$out.= '<form name="lpform" id="lpform" action="'.SFLOSTPASS.'" method="post">'."\n";
	$out.= '<p class="sfalignright"><input type="submit" class="sfcontrol sfalignright" tabindex="104" name="button6" value="'.__('Lost Password', "sforum").'" /></p>'."\n";
	$out.= '</form>'."\n";

	$out.= '</fieldset></div>'."\n";
	return $out;
}

?>