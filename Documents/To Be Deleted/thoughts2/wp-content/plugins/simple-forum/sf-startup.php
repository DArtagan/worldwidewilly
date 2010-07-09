<?php
/*
Simple:Press Forum
Start Up Functions to support the forum
$LastChangedDate: 2009-02-20 20:17:00 +0000 (Fri, 20 Feb 2009) $
$Rev: 1430 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ------------------------------------------------------------------
# sf_setup_header()
#
# Constructs the header fr the forum - Javascript and CSS
# ------------------------------------------------------------------
function sf_setup_header()
{
	global $wp_query, $current_user, $wp_rewrite, $sfglobals;

	# The CSS is being set early in case we have to bow out quickly due to
	# the forum needing to be ugraded. This is a 3.2 change to ensure that
	# this is the very FIRST thing to happen in the header
	echo '<link rel="stylesheet" type="text/css" href="' . SFSKINCSS .'" />' . "\n";

	# So - check if it needs to be upgraded...
	if(sf_get_system_status() != 'ok') return sf_forum_unavailable();

	remove_filter('the_content','wpautop');

	# If page is password protected, ensure it matches before starting
	if (!empty($wp_query->post->post_password))
	{
		if ($_COOKIE['wp-postpass_'.COOKIEHASH] != $wp_query->post->post_password)
		{
			return;
		}
	}
	$value = SFSIZE;
	if(!empty($value))
	{
		$value=trim($value, '%');
		if(intval($value) != 0)
		{
			echo '<style type="text/css">';
			echo '#sforum {font-size: '.$value.'%; }';
			echo '</style>'."\n";
		}
	}

	# Load up the cache for permissions data
	sf_build_membership_cache();
	sf_build_permissions_cache();
	sf_build_roles_cache();
	sf_build_ranks_cache();
	sf_setup_globals();

	# permission for file uploads?
	if (sf_is_forum_admin($current_user->ID)) $current_user->forumadmin = true;
	if (($current_user->ID == '') || ($current_user->ID == 0)) $current_user->guest = true;

	if($wp_rewrite->using_permalinks())
	{
		$forumid = sf_get_forum_id(get_query_var('sf_forum'));
	} else {
		$stuff=array();
		$stuff=explode('/', $_SERVER['QUERY_STRING']);
		$forumid = sf_get_forum_id($stuff[1]);
	}

	if (isset($forumid))
	{
		$perm = array();
		$perm[0] = 'Can upload images';
		$they = sf_get_permissions($perm, $forumid);
		$current_user->sfuploads = $they['Can upload images'];
	} else {
		$current_user->sfuploads = false;
	}

	?>
	<link rel="stylesheet" type="text/css" href="<?php echo(SFJSCRIPT); ?>highslide/highslide.css" />

	<script type="text/javascript">
		hs.graphicsDir = "<?php echo(SFJSCRIPT); ?>highslide/graphics/";
		hs.outlineType = "rounded-white";
		hs.outlineWhileAnimating = true;
		hs.cacheAjax = false;
		hs.showCredits = false;
		hs.lang = {
			closeText : '',
			closeTitle : '<?php _e("Close", "sforum");?>',
			moveText  : '',
			moveTitle : '<?php _e("Move", "sforum");?>',
			loadingText  : '<?php _e("Loading", "sforum");?>'
		};
	</script>

	<?php if(SF_USE_PRETTY_CBOX) { ?>
	<script type="text/javascript">
		jQuery.noConflict();
		jQuery(document).ready(function() { 
			jQuery("input[type=checkbox],input[type=radio]").prettyCheckboxes();
		});
	</script>
	<?php }

	switch($sfglobals['editor']['sfeditor'])
	{
	case RICHTEXT:
		include_once(SFEDITORDIR."tinymce/sf-tinyinit.php");
		break;
	case HTML:
		include_once(SFEDITORDIR."html/sf-htmlinit.php");
		break;
	case BBCODE:
		include_once(SFEDITORDIR."bbcode/sf-bbcodeinit.php");
		break;
	}
}

function sf_load_front_js()
{
	# Load up the javascript files
	wp_enqueue_script('jquery');
	if(SF_USE_PRETTY_CBOX)
	{
		wp_enqueue_script('jquery.checkboxes', SFJSCRIPT.'prettyCheckboxes.js', array('jquery'));
	}
	wp_enqueue_script('highslide', SFJSCRIPT.'highslide/highslide.js');
	wp_enqueue_script('sfjs', SFJSCRIPT.'sf.js');

	if(isset($_GET['pmaction']))
	{
		wp_enqueue_script('sfpmjs', SFJSCRIPT.'sfpm.js');
	}

	if(SF_USE_PRETTY_CBOX)
	{
		?>
		<script type='text/javascript'>
		var pcbExclusions = new Array(
		<?php
			$exc = get_option('sfcbexclusions');
			if($exc)
			{
				$exc = rtrim($exc, ",");
				$exclist = explode(",", $exc);
				foreach($exclist as $item)
				{
					echo '"'.trim($item).'",';
				}
			}
			echo '"sfcbdummy"'."\n";
		?>
		);
		</script>
		<?php
	}

	return;
}

# ------------------------------------------------------------------
# sf_localisation()
#
# Setup the forum localisation
# ------------------------------------------------------------------
function sf_localisation()
{
	global $wp_version;

	# i18n support
	if(version_compare($wp_version, '2.6', '<'))
	{
		load_plugin_textdomain('sforum', '/wp-content/plugins/simple-forum/languages');
	} else {
		load_plugin_textdomain('sforum', SF_PLUGIN_DIR.'/languages', 'simple-forum/languages');
	}
	return;
}

# ------------------------------------------------------------------
# sf_feed()
#
# Redirects RSS feed requests
# ------------------------------------------------------------------
function sf_feed()
{
	if(isset($_GET['xfeed']))
	{
		include SF_PLUGIN_DIR.'/sf-feeds.php';
		exit;
	}
}

# ------------------------------------------------------------------
# sfg_404()
# notes....
# ------------------------------------------------------------------
function sfg_404()
{
	if(is_404())
	{
		if(strpos($_SERVER[REQUEST_URI], get_option('sfslug'), 0) && get_sfsetting('404') == -1)
		{
			add_sfsetting('404', '404');
			sfa_update_permalink();
			wp_redirect($_SERVER[REQUEST_URI]);
		}
	}
}

# ------------------------------------------------------------------
# sf_set_rewrite_rules()
#
# Setup the forum rewrite rules
# ------------------------------------------------------------------
function sf_set_rewrite_rules ($rules)
{
	global $wp_rewrite;

	$slug = get_option('sfslug');
	if ($wp_rewrite->using_index_permalinks())
	{
		$slugmatch ='index.php/'.$slug;
	} else {
		$slugmatch = $slug;
	}

	$sf_rules[$slugmatch.'/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]';
	$sf_rules[$slugmatch.'/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_page=$matches[2]';
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]';
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_page=$matches[3]';

	$rules = array_merge($sf_rules, $rules);

	return $rules;
}

# ------------------------------------------------------------------
# sf_set_query_vars()
#
# Setup the forum query variables
# ------------------------------------------------------------------
function sf_set_query_vars($vars)
{
	$vars[] = 'sf_forum';
	$vars[] = 'sf_topic';
	$vars[] = 'sf_page';

	return $vars;
}

# ------------------------------------------------------------------
# sf_populate_query_vars()
#
# Populate the forum  query variables from the URL
# ------------------------------------------------------------------
function sf_populate_query_vars()
{
	global $sfvars, $wp_rewrite;

	# load query vars
	$sfvars = array();

	$sfvars['error'] = false;
	$sfvars['forumid'] = 0;
	$sfvars['topicid'] = 0;

	# We can check to see if the url is a pre V3 url
	# this checks for numeric value. Fine as long as
	# someone doesn't name their forum with simply a number!
	if((isset($_GET['forum'])) && (is_numeric($_GET['forum'])))
	{
		# suggests an old url
		$sfvars['forumslug'] = sf_get_forum_slug(sf_syscheckint($_GET['forum']));
		if(isset($_GET['topic'])) $sfvars['topicslug'] = sf_get_topic_slug(sf_syscheckint($_GET['topic']));
		sf_populate_support_vars();
		return;
	}

	# if V3 link and user has permalinks
	if($wp_rewrite->using_permalinks())
	{
		# post V3 permalinks
		# using permalinks so get the values from the query vars
		$sfvars['forumslug'] = sf_syscheckstr(get_query_var('sf_forum'));
		if(empty($sfvars['forumslug']) && isset($_GET['search']))
		{
			$sfvars['forumslug']=sf_syscheckstr($_GET['forum']);
		}
		$sfvars['topicslug'] = sf_syscheckstr(get_query_var('sf_topic'));
		if (get_query_var('sf_page') != '')
		{
			$sfvars['page'] = sf_syscheckint(get_query_var('sf_page'));
		}
		sf_populate_support_vars();
		return;
	} else {
		# post V3 but using default
		# Not using permalinks so we need to parse the query string from the url and do it ourselves

		$stuff=array();
		$stuff=explode('/', $_SERVER['QUERY_STRING']);

		# deal with non-standard cases first
		if (isset($_GET['search']))
		{
			sf_build_search_vars($stuff);
		} else {
			sf_build_standard_vars($stuff);
		}

		sf_populate_support_vars();

		return;
	}
}

# ------------------------------------------------------------------
# sf_populate_support_vars()
#
# Query Variables support routine
# ------------------------------------------------------------------
function sf_populate_support_vars()
{
	global $sfvars;

	# Populate the rest of sfvars

	if(empty($sfvars['page']))
	{
		$sfvars['page'] = 1;
	}
	if(!empty($sfvars['forumslug']) && $sfvars['forumslug'] != 'all')
	{
		$record = sf_get_forum_record_from_slug($sfvars['forumslug']);
		$sfvars['forumid'] = $record->forum_id;
		$sfvars['forumname'] = stripslashes($record->forum_name);
	}
	if(!empty($sfvars['topicslug']))
	{
		$record = sf_get_topic_record_from_slug($sfvars['topicslug']);
		$sfvars['topicid'] = $record->topic_id;
		$sfvars['topicname'] = stripslashes($record->topic_name);
	}
	return;
}

# ------------------------------------------------------------------
# sf_build_search_vars()
#
# Query Variables support routine
# ------------------------------------------------------------------
function sf_build_search_vars($stuff)
{
	global $sfvars;

	if(isset($_GET['forum']))
	{
		# means searching all
		$sfvars['forumslug'] = sf_syscheckstr($_GET['forum']);
	} else {
		# searching single forum
		if(!empty($stuff[1]))
		{
			$sfvars['forumslug'] = $stuff[1];
		}

		# (2) topic
		if(!empty($stuff[2]))
		{
			$parts = explode("&", $stuff[2]);
			$sfvars['topicslug'] = $parts[0];
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_build_standard_vars()
#
# Query Variables support routine
# ------------------------------------------------------------------
function sf_build_standard_vars($stuff)
{
	global $sfvars;

	# (1) forum first
	if(!empty($stuff[1]))
	{
		$substuff = explode('&', $stuff[1]);
		$sfvars['forumslug'] = $substuff[0];
	}

	# (2) topic or page?
	if(!empty($stuff[2]))
	{
		$matches = array();
		if(preg_match("/page-(\d+)/", $stuff[2], $matches))
		{
			$sfvars['page'] = intval($matches[1]);
		} else {
		$substuff = explode('&', $stuff[2]);
		$sfvars['topicslug'] = $substuff[0];
		}
	}

	# (3) if here must be page
	if(!empty($stuff[3]))
	{
		if(preg_match("/page-(\d+)/", $stuff[3], $matches))
		{
			$sfvars['page'] = intval($matches[1]);
		}
	}

	return;
}

# ------------------------------------------------------------------
# sf_get_sfqurl()
#
# Build a forum query url ready for parameters
# ------------------------------------------------------------------
function sf_get_sfqurl($url)
{
	# if no ? then add one on the end
	$url = rtrim($url, '/');
	if(strpos($url, '?') === false)
	{
		$url .= '?';
	}
	return $url;
}

# ------------------------------------------------------------------
# sf_get_sfurl_plus_amp()
#
# Detect if default permalink which also adds ampersand...
# ------------------------------------------------------------------
function sf_get_sfurl_plus_amp($url)
{
	# if no ? then add one on the end
	$url = rtrim($url, '/');
	if(strpos($url, '?') === false)
	{
		$url .= '?';
	} else {
		$url .= '&amp;';
	}
	return $url;
}

# ------------------------------------------------------------------
# sf_get_system_status()
#
# Determine if forum can be run or if it requires install/upgrade
# ------------------------------------------------------------------
function sf_get_system_status()
{
	$current_version = get_option('sfversion');
	$current_build = get_option('sfbuild');

	# Has the systen been installed?
	if(version_compare($current_version, '1.0', '<'))
	{
		return 'Install';
	}

	# Base already installed - check Version and Build Number
	if(($current_build < SFBUILD) || ($current_version > SFVERSION))
	{
		return 'Upgrade';
	}
	return 'ok';
}

?>