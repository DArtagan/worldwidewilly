/* ---------------------------------
Simple:Press Forum 
Admin Javascript
$LastChangedDate: 2009-02-19 19:03:14 +0000 (Thu, 19 Feb 2009) $
$Rev: 1424 $
------------------------------------ */

/* ----------------------------------*/
/* The Installer routine             */
/* ----------------------------------*/

var thisPhpUrl;
var thisPhaseCount;
var thisCurrentPhase;
var checkZone;
var message_strings;

function sfjPerformInstall(phpUrl, phaseCount, currentPhase, image, messages)
{
	if(message_strings == null)
	{
		var installtext = new String(messages);
		message_strings = installtext.split("@");
	}
	
	var izone=document.getElementById('imagezone');

	if(parseInt(currentPhase) == (parseInt(phaseCount)+1))
	{
		var checkTimer=window.clearInterval(checkZone);
		var fDiv = document.getElementById('finishzone');
		
		fDiv.innerHTML = '<form name="sfinstalldone" method="post" action="admin.php?page=simple-forum/admin/sf-adminoptions.php"><br /><input type="submit" class="button-secondary" name="goforuminstall" value="' + message_strings[0] + '" /></form>';		
		izone.innerHTML = message_strings[2];
		return;
	}

	if(currentPhase == "0")
	{
		var newHTML = '<br /><img src="' + image + '" /><br />' + message_strings[1] + '<br />';
		izone.innerHTML = newHTML;
	}
	
	thisPhpUrl = phpUrl;
	thisPhaseCount = phaseCount;
	thisCurrentPhase = currentPhase;

	var thisUrl = phpUrl + "phase="+currentPhase;
	var target = "zone"+currentPhase;
	
	ahahRequest(thisUrl, target);
	
	checkZone=self.setInterval("sfjCheckZone()", 3500);
}

function sfjCheckZone()
{
	if(parseInt(thisCurrentPhase) == (parseInt(thisPhaseCount)+1))
	{
		var checkTimer=window.clearInterval(checkZone);
		var izone=document.getElementById('imagezone');
		var fDiv = document.getElementById('finishzone');
		
		izone.innerHTML = message_strings[2];
		fDiv.innerHTML = '<form name="sfinstalldone" method="post" action="admin.php?page=simple-forum/admin/sf-adminoptions.php"><br /><input type="submit" class="button-secondary" name="goforuminstall" value="' + message_strings[0] + '" /></form>';		
		return;
	}

	var targetZone = document.getElementById('zone'+thisCurrentPhase);
	
	if(targetZone.innerHTML != "")
	{
		var checkTimer=window.clearInterval(checkZone);
		thisCurrentPhase++;
		sfjPerformInstall(thisPhpUrl, thisPhaseCount, thisCurrentPhase);
	}
}

/* ----------------------------------*/
/* The Upgrade routine               */
/* ----------------------------------*/

var targetPhase;
var currentPhase;
var checkUpZone;
var blockGraphic;

function sfjPerformUpgrade(phpUrl, startBuild, targetBuild, imageBlock, image, messages)
{
	if(message_strings == null)
	{
		var installtext = new String(messages);
		message_strings = installtext.split("@");
	}

	targetPhase = targetBuild;
	currentPhase = startBuild;
	blockGraphic = imageBlock;
	
	var izone=document.getElementById('imagezone');

	if(parseInt(currentPhase) == parseInt(targetPhase))
	{
		var checkTimer=window.clearInterval(checkUpZone);
		var fDiv = document.getElementById('finishzone');
		
		fDiv.innerHTML = '<form name="sfinstalldone" method="post" action="admin.php?page=simple-forum/admin/sf-adminoptions.php"><br /><input type="submit" class="button-secondary" name="goforumupgrade" value="' + message_strings[0] + '" /></form>';		
		izone.innerHTML = message_strings[2];
		return;
	}

	var checkTarget = document.getElementById('zonecount');
	var zoneContent = checkTarget.innerHTML;

	if(zoneContent.length > 6)
	{
		checkTarget.style.display = "block";
		var checkTimer=window.clearInterval(checkUpZone);
		var fDiv = document.getElementById('finishzone');
		
		fDiv.innerHTML = message_strings[3];	
		izone.innerHTML = "";
		return;
	}

	if(izone.innerHTML == '')
	{
		var newHTML = '<br /><img src="' + image + '" /><br />' + message_strings[1] + '<br />';
		izone.innerHTML = newHTML;
	}

	thisPhpUrl = phpUrl;
	var startPhpUrl = thisPhpUrl + 'start=' + currentPhase;	

	ahahRequest(startPhpUrl, 'zonecount');

	checkUpZone=self.setInterval("sfjCheckUpZone()", 1500);
}

