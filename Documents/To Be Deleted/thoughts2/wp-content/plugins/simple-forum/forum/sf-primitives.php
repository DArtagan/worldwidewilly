<?php
/*
Simple:Press Forum
Base functions
$LastChangedDate: 2009-05-30 16:11:57 +0100 (Sat, 30 May 2009) $
$Rev: 1961 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# = MEMBERS TABLE HANDLERS ====================

# ------------------------------------------------------------------
# sf_get_member_row()
#
# returns the members table content for specified user.
# NOTE: This us returned as an array - columns that require ot are
# NOT unserialized.
#	$userid:		User to lookup
# ------------------------------------------------------------------
function sf_get_member_row($userid)
{
	global $wpdb;

	$member = $wpdb->get_row("SELECT * FROM ".SFMEMBERS." WHERE user_id = $userid", ARRAY_A);
	return $member;
}

# ------------------------------------------------------------------
# sf_get_member_list()
#
# returns specified columns from members table for specified user.
# NOTE: This us returned as an array - columns that require it are
# returned unserialized.
#	$userid:		User to lookup
#	$itemlist:		comma-space delimited list of columns
# ------------------------------------------------------------------
function sf_get_member_list($userid, $itemlist)
{
	global $wpdb;

	$member = $wpdb->get_row("SELECT $itemlist FROM ".SFMEMBERS." WHERE user_id = $userid", ARRAY_A);
	if($member['buddies']) $member['buddies'] = unserialize($member['buddies']);
	if($member['newposts']) $member['newposts'] = unserialize($member['newposts']);
	if($member['admin_options']) $member['admin_options'] = unserialize($member['admin_options']);

	return $member;
}

# ------------------------------------------------------------------
# sf_get_member_item()
#
# returns a specified column from members table for specified user.
# NOTE: This us returned as an var - columns that require it are
# returned unserialized.
#	$userid:		User to lookup
#	$item:			column name
# ------------------------------------------------------------------
function sf_get_member_item($userid, $item)
{
	global $wpdb;

	$thisitem = $wpdb->get_var("SELECT $item FROM ".SFMEMBERS." WHERE user_id = $userid");
	if($item == 'buddies') $thisitem = unserialize($thisitem);
	if($item == 'newposts') $thisitem = unserialize($thisitem);
	if($item == 'admin_options') $thisitem = unserialize($thisitem);

	return $thisitem;
}

# ------------------------------------------------------------------
# sf_update_member_item()
#
# updates a specified column from members table for specified user.
# NOTE: Data requiring serialization must be passed as an array
# 'checktime' and 'lastvisit' are set to now() by the update code
#	$userid:		User to lookup
#	$itemname:		column name
#	$itemdata:		singe var or array
# ------------------------------------------------------------------
function sf_update_member_item($userid, $itemname, $itemdata)
{
	global $wpdb;

	if($itemname == 'buddies') $itemdata="'".serialize($itemdata)."'";
	if($itemname == 'newposts') $itemdata="'".serialize($itemdata)."'";
	if($itemname == 'admin_options') $itemdata="'".serialize($itemdata)."'";

	if($itemname == 'lastvisit')
	{
		$itemdata= "'".sf_get_member_item($userid, 'checktime')."'";
	}

	if($itemname == 'checktime') $itemdata= 'now()';

	if(strpos("display_name avatar signature sigimage subscribe watches posts_rated", $itemname) !== false)
	{
		$itemdata = "'".$itemdata."'";
	}

	$thisitem = $wpdb->query("UPDATE ".SFMEMBERS." SET $itemname = $itemdata WHERE user_id=$userid");
	return $thisitem;
}

# ------------------------------------------------------------------
# sf_set_members_timezone()
#
# Sets date time to zone option for last visit and checktime
# member settings
# ------------------------------------------------------------------
function sf_set_members_timezone()
{
	$zone = get_option('sfzone');
	if($zone == 0) return 'NOW()';
	if($zone < 0)
	{
		return 'DATE_SUB(NOW(), INTERVAL '.abs($zone).' HOUR)';
	} else {
		return 'DATE_ADD(NOW(), INTERVAL '.abs($zone).' HOUR)';
	}
}

# ------------------------------------------------------------------
# sf_update_member_moderator_flag()
#
# checks an updates moderator flag for specified user
#	$userid:		User to lookup
# ------------------------------------------------------------------
function sf_update_member_moderator_flag($userid)
{
	global $wpdb;

	$ugs = sf_get_user_memberships($userid);
	if ($ugs)
	{
		foreach($ugs as $ug)
		{
			$mod = $wpdb->get_var("SELECT usergroup_is_moderator FROM ".SFUSERGROUPS." WHERE usergroup_id = ".$ug['usergroup_id']);
			if ($mod)
			{
				sf_update_member_item($userid, 'moderator', 1);
				return;
			}
		}
	}

	sf_update_member_item($userid, 'moderator', 0);
	return;
}


# = NOTICE TABLE HANDLERS =====================
function get_sfnotice($item)
{
	global $wpdb;

	$id=$_SERVER['REMOTE_ADDR'];
	$message = $wpdb->get_var("SELECT message FROM ".SFNOTICE." WHERE id='$id' AND item='$item'");
	return stripslashes($message);
}

function update_sfnotice($item, $message)
{
	global $wpdb;

	$message = $wpdb->escape($message);
	$id=$_SERVER['REMOTE_ADDR'];

	# as usual we need to check if already there because it was orphaned...
	$check=get_sfnotice($item);
	if($check)
	{
		delete_sfnotice($item);
	}
	$wpdb->query("INSERT INTO ".SFNOTICE." (id, item, message, ndate) VALUES ('$id', '$item', '$message', now())");
	$wpdb->flush();

	return;
}

function delete_sfnotice($item)
{
	global $wpdb;

	$id=$_SERVER['REMOTE_ADDR'];
	$wpdb->query("DELETE FROM ".SFNOTICE." WHERE id='$id' AND item='$item'");
	$wpdb->flush();
	return;
}

function  sf_clean_sfnotice()
{
	global $wpdb;
	$wpdb->query("DELETE FROM ".SFNOTICE." WHERE ndate < DATE_SUB(CURDATE(), INTERVAL 24 HOUR);");
	return;
}

# = SETTINGS TABLE HANDLERS ===================
function get_sfsetting($setting)
{
	global $wpdb;

	$value = $wpdb->get_var("SELECT setting_value FROM ".SFSETTINGS." WHERE setting_name = '$setting'");
	if(empty($value))
	{
		return -1;
	} else {
		return $value;
	}
}

function add_sfsetting($setting_name, $setting_value = '')
{
	global $wpdb;

	$check = get_sfsetting($setting_name);
	if($check == -1)
	{
		$setting_name = $wpdb->escape($setting_name);
		$setting_value = $wpdb->escape($setting_value);
		$wpdb->query("INSERT INTO ".SFSETTINGS." (setting_name, setting_value, setting_date) VALUES ('$setting_name', '$setting_value', now())");
		$wpdb->flush();
	} else {
		update_sfsetting($setting_name, $setting_value);
	}
	return;
}

function update_sfsetting($setting_name, $setting_value)
{
	global $wpdb;

	if (is_string($setting_value)) $setting_value = trim($setting_value);

	# If the new and old values are the same, no need to update.
	$oldvalue = get_sfsetting($setting_name);
	if ($setting_value == $oldvalue)
	{
		return false;
	}

	if (($oldvalue == -1) || (empty($oldvalue)))
	{
		add_sfsetting($setting_name, $setting_value);
		return true;
	}

	$setting_value = $wpdb->escape($setting_value);
	$setting_name = $wpdb->escape($setting_name);
	$wpdb->query("UPDATE ".SFSETTINGS." SET setting_value = '$setting_value', setting_date = now() WHERE setting_name = '$setting_name'");
	if($wpdb->rows_affected == 1)
	{
		return true;
	}
	return false;
}

function delete_sfsetting($setting_name)
{
	global $wpdb;
	# Get the ID, if no ID then return
	$setting_id = $wpdb->get_var("SELECT setting_id FROM ".SFSETTINGS." WHERE setting_name = '$setting_name'");
	if (!$setting_id) return false;
	$wpdb->query("DELETE FROM ".SFSETTINGS." WHERE setting_name = '$setting_name'");
	return true;
}

function  sf_clean_settings()
{
	global $wpdb;
	$wpdb->query("DELETE FROM ".SFSETTINGS." WHERE setting_date < DATE_SUB(CURDATE(), INTERVAL 24 HOUR) AND setting_name <> 'membercount' AND setting_name <> 'maxonline';");
	$wpdb->query("DELETE FROM ".SFSETTINGS." WHERE setting_name='membercount' AND setting_value < 1;");
	$wpdb->query("DELETE FROM ".SFSETTINGS." WHERE setting_name='404'");
	return;
}

# = META TABLE HANDLERS ====================

# ------------------------------------------------------------------
# sf_add_sfmeta()
#
# Adds a new record to the sfmeta table
#	$type:		The type of the meta record
#	$key:		The unique key name
#	$value:		value - array expected and will serialize
# ------------------------------------------------------------------
function sf_add_sfmeta($type, $key, $value)
{
	global $wpdb;

	if(empty($type) || empty($key) || empty($value)) return false;

	# Check if already exists
	$sql = 	"SELECT meta_id FROM ".SFMETA.
			" WHERE meta_type='".$type."' AND meta_key='".$key."'";

	$check = $wpdb->get_var($sql);

	# so - does it?
	if($check)
	{
		# yes - so needs to be an update call
		sf_update_sfmeta($type, $key, $value, $check);
	} else {
		$sql =  "INSERT INTO ".SFMETA.
				"(meta_type, meta_key, meta_value)
				VALUES
				('".$type."', '".$key."', '".$value."')";
		$wpdb->query($sql);
	}
	return;
}

# ------------------------------------------------------------------
# sf_update_sfmeta()
#
# Updates a record in the sfmeta table
#	$type:		The type of the meta record
#	$key:		The unique key name
#	$value:		value - array expected and will serialize
#	$id:		The meta records ID
# ------------------------------------------------------------------
function sf_update_sfmeta($type, $key, $value, $id)
{
	global $wpdb;

	$sql =	"UPDATE ".SFMETA." SET
			 meta_type='".$type."',
			 meta_key='".$key."',
			 meta_value='".$value."'
			 WHERE meta_id=".$id;

	if($wpdb->query($sql))
	{
		return true;
	} else {
		return false;
	}
}

# ------------------------------------------------------------------
# sf_get_sfmeta()
#
# Gets a record(s) from the sfmeta table
#	$type:		The type of the meta record
#	$key:		The unique key name - can be false to get all of type
#	$id:		If set then returns by id (one row regardless of $key)
# ------------------------------------------------------------------
function sf_get_sfmeta($type, $key=false, $id=0)
{
	global $wpdb;

	$WHERE = " meta_type='".$type."'";

	if($id != 0)
	{
		$WHERE .= " AND meta_id=".$id;
	} else {
		if($key)
		{
			$WHERE .= " AND meta_key='".$key."'";
		}
	}

	$sql =  "SELECT * FROM ".SFMETA.
			" WHERE ".$WHERE.
			" ORDER BY meta_id";

	$records = $wpdb->get_results($sql, ARRAY_A);
	return $records;
}

# ------------------------------------------------------------------
# sf_delete_sfmeta()
#
# Deletes a record in the sfmeta table
#	$id:		The meta records ID
# ------------------------------------------------------------------
function sf_delete_sfmeta($id)
{
	global $wpdb;

	$sql = 	"DELETE FROM ".SFMETA.
			" WHERE meta_id=".$id;

	$wpdb->query($sql);
	return;
}

# = SEARCH STRING HANDLERS ====================
function sf_construct_search_parameter($term, $type)
{
	$newterm = str_replace(' ', '%', $term);
	$newterm = str_replace("'", "", $newterm);
	$newterm = str_replace('"', '', $newterm);
	$newterm .= '%'.$type;
	return $newterm;
}

function sf_deconstruct_search_parameter($term)
{
	global $sfvars;

	if(substr($term, 0, 10) == 'statusflag')
	{
		$temp=array();
		$temp=explode('%', $term);
		$flag=$temp[1];
		$newterm = "Topic Status: ".sf_get_topic_status_from_forum($sfvars['forumid'], $flag);
	} elseif (substr($term, 0, 11) == "sf%members%")
	{
		$newterm = sf_deconstruct_search_for_display($term);
	} else {
		$newterm = str_replace('%', ' ', $term);
		$newterm = substr($newterm, 0, strlen($newterm)-2);
	}
	return $newterm;
}

function sf_deconstruct_search_for_display($term)
{
	global $wpdb;

	if (substr($term, 0, 11) == "sf%members%")
	{
		$items=explode('%', $term);
		$id = substr($items[3], 4, 25);
		$name = sf_get_member_item($id, 'display_name');

		if($items[2] == 1)
		{
			$newterm = sprintf(__("Topics in which %s has posted", "sforum"), $name);
		} else {
			$newterm = sprintf(__("Topics started by %s", "sforum"), $name);
		}
	} else {
		$newterm = sf_deconstruct_search_parameter($term);
	}
	return $newterm;
}

function sf_construct_search_term($term)
{
	# get the search type from end of string
	$type = substr($term, -1, 1);

	# get the search terms(s)
	$term = sf_deconstruct_search_parameter($term);

	switch($type)
	{
		case 1:
			$searchterm = $term;
			break;

		case 2:
			$term = str_replace(' ', ' +', $term);
			$searchterm.= '+'.$term;
			break;

		case 3:
			$searchterm = '"'.$term.'"';
			break;
	}
	return $searchterm;
}

function sf_deconstruct_search_term($term)
{
	$searchterms = array();

	# get the search type from end of string
	$type = substr($term, -1, 1);

	# get the search terms(s)
	$term = sf_deconstruct_search_parameter($term);

	if($type == 3)
	{
		# return term as is in 0 array
		$searchterms[0] = $term;
	} else {
		# for type 1 or 2 send back word or array of words
		$searchterms = explode(' ', $term);
	}
	return $searchterms;
}

# = SAFARI BROWSER CHECK ======================
function sf_is_safari()
{
	$pos = strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit');
	if($pos === false)
	{
		return false;
	} else {
		$kit = substr($_SERVER['HTTP_USER_AGENT'], $pos+12, 3);
		if($kit >= 522)
		{
			return false;
		} else {
			return true;
		}
	}
}

# = RSS DATA FILTER ===========================
function sf_rss_filter($text)
{
  echo convert_chars(ent2ncr($text));
}

function sf_rss_excerpt($text)
{
	$max=get_option('sfrsswords');
	if($max == 0) return $text;
	$bits=explode(" ", $text);
	$text='';
	$end='';
	if(count($bits) < $max)
	{
		$max=count($bits);
	} else {
		$end='...';
	}
	$text="";
	for($x=0; $x<$max; $x++)
	{
		$text.=$bits[$x].' ';
	}
	return $text.$end;
}

# = GENERAL TOP MESSAGE DISPLAY ===============
function sf_message($message)
{
	$comp = explode('@', $message);
	if(count($comp) > 1)
	{
		$mtype = $comp[0];
		if($mtype == 1)
		{
			$icon = '<img class="sficon" src="'. SFRESOURCES .'failure.png" alt="" />';
			$class= "sfmessagefail";
			$message = $comp[1];
		} else if ($mtype == 0)
		{
			$icon = '<img class="sficon" src="'. SFRESOURCES .'success.png" alt="" />';
			$class= "sfmessage";
			$message = $comp[1];
		}
	} else {
			$icon = '';
			$class= "sfmessage";
	}

	$out = '<div id="sfcomm" class="'.$class.'">' . $icon . $message . '</div>'."\n";

	$out.= '<script type="text/javascript">'."\n";
	$out.= 'sfjmDisplay();';
	$out.= '</script>'."\n"."\n";

	return $out;
}

# = JAVASCRIPT CHECK ==========================
function sf_js_check()
{
	return '<noscript><div class="sfmessage">'.__("This forum requires Javascript to be enabled for posting content", "sforum").'</div></noscript>'."\n";
}

# = COOKIE HANDLING ===========================
function sf_write_guest_cookie($guestname, $guestemail)
{
	$cookiepath = '/';
	setcookie('guestname_' . COOKIEHASH, $guestname, time() + 30000000, $cookiepath, false);
	setcookie('guestemail_' . COOKIEHASH, $guestemail, time() + 30000000, $cookiepath, false);
	setcookie('sflast_' . COOKIEHASH, time(), time() + 30000000, $cookiepath, false);

	return;
}

# = SPAM MATH HANDLING ========================
function sf_math_spam_build()
{
	$spammath[0] = rand(1, 12);
	$spammath[1] = rand(1, 12);

	# Calculate result
	$result = $spammath[0] + $spammath[1];

	# Add name of the weblog:
	$result .= get_bloginfo('name');
	# Add date:
	$result .= date('j') . date('ny');
	# Get MD5 and reverse it
	$enc = strrev(md5($result));
	# Get only a few chars out of the string
	$enc = substr($enc, 26, 1) . substr($enc, 10, 1) . substr($enc, 23, 1) . substr($enc, 3, 1) . substr($enc, 19, 1);

	$spammath[2] = $enc;

	return $spammath;
}

function sf_spamcheck()
{
	$spamcheck = array();
	$spamcheck[0]=false;

	# Check dummy input field
	if(array_key_exists ('url', $_POST))
	{
		if(!empty($_POST['url']))
		{
			$spamcheck[0]=true;
			$spamcheck[1]= __('1@Form not filled by human hands!', "sforum");
			return $spamcheck;
		}
	}

	# Check math question
	$correct = $_POST['sfvalue2'];
	$test = $_POST['sfvalue1'];
	$test = preg_replace('/[^0-9]/','',$test);

	if($test == '')
	{
		$spamcheck[0]=true;
		$spamcheck[1]= __('1@No answer was given to the math question', "sforum");
		return $spamcheck;
	}

	# Add name of the weblog:
	$test .= get_bloginfo('name');
	# Add date:
	$test .= date('j') . date('ny');
	# Get MD5 and reverse it
	$enc = strrev(md5($test));
	# Get only a few chars out of the string
	$enc = substr($enc, 26, 1) . substr($enc, 10, 1) . substr($enc, 23, 1) . substr($enc, 3, 1) . substr($enc, 19, 1);

	if($enc != $correct)
	{
		$spamcheck[0]=true;
		$spamcheck[1]= __('1@The answer to the math question was incorrect', "sforum");
		return $spamcheck;
	}
	return $spamcheck;
}

# = CENTRAL EMAIL ROUTINE =====================
function sf_send_email($mailto, $mailsubject, $mailtext, $replyto='', $headers='')
{
	$email_sent = array();
	if ($replyto <> '')
	{
		$header = "MIME-Version: 1.0\n".
		"From: wordpress@" . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])) . "\n" .
		"Reply-To: {$replyto}\n" .
		"Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";
		$email = wp_mail($mailto, $mailsubject, $mailtext, $header);
	} else {
		$email = wp_mail($mailto, $mailsubject, $mailtext, $headers);
	}

	if ($email == false)
	{
		$email_sent[0] = false;
		$email_sent[1] = __('Email Notification Failed', "sforum");
	} else {
		$email_sent[0] = true;
		$email_sent[1] = __('Email Notification Sent', "sforum");
	}

	return $email_sent;
}

function sf_syscheckint($checkval)
{
	$actual = '';
	if (isset($checkval))
	{
		if (is_numeric($checkval))
		{
			$actual = $checkval;
		}
		$checklen = strlen(strval($actual));
		if ($checklen != strlen($checkval)) die(__("A Suspect Request has been Rejected", "sforum"));
	}
	return $actual;
}

function sf_syscheckstr($string)
{
	if (get_magic_quotes_gpc())  # prevents duplicate backslashes
  	{
    	$string = stripslashes($string);
  	}

	  if (phpversion() >= '4.3.0')
  	{
    	$string = mysql_real_escape_string($string);
  	} else {
    	$string = mysql_escape_string($string);
  	}

  	return $string;
}

function sf_create_nonce($action)
{
	return '<input type="hidden" name="'.$action.'" value="'.wp_create_nonce($action).'" />'."\n";
}

function sf_split_button_label($text, $pos=10)
{
	$label=array();
	$label=explode(' ', $text);
	$label[$pos].='&#x0A;';
	$text=implode(' ', $label);
	return str_replace('&#x0A; ', '&#x0A;', $text);
}

function sf_split_label($text, $pos=10)
{
	$label=array();
	$label=explode(' ', $text);
	$label[$pos].='<br />';
	$text=implode(' ', $label);
	return $text;
}

function sf_current_user_can($cap)
{
	global $current_user, $wpdb;

	# if this is a wpmu site admin, make sure he has capabilities on spf
	$WPMUSITEADMIN = false;
	if (function_exists('is_site_admin') && (is_site_admin())) $WPMUSITEADMIN = true;

	# if there are no SPF admins defined, revert to allowing all WP admins so forum admin isn't locked out
	$WPADMIN = false;
	if (sf_get_admins() == '' && get_usermeta($current_user->ID, $wpdb->prefix.'user_level') == 10) $WPADMIN = true;

	if (current_user_can($cap) || $WPMUSITEADMIN || $WPADMIN)
		return true;
	else
		return false;
}


function sf_gis_error ($errno, $errstr, $errfile, $errline, $errcontext)
{
	global $gis_error;

	if($errno == E_WARNING || $errno == E_NOTICE)
	{
		$gis_error = __('Unable to validate image details', 'sforum');
	}

}

?>