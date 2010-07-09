<?php
/*
Simple:Press Forum
Registration Policy Form
$LastChangedDate: 2009-04-28 09:52:07 +0100 (Tue, 28 Apr 2009) $
$Rev: 1815 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_render_policy_form()
{
	$sflogin = array();
	$sflogin = get_option('sflogin');
	
	$policy = sf_get_sfmeta('registration', 'policy');
	$out='';

	$out .= '<div id="sforum">'."\n";
	$out .= '<div id="sfstandardform">'."\n";

	$out .= '<fieldset><legend><b>'.__("Registration Policy", "sforum").'</b></legend>'."\n";
	$out .= $policy[0]['meta_value']."\n";
	

	$out.= '</fieldset>'."\n";
	$out .= '<br />'."\n";
	
	if($sflogin['sfregcheck'])
	{
		$out .= '<p><label for="sfaccept">'.__("Accept Policy to Register", "sforum").'</label>'."\n";
//		$out .= '<input type="checkbox" name="accept" tabindex="1" value="" onchange="sfjtoggleRegister(this);" /></p>'."\n";
		$out .= '<input type="checkbox" name="accept" tabindex="1" value="" onclick="sfjreDirect( \''.SFREGISTER.'\');" /></p>'."\n";
	}	
	$out .= '<br />'."\n";

	if(!$sflogin['sfregcheck'])
	{
		$out .= '<input type="button" class="sfcontrol" tabindex="2" id="regbutton" name="regbutton" value="'.__('Register', "sforum").'" onclick="sfjreDirect( \''.SFREGISTER.'\');" />'."\n";
	}
	$out .= '<input type="button" class="sfcontrol" tabindex="3" id="retbutton" name="retbutton" value="'.__('Return to Forum', "sforum").'" onclick="sfjreDirect(\''.SFURL.'\');" />'."\n";
	$out .= '</div></div>'."\n";	
	
	return $out;
}

?>