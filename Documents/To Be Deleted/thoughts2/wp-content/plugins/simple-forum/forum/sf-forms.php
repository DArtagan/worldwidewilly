<?php
/*
Simple:Press Forum
Form Rendering
$LastChangedDate: 2009-06-22 19:41:59 +0100 (Mon, 22 Jun 2009) $
$Rev: 2097 $
*/

function sf_add_topic($forumid, $forumname, $statusset, $userid)
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-topic.php');
	return sf_render_add_topic_form($forumid, $forumname, $statusset, $userid);
}

function sf_edit_topic_title($topicid, $topicname, $forumid)
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-topic.php');
	return sf_render_edit_topic_title_form($topicid, $topicname, $forumid);
}

function sf_add_post($forumid, $topicid, $topicname, $statusset, $statusflag, $userid, $subs)
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-post.php');
	return sf_render_add_post_form($forumid, $topicid, $topicname, $statusset, $statusflag, $userid, $subs);
}

function sf_edit_post($postid, $postcontent, $forumid, $topicid, $page, $postedit)
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-post.php');
	return sf_render_edit_post_form($postid, $postcontent, $forumid, $topicid, $page, $postedit);
}

function sf_searchbox($pageview, $statusset=0)
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-search.php');
	return sf_render_searchbox_form($pageview, $statusset);
}

function sf_subscription_form()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-profile.php');
	return sf_render_subscription_form();
}

function sf_add_pm()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-pm.php');
	return sf_render_add_pm_form();
}

function sf_report_post_form()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-report.php');
	return sf_render_report_post_form();
}

function sf_inline_login_form()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-login.php');
	return sf_render_inline_login_form();
}

function sf_policy_form()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-policy.php');
	return sf_render_policy_form();
}

function sf_forum_unavailable()
{
	$out = '';

	$out.= '<div id="sforum"><div class="sfmessagestrip">'."\n";
	$out.= '<img class="sfalignleft" src="'.SFRESOURCES.'information.png" alt="" />'."\n";
	$out.= '<p>&nbsp;&nbsp;'.__("The forum is temporarily unavailable while being upgraded to a new version", "sforum").'</p>';
	$out.= '</div></div>';
	return $out;
}

function sf_validation_messages($type)
{
	global $current_user, $sfglobals;

	# 0 = base error message
	# 1 = guest name
	# 2 = guest email
	# 3 = topic title
	# 4 = spam math
	# 5 = post
	# 6 = embedded formatting

	$msg = array();
	$msg[0] = addslashes(__("Problem! Please correct and re-save", "sforum"));

	if($current_user->guest)
	{
		$msg[1] = addslashes(__("No Guest Name Entered", "sforum"));
		$msg[2] = addslashes(__("No Guest EMail Entered", "sforum"));
	} else {
		$msg[1] = '';
		$msg[2] = '';
	}

	if($type == 'topic')
	{
		$msg[3] = addslashes(__("No Topic Name Entered", "sforum"));
	} else {
		$msg[3] = '';
	}

	if(!$current_user->sfspam)
	{
		$msg[4] = addslashes(__("Spam Math Unanswered", "sforum"));
	} else {
		$msg[4] = '';
	}

	$msg[5] = addslashes(__("No Post Content Entered", "sforum"));

	if($sfglobals['editor']['sfrejectformat'] && $sfglobals['editor']['sfeditor'] == RICHTEXT)
	{
		if(in_array('pastetext', $sfglobals['toolbar']['tbar_buttons_add']) || in_array('pasteword', $sfglobals['toolbar']['tbar_buttons_add']))
		{
			$msg[6] = addslashes(__("This text contains embedded formatting and was probably pasted in. Please completely remove text and use the approriate paste toolbar button (text or MS Word) to paste into or rewrite as plain text", "sforum"));
		}
	}

	return $msg;
}

function sf_setup_editor($tab, $content='')
{
	global $sfglobals;

	$out = '';
	if($sfglobals['editor']['sfeditor'] == RICHTEXT || $sfglobals['editor']['sfeditor'] == PLAIN)
	{
		# rich text/tinymce - or - plain textarea
		$out.='<textarea  tabindex="'.$tab.'" class="sftextarea" name="postitem" id="postitem" cols="60" rows="12">'.$content.'</textarea>'."\n";
		return $out;
	}
	if($sfglobals['editor']['sfeditor'] == HTML)
	{
		# html quicktags
		$image = "html/htmlEditor.gif";
		$alttext = __("HTML Editor", "sforum");
	} else {
		# bbcode quicktags
		$image = "bbcode/bbcodeEditor.gif";
		$alttext = __("bbCode Editor", "sforum");
	}
	$out.='<div class="quicktags">'."\n";
	$out.='<img class="sfalignright" src="'.SFEDSTYLE.$image.'" alt="'.$alttext.'" />';
	$out.='<script type="text/javascript">edToolbar();</script><textarea tabindex="'.$tab.'" class="sftextarea" name="postitem" id="postitem" rows="12" cols="60">'.$content.'</textarea><script type="text/javascript">var edCanvas = document.getElementById("postitem");</script>'."\n";
	$out.='</div>'."\n";
	return $out;
}

function sf_render_smileys()
{
	global $sfglobals;

	$out='';

	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		# load smiles from sfmeta
		if($sfglobals['smileys'])
		{
			foreach ($sfglobals['smileys'] as $sname => $sinfo)
			{
				$out.= '<img class="sfsmiley" src="'.SFSMILEYS.$sinfo[0].'" title="'.$sname.'" alt="'.$sname.'" ';
				$out.= 'onclick="sfjLoadSmiley(\''.$sinfo[0].'\', \''.$sname.'\', \''.SFSMILEYS.'\', \''.$sinfo[1].'\', \''.$sfglobals['editor']['sfeditor'].'\');" />'."\n";
			}
		}
	}
	return $out;
}

?>