function sfjCheckUpZone()
{
	var fDiv = document.getElementById('finishzone');
	
	if(parseInt(currentPhase) == parseInt(targetPhase))
	{
		var checkTimer=window.clearInterval(checkUpZone);
		var izone=document.getElementById('imagezone');
		
		izone.innerHTML = message_strings[2];
		fDiv.innerHTML = '<form name="sfinstalldone" method="post" action="admin.php?page=simple-forum/admin/sf-adminoptions.php"><br /><input type="submit" class="button-secondary" name="goforumupgrade" value="' + message_strings[0] + '" /></form>';		
		return;
	}

	var checkTarget = document.getElementById('zonecount');

	var statusVal = fDiv.innerHTML;
	fDiv.innerHTML = statusVal + '<img src="' + blockGraphic + '" />';

	/* use this line instead of above for debugging */	
	/* fDiv.innerHTML = statusVal + currentPhase + ' '; */
	
	var startTarget = checkTarget.innerHTML;

	if(parseInt(startTarget) != parseInt(currentPhase))
	{
		var checkTimer=window.clearInterval(checkUpZone);
		sfjPerformUpgrade(thisPhpUrl, startTarget, targetPhase, blockGraphic);
	}
}

/* ----------------------------------*/
/* Open and Close of hidden divs     */
/* ----------------------------------*/

