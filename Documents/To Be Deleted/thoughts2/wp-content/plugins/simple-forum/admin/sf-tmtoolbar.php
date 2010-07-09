<?php
/*
Simple:Press Forum
Tinymce toolbar options support rotuines
$LastChangedDate: 2009-01-14 02:05:53 +0000 (Wed, 14 Jan 2009) $
$Rev: 1199 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	echo (__('Access Denied', "sforum"));
	die();
}

# Display the toolbar as one line
function sfa_render_remove_toolbar()
{
	$ipath = SF_PLUGIN_URL.'/admin/images/toolbar/';
	$delprompt = __("Remove Selected Toolbar Button?", "sforum");
	echo '<label for="sftbarall">'.__("Click on the buttons you wish to remove. When finished, click on the Update Toolbar button to save", "sforum").'</label><br /><br />'."\n";
	?>
	<ul id="sftbarall">
	<?php
	$tbmeta = sf_get_sfmeta('tinymce_toolbar', 'user');
	$tbdata = unserialize($tbmeta[0]['meta_value']);
	$toolbar = array_merge($tbdata['tbar_buttons'], $tbdata['tbar_buttons_add']);
	$thisb = 0;
	foreach($toolbar as $button)
	{
		if($button == "|")
		{
			$img = "separator.gif";
			$bname = 'separator';
		} else {
			$img = $button.'.gif';
			$bname = $button;
		}

		if($thisb >= count($tbdata['tbar_buttons']))
		{
			$buttonid = "plugin_" . ($thisb - count($tbdata['tbar_buttons']));
		} else {
			$buttonid = "button_" . $thisb;
		}
		?>
		<li id="del_btn_<?php echo($thisb); ?>">
		<a id="<?php echo($buttonid); ?>" href="javascript:void(0);" onclick="sfjDelTbButton(this, '<?php echo($delprompt); ?>');"><img src="<?php echo($ipath.$img); ?>" alt="" title="<?php echo($bname); ?>" /></a>
		</li>
		<?php
		$thisb++;
	}
	?>
	</ul>

	<?php 
	echo '<table class="sfabuttontable">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="tbdelbuttons" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:document.sfcomponents.submit();">';
	echo sfa_split_heading(__("Update Toolbar", "sforum"), 0);
	echo '</a>';
	echo '</td>';
	echo '<td class="sfabuttonitem sfabgcancel" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="tbrestore" id="tbrestore" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'tbrestore\')">';
	echo sfa_split_heading(__("Restore Defaults", "sforum"), 0);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	?>
	
	<input type="text" class="inline_edit" size="70" id="delbuttons" name="delbuttons" />
	
	<?php
	return;
}

# Display the draggable/sortable toolbars (standard and plugins additions
function sfa_render_drag_toolbar()
{
	$ipath = SF_PLUGIN_URL.'/admin/images/toolbar/';
	echo '<label for="sftbarstan">'.__("To re-order, select buttons with the mouse cursor and drag to new position. Finally click on the Update Toolbar to save", "sforum").'<br /><br />'.__("Standard Buttons", "sforum").'</label>';
	?>

	<ul id="sftbarstan">
	<?php
	$tbmeta = sf_get_sfmeta('tinymce_toolbar', 'user');
	$tbdata = unserialize($tbmeta[0]['meta_value']);
	$toolbar = $tbdata['tbar_buttons'];
	$thisb = 0;
	foreach($toolbar as $button)
	{
		if($button == "|")
		{
			$img = "separator.gif";
			$bname = 'separator';
		} else {
			$img = $button.'.gif';
			$bname = $button;
		}
	
		?>
		<li id="sItem_<?php echo($thisb); ?>"><img src="<?php echo($ipath.$img); ?>" class="handle" alt="<?php _e("move", "sforum"); ?>" title="<?php echo($bname); ?>" /></li>
		<?php
		$thisb++;
	}
	?>
	</ul>
	<?php
	echo '<label for="sftbarplug">'.__("Plugin Buttons", "sforum").'</label>';
	?>
	<ul id="sftbarplug">
	<?php
	$tbmeta = sf_get_sfmeta('tinymce_toolbar', 'user');
	$tbdata = unserialize($tbmeta[0]['meta_value']);
	$toolbar = $tbdata['tbar_buttons_add'];
	$thisb = 0;
	foreach($toolbar as $button)
	{
		if($button == "|")
		{
			$img = "separator.gif";
			$bname = 'separator';
		} else {
			$img = $button.'.gif';
			$bname = $button;
		}
	
		?>
		<li id="pItem_<?php echo($thisb); ?>"><img src="<?php echo($ipath.$img); ?>" class="handle" alt="<?php _e("move", "sforum"); ?>" title="<?php echo($bname); ?>" /></li>
		<?php
		$thisb++;
	}
	?>
	</ul>

	<?php 
	echo '<table class="sfabuttontable">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="tborderbuttons" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:document.sfcomponents.submit();">';
	echo sfa_split_heading(__("Update Toolbar", "sforum"), 0);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	?>

	<input type="text" class="inline_edit" size="70" id="stan_buttons" name="stan_buttons" />
	<input type="text" class="inline_edit" size="70" id="plug_buttons" name="plug_buttons" />
	<?php
	return;
}


# Save toolbar changes - removal of buttons
function sfa_remove_toolbar_buttons($tblist)
{
	# Load up current from sfmeta
	$tbmeta = sf_get_sfmeta('tinymce_toolbar', 'user');
	$current = unserialize($tbmeta[0]['meta_value']);
		
	$tblist = explode("&", $tblist);
	$buttons = array();
	$plugins = array();
	foreach($tblist as $item)
	{
		$thisone = explode("_", $item);
		if($thisone[0]=="button")
		{
			$buttons[] = $thisone[1];
		} else {
			$plugins[] = $thisone[1];
		}
	}

	if($buttons) sort($buttons, SORT_NUMERIC);
	$index = 0;
	$newarray = array();
	foreach($current['tbar_buttons'] as $btn)
	{
		if(!in_array($index, $buttons))
		{
			$newarray[] = $btn;
		}
		$index++;
	}
	$current['tbar_buttons'] = $newarray;

	if($plugins) sort($plugins, SORT_NUMERIC);
	$index = 0;
	$newarray = array();
	foreach($current['tbar_buttons_add'] as $btn)
	{
		if(!in_array($index, $plugins))
		{
			$newarray[] = $btn;
		}
		$index++;
	}
	$current['tbar_buttons_add'] = $newarray;

	sf_update_sfmeta('tinymce_toolbar', 'user', serialize($current), $tbmeta[0]['meta_id']);

	$mess= __('Toolbar Updated', "sforum").$mess;
	sfa_message($mess);

	return;
}

# Save toolbar changes - re-ordering of buttons
function sfa_reorder_toolbar_buttons($stanlist, $pluglist)
{
	# Load up current from sfmeta
	$tbmeta = sf_get_sfmeta('tinymce_toolbar', 'user');
	$current = unserialize($tbmeta[0]['meta_value']);

	if($stanlist)
	{
		$stanlist = explode("&", $stanlist);
		$newarray = array();
		foreach($stanlist as $btn)
		{
			$thisone = explode("=", $btn);
			$newarray[] = $current['tbar_buttons'][$thisone[1]];
		}
		$current['tbar_buttons'] = $newarray;
	}
	
	if($pluglist)
	{
		$pluglist = explode("&", $pluglist);
		$newarray = array();
		foreach($pluglist as $btn)
		{
			$thisone = explode("=", $btn);
			$newarray[] = $current['tbar_buttons_add'][$thisone[1]];
		}
		$current['tbar_buttons_add'] = $newarray;
	}
	
	sf_update_sfmeta('tinymce_toolbar', 'user', serialize($current), $tbmeta[0]['meta_id']);

	$mess= __('Toolbar Updated', "sforum").$mess;
	sfa_message($mess);

	return;
}

# restore the full TM toolbar as supplied
function sfa_restore_toolbar_defaults()
{
	# Load up current from sfmeta (User)
	$tbmetauser = sf_get_sfmeta('tinymce_toolbar', 'user');
	$tbmetadefault = sf_get_sfmeta('tinymce_toolbar', 'default');
	
	sf_update_sfmeta('tinymce_toolbar', 'user', $tbmetadefault[0]['meta_value'], $tbmetauser[0]['meta_id']);

	$mess= __('Toolbar Restored', "sforum").$mess;
	sfa_message($mess);

	return;
}

?>