<?php
/*
Simple:Press Forum
Login (etc) Form Actions and Filters
$LastChangedDate: 2009-05-17 10:03:04 +0100 (Sun, 17 May 2009) $
$Rev: 1867 $
*/

include_once(SF_PLUGIN_DIR.'/forum/sf-primitives.php');

function sf_login_header()
{
	if(is_admin()) return;

	$sflogin=array();
	$sflogin=get_option('sflogin');
	if(!$sflogin['sfloginskin']) return;

	if(isset($_REQUEST['view']))
	{
		$sfstyle=array();
		$sfstyle=get_option('sfstyle');

		echo '<link rel="stylesheet" type="text/css" href="'.SF_PLUGIN_URL.'/styles/skins/'.$sfstyle['sfskin'].'/sf-credentials.css" />'."\n";
		echo '<script type="text/javascript" src="'.SF_PLUGIN_URL.'/credentials/sf-credentials.js"></script>'."\n";

//		if(function_exists('site_url'))
//		{
//			$base  = trailingslashit(site_url());
//		} else {
//			$base = get_option('siteurl');
//		}
		$base=SFHOME;
		$forum = SFURL;

		?>
		<script type="text/javascript">
			window.onload=function(){
			sfjsetCredentials("<?php echo($base); ?>", "<?php echo($forum); ?>");
			}
		</script>
		<?php
	}
}

function sf_login_url()
{
	if(is_admin()) return;

	$sflogin=array();
	$sflogin=get_option('sflogin');
	if(!$sflogin['sfloginskin']) return;

	if(isset($_REQUEST['view']))
	{
		echo SFURL;
	}
}

function sf_login_title()
{
	if(is_admin()) return;

	$sflogin=array();
	$sflogin=get_option('sflogin');
	if(!$sflogin['sfloginskin']) return;

	if(isset($_REQUEST['view']))
	{
		echo get_option('blogname');
	}
}

function sf_login_form_action()
{
	if(is_admin()) return;

	$sflogin=array();
	$sflogin=get_option('sflogin');
	if(!$sflogin['sfloginskin']) return;

	if(isset($_REQUEST['view']))
	{
	?>
		<p class="submit"><input type="button" name="button1" value="<?php _e('Forum', "sforum"); ?>" onclick="sfjreDirect('<?php echo(SFURL); ?>');" /></p>
	<?php
	}
}

function sf_register_as_forum()
{
	if(is_admin()) return;

	if(isset($_REQUEST['view']))
	{
		add_sfsetting($_SERVER['REMOTE_ADDR'], 'Registering');
	}
}

function sf_post_login_check($login_name)
{
	$dname = sf_get_login_display_name($login_name);

//	if(function_exists('site_url'))
//	{
//		$base  = trailingslashit(site_url());
//	} else {
//		$base = get_option('siteurl');
//	}
	$base=SFHOME;
	
	$cookiepath = preg_replace('|https?://[^/]+|i', '', $base );
	setcookie('sforum_' . COOKIEHASH, stripslashes($dname), time() + 30000000, $cookiepath, false);

	delete_sfsetting($_SERVER['REMOTE_ADDR']);
}

function sf_get_login_display_name($login_name)
{
	global $wpdb;

	return $wpdb->get_var(
			"SELECT ".SFMEMBERS.".display_name
			 FROM ".SFMEMBERS."
			 JOIN ".SFUSERS." ON ".SFUSERS.".ID = ".SFMEMBERS.".user_id
			 WHERE user_login = '".$login_name."';");
}

?>