function sfjtoggleLayer(whichLayer)
{
	if (document.getElementById)
	{
		/* this is the way the standards work */
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	}
		else if (document.all)
	{
		/* this is the way old msie versions work */
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
		else if (document.layers)
	{
		/* this is the way nn4 works */
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	var obj = document.getElementById(whichLayer);
	if (whichLayer == 'sfpostform')
	{
		obj.scrollIntoView(top);
	}
}

/* ----------------------------------*/
/* Admin Option Tools                */
/* ----------------------------------*/

function sfjadminTool(url, target, imageFile)
{
	if(imageFile != '')
	{
		document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
	}
	ahahRequest(url, target);
}

function sfjadminMsg(target, imageFile, msg)
{
	var div = document.getElementById(target);
	if(imageFile != '')
	{
		div.innerHTML = '<br /><img src="' + imageFile + '" />&nbsp;&nbsp<b>' + msg + '</b><br />';
		div.style.display = "block";
		
	}
	return false;
}

function sfjshowSubsList(id, url, imageFile)
{
	var subForm = document.getElementById(id);
	var delim;
	var thisValue;
	var showsubs;
	var showwatches;
	var filter;
	var groups="";
	var forums="";
	
	subForm.showsubs.checked ? thisValue='1' : thisValue='0';
	showsubs = "&showsubs=" + thisValue;
	
	subForm.showwatches.checked ? thisValue='1' : thisValue='0';
	showwatches = "&showwatches=" + thisValue;

	if(subForm.sffilterall.checked) filter="&filter=all";
	if(subForm.sffiltergroups.checked)
	{
		filter="&filter=groups";
		var groupIds = document.getElementById('grouplist');
		if(groupIds.value == '')
		{
			groups = "&groups=error";
		} else {
			var x = 0;
			for (i=0;i<groupIds.length;i++)
			{
				if (groupIds.options[i].selected)
				{
					if(x==0 ? delim="" : delim="-");
					groups += delim + groupIds.options[i].value;
					x++;
				}
			}
			if(groups != null)
			{
				groups = "&groups=" + groups;
			} else {
				groups = "&groups=error";
			}		
		}
	}

	if(subForm.sffilterforums.checked)
	{
		filter="&filter=forums";
		var forumIds = document.getElementById('forumlist');
		if(forumIds.value == '')
		{
			forums = "&forums=error";
		} else {
			var x = 0;
			for (i=0;i<forumIds.length;i++)
			{
				if (forumIds.options[i].selected)
				{
					if(x==0 ? delim="" : delim="-");
					forums += delim + forumIds.options[i].value;
					x++;
				}
			}
			if(forums != null)
			{
				forums = "&forums=" + forums;
			} else {
				forums = "&forums=error";
			}
		}
	}
	
	urlGet = url + showsubs + showwatches + filter + groups + forums;

	if(imageFile != '')
	{
		document.getElementById('subsdisplayspot').innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
	}
	ahahRequest(urlGet, 'subsdisplayspot');
}

/* ----------------------------------*/
/* Admin Show Group Members          */
/* ----------------------------------*/

function sfjshowMemberList(url, imageFile, groupID, show, hide)
{

	var showButton = document.getElementById('show'+groupID);
	var memberList = document.getElementById('ugrouplist'+groupID);
	var target = 'ugrouplist'+groupID;

	if(memberList.innerHTML == '')
	{
		if(imageFile != '')
		{
			document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
		} else {
			document.getElementById(target).innerHTML = '';	
		}
		ahahRequest(url, target);
		showButton.innerHTML = hide;
	} 
		else if (memberList.style.display == 'none')
	{
		memberList.style.display = 'block';
		showButton.innerHTML = hide;
	}
		else 
	{
			memberList.style.display = 'none';
			showButton.innerHTML = show;
	}
}

/* ----------------------------------*/
/* Admin Show Group Add Members List */
/* ----------------------------------*/

function sfjshowAddMemberList(url, imageFile, groupID)
{
	var target = 'selectadd'+groupID;
	sfjtoggleLayer('amember-'+groupID);
	if(imageFile != '')
	{
		document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
	}
	ahahRequest(url, target);
}

/* ----------------------------------*/
/* Admin Show Group Del Members List */
/* ----------------------------------*/

function sfjshowDelMemberList(url, imageFile, groupID)
{
	var target = 'selectdel'+groupID;
	sfjtoggleLayer('dmember-'+groupID);
	if(imageFile != '')
	{
		document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
	}
	ahahRequest(url, target);
}

/* ----------------------------------*/
/* Admin Show Group List */
/* ----------------------------------*/

function sfjshowGroupList(url, imageFile)
{
	var target = 'selectgroup';
	sfjtoggleLayer('select-group');
	if(imageFile != '')
	{
		document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
	}
	ahahRequest(url, target);
}

/* ----------------------------------*/
/* Admin Show Forum List */
/* ----------------------------------*/

function sfjshowForumList(url, imageFile)
{
	var target = 'selectforum';
	sfjtoggleLayer('select-forum');
	if(imageFile != '')
	{
		document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
	}
	ahahRequest(url, target);
}

function sfjShowProfile(url, imageFile, rowid)
{
	var target = rowid;
	sfjtoggleLayer(rowid);
	if(imageFile != '')
	{
		document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
	}
	ahahRequest(url, target);
}

function sfjDelPMs(url, imageFile, fade, rowid)
{
	var target = rowid;
	if (fade == 0)
	{
		if(imageFile != '')
		{
			document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
		}
	} else {
		var row = document.getElementById(target);
		if (navigator.appName == "Microsoft Internet Explorer")
		{
			sfjopacity(row.style,9,0,10,function(){sfjremoveIt(row);});
		} else {
			sfjopacity(row.style,199,0,10,function(){sfjhideIt(row);});
		}		
	}

	ahahRequest(url, target);	
}

function sfjDelRank(url, imageFile, fade, rowid)
{
	var target = rowid;
	if (fade == 0)
	{
		if(imageFile != '')
		{
			document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
		}
	} else {
		var row = document.getElementById(target);
		if (navigator.appName == "Microsoft Internet Explorer")
		{
			sfjopacity(row.style,9,0,10,function(){sfjremoveIt(row);});
		} else {
			sfjopacity(row.style,199,0,10,function(){sfjhideIt(row);});
		}		
	}

	ahahRequest(url, target);	
}

function sfjDelWatchesSubs(url, imageFile, fade, rowid)
{
	var target = rowid;

	if (fade == 0)
	{
		if(imageFile != '')
		{
			document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
		}
	} else {
		var row = document.getElementById(target);
		if (navigator.appName == "Microsoft Internet Explorer")
		{
			sfjopacity(row.style,9,0,10,function(){sfjremoveIt(row);});
		} else {
			sfjopacity(row.style,199,0,10,function(){sfjhideIt(row);});
		}		
	}

	ahahRequest(url, target);	
}

function sfjDelCfield(url, imageFile, rowid)
{
	var target = rowid;

	var row = document.getElementById(target);
	if (navigator.appName == "Microsoft Internet Explorer")
	{
		sfjopacity(row.style,9,0,10,function(){sfjremoveIt(row);});
	} else {
		sfjopacity(row.style,199,0,10,function(){sfjhideIt(row);});
	}		

	ahahRequest(url, target);	
}

function sfjremoveIt(target)
{
	target.style.height="0px";
	target.style.borderStyle="none";
	target.style.display="none";
}

function sfjhideIt(target)
{
	target.style.visibility="collapse";
	target.style.borderStyle="none";
}

function sfjopacity(ss,s,e,m,f){
	if(s>e){
		s--;
	}else if(s<e){
		s++;
	}
	sfjsetOpacity(ss,s);
	if(s!=e){
		setTimeout(function(){sfjopacity(ss,s,e,m,f);},Math.round(m/10));
	}else if(s==e){
		if(typeof f=='function'){f();}
	}
}

function sfjsetOpacity(s,o){
	s.opacity=o/100;
	s.MozOpacity=o/100;
	s.KhtmlOpacity=o/100;
	s.filter='alpha(opacity='+o+')';
}

/* ----------------------------------*/
/* AHAH master routines              */
/* ----------------------------------*/

function ahahRequest(url,target) {
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
        req.onreadystatechange = function() {ahahResponse(target);};
        req.open("GET", url, true);
        req.send(null);
    } else if (window.ActiveXObject) {
        req = new ActiveXObject("Microsoft.XMLHTTP");
        if (req) {
            req.onreadystatechange = function() {ahahResponse(target);};
            req.open("GET", url, true);
            req.send();
        }
    }
} 

function ahahResponse(target) {
   /* only if req is "loaded" */
   if (req.readyState == 4) {
       /* only if "OK" */
       if (req.status == 200 || req.status == 304) {
           results = req.responseText;
           document.getElementById(target).innerHTML = results;
       } else {
           document.getElementById(target).innerHTML="ahah error:\n" + req.status + ' ' + req.statusText;
       }
   }
}

/* ----------------------------------*/
/* Check/Uncheck box collection      */
/* ----------------------------------*/

function sfjcheckAll(container)
{
	jQuery(container).find('input[type=checkbox]:not(:checked)').each(function()
	{
		jQuery('label[for='+jQuery(this).attr('id')+']').trigger('click');
		if(jQuery.browser.msie)
		{
			jQuery(this).attr('checked','checked');
		}else{
			jQuery(this).trigger('click');
		};
	});
}


function sfjuncheckAll(container)
{
	jQuery(container).find('input[type=checkbox]:checked').each(function()
	{
		jQuery('label[for='+jQuery(this).attr('id')+']').trigger('click');
		if(jQuery.browser.msie)
		{
			jQuery(this).attr('checked','');
		}else{
			jQuery(this).trigger('click');
		};
	});
}


function sfjDelTbButton(button, message)
{
	if(confirm(message))
	{
		var remList = document.getElementById("delbuttons");
		var currentList = remList.value;

		if(currentList == "")
		{
			remList.value = button.id;
		} else {
			remList.value = currentList + "&" + button.id;
		}
		button.style.display = "none";
	}
}

function sfjSubmit(target)
{
	var submitField = document.getElementById(target);
	submitField.value = "update";
	document.forms[0].submit();
}

