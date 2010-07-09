<?php
/*
Simple:Press Forum
Admin Panels - Options/Components Tab Rendering Support
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# == PAINT ROUTINES

function sfa_paint_options_init()
{
	global $tab;

	$tab=1;
	return;
}

function sfa_paint_open_tab($tabname)
{
	echo "<div id='".$tabname."' class='ui-tabs-panel'>";
	echo "<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "<tr valign='top'>\n";
	echo "<td width='50%'>\n";
	return;
}

function sfa_paint_close_tab()
{
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</div>\n";
	return;
}

function sfa_paint_tab_right_cell()
{
	echo "</td>\n";
	echo "<td width='50%'>\n";
	return;
}

function sfa_paint_open_panel()
{
	echo "<table width='100%'>\n";
	return;
}

function sfa_paint_close_panel()
{
	echo "</table>\n";
	return;
}

function sfa_paint_open_fieldset($legend, $displayhelp=false, $helpname='', $opentable=true)
{
	global $adminhelpfile;

	echo "<tr>\n";
	echo "<td>\n";
	echo "<fieldset class='sffieldset'>\n";
	echo "<legend><strong>$legend</strong></legend>\n";
	if($displayhelp) echo sfa_paint_help($helpname, $adminhelpfile);
	if($opentable)
	{
		echo "<table class='form-table' width='100%'>\n";
	}
	return;
}

function sfa_paint_close_fieldset($closetable=true)
{
	if($closetable)
	{
		echo "</table>\n";
	}
	echo "</fieldset>\n";
	echo "</td>\n";
	echo "</tr>\n";
	return;
}

function sfa_paint_input($label, $name, $value, $disabled=false, $large=false)
{
	global $tab;

	echo "<tr valign='top'>\n";
	if($large)
	{
		echo "<td class='sflabel' width='40%'>\n";
	} else {
		echo "<td class='sflabel' width='60%'>\n";
	}
	echo $label.":</td>\n";
	echo "<td>\n";
	echo '<input type="text" class="sfpostcontrol" tabindex="'.$tab.'" name="'.$name.'" value="'.$value.'" ';
	if($disabled == true)
	{
		echo "disabled='disabled' ";
	}
	echo "/></td>\n";

	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_file($label, $name, $disabled=false, $large=false)
{
	global $tab;

	echo "<tr valign='top'>\n";
	if($large)
	{
		echo "<td class='sflabel' width='40%'>\n";
	} else {
		echo "<td class='sflabel' width='60%'>\n";
	}
	echo $label.":</td>\n";
	echo "<td>\n";
	echo '<input type="file" class="sfpostcontrol" tabindex="'.$tab.'" name="'.$name.'" id="'.$name.'" ';
	if($disabled == true)
	{
		echo "disabled='disabled' ";
	}
	echo "/></td>\n";

	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_hidden_input($name, $value)
{
	echo "<tr style='display:none'><td>\n";
	echo "<input type='hidden' name='$name' value='$value' />";
	echo "</td></tr>\n";
	return;
}

function sfa_paint_textarea($label, $name, $value, $submessage='')
{
	global $tab;

	echo "<tr valign='top'>\n";
	echo "<td class='sflabel' width='60%'>\n$label";
	if(!empty($submessage))
	{
		echo "<br /><small><strong>$submessage</strong></small>\n";
	}
	echo "</td>\n";
	echo "<td>\n";
	echo "<textarea rows='6' cols='80' class='sftextarea' tabindex='$tab' name='$name'>$value</textarea>\n";
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_wide_textarea($label, $name, $value, $submessage='')
{
	global $tab;

	echo "<tr valign='top'>\n";
	echo "<td class='sflabel' width='60%' colspan='2'>\n$label";
	if(!empty($submessage))
	{
		echo "<br /><small><strong>$submessage</strong></small>\n";
	}
	echo "<div class='sfformcontainer'>";
	echo "<textarea rows='4' cols='80' class='sftextarea' tabindex='$tab' name='$name'>$value</textarea>\n";
	echo "</div>";
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_checkbox($label, $name, $value, $disabled=false, $large=false, $displayhelp=true)
{
	global $tab;

	echo "<tr valign='top'>\n";

	echo "<td class='sflabel' width='100%' colspan='2'>\n";
	echo "<label for='sf-".$name."'>$label</label>\n";
	echo "<input type='checkbox' tabindex='$tab' name='$name' id='sf-$name' ";
	if($value == true)
	{
		echo "checked='checked' ";
	}
	if($disabled == true)
	{
		echo "disabled='disabled' ";
	}

	echo "/>\n";
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_radiogroup($label, $name, $values, $current, $large=false, $displayhelp=true)
{
	global $tab;

	$pos = 1;

	echo "<tr valign='top'>\n";

	if($large)
	{
		echo "<td class='sflabel' width='60%'>\n";
	} else {
		echo "<td class='sflabel' width='40%'>\n";
	}
	echo $label;
	echo ":\n</td>\n";
	echo "<td>\n";
	foreach($values as $value)
	{
		$check = '';
		if($current == $pos) $check = ' checked="checked" ';
		echo '<label for="sfradio-'.$tab.'" class="sflabel">'.__($value, "sforum").'</label>'."\n";
		echo '<input type="radio" name="'.$name.'" id="sfradio-'.$tab.'"  tabindex="'.$tab.'" value="'.$pos.'" '.$check.' />'."\n";
		$pos++;
		$tab++;
	}
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_radiogroup_confirm($label, $name, $values, $current, $msg, $large=false, $displayhelp=true)
{
	global $tab;

	$pos = 1;

	echo "<tr valign='top'>\n";

	if($large)
	{
		echo "<td class='sflabel' width='60%'>\n";
	} else {
		echo "<td class='sflabel' width='40%'>\n";
	}
	echo $label;
	echo ":\n</td>\n";
	echo "<td>\n";
	foreach($values as $value)
	{
		$check = '';
		$select = '';
		if ($current == $pos)
		{
			$check = " checked = 'checked' ";
		} else {
			$select = " onclick ='sfjtoggleLayer(\"confirm-".$name."\")'";
		}
		echo "<input type='radio' id='sfradio".$pos."' name='".$name."' tabindex='$tab' value='".$pos."'".$check.$select." />";
		echo "<label class='sfradio' for='sfradio".$pos."'>&nbsp;&nbsp;".__($value, 'sforum')."</label><br />";
		$pos++;
	}
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr valign='top' id='confirm-".$name."' style='display:none'>";
	echo "<td>".$msg."</td>";

	echo "<td class='sflabel'>\n";
	echo "<label for='sfconfirm-box-".$name."'>".__('Confirm', 'sforum')."</label>\n";
	echo "<input type='checkbox' name='confirm-box-".$name."' id='sfconfirm-box-".$name."' /></td>\n";
	echo "</tr>";

	$tab++;
	return;
}

function sfa_paint_select_start($label, $name, $helpname)
{
	global $tab;

	echo "<tr valign='top'>\n";
	echo "<td class='sflabel' width='60%'>\n$label";
	echo "\n</td>\n";
	echo "<td>\n";
	echo "<select style='width:130px' class=' sfacontrol' tabindex='$tab' name='$name'>\n";
	$tab++;
	return;
}

function sfa_paint_select_end()
{
	echo "</select>\n";
	echo "</td>\n";
	echo "</tr>\n";
	return;
}

function sfa_paint_link($link, $label)
{
	echo "<tr>\n";
	echo "<td class='sflabel'>\n";
	echo "<a href=\"$link\">$label</a>\n";
	echo "</td>\n";
	echo "</tr>\n";
	return;
}

function sfa_paint_icon($icon)
{
	if(empty($icon)) return;

	$path = SFCUSTOM.$icon;
	if(!file_exists($path))
	{
		echo "<p class='sfoptionerror'>".sprintf(__("Custom Icon '%s' does not exist", "sforum"), $icon)."</p>\n";
	} else {
		echo "&nbsp;<img src='".SFRESOURCES."custom/".$icon."' alt='' />\n";
	}
	return;
}

function sfa_paint_spacer()
{
	echo "<br /><div class='clearboth'></div>";
	return;
}

?>