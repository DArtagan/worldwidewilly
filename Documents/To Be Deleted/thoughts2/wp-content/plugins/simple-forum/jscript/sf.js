/* ---------------------------------
Simple:Press Forum - Version 4.0
Base Front-end Forum Javascript

$LastChangedDate: 2009-04-25 00:03:01 +0100 (Sat, 25 Apr 2009) $
$Rev: 1778 $
------------------------------------ */

/* ----------------------------------
Validate the PM form	     
-------------------------------------*/
function sfjvalidatePMForm(theForm, editor, msg0, msg1, msg2, msg3)
{	
	var reason = "";
	reason += sfjvalidateThis(theForm.pmtoidlist, " - " + msg1);
	reason += sfjvalidateThis(theForm.pmtitle, " - " + msg2);
	if(editor == 'QT')
	{
		reason += sfjvalidateThis(theForm.postitem, " - " + msg3);
	} else {
		reason += sfjvalidateTiny('postitem', " - " + msg3);
	}
	if (reason != "") 

	{
		var target = document.getElementById('sfvalid');
		target.innerHTML = "<br />" + msg0 + ":<br /><br />" + reason + "<br /><br />";
		var box = hs.htmlExpand(document.getElementById('sfsave'), {contentId: 'my-content', preserveContent: false});
		return false;
	}
	return true;
}

/* ----------------------------------
Validate the new post form	     
-------------------------------------*/
function sfjvalidatePostForm(theForm, editor, msg0, msg1, msg2, msg3, msg4, msg5, msg6)
{

	var reason = "";
	if(msg1 != '') reason += sfjvalidateThis(theForm.guestname, " - " + msg1);
	if(msg2 != '') reason += sfjvalidateThis(theForm.guestemail, " - " + msg2);
	if(msg3 != '') reason += sfjvalidateThis(theForm.newtopicname, " - " + msg3);
	if(msg4 != '') reason += sfjvalidateThis(theForm.sfvalue1, " - " + msg4);
	if(editor == 'QT')
	{
		if(msg5 != '') reason += sfjvalidateThis(theForm.postitem, " - " + msg5);
	} else {
		if(msg5 != '') reason += sfjvalidateTiny('postitem', " - " + msg5);
	}

	if(msg6 != '')
	{
		if(editor == 'QT')
		{
			var thisPost = theForm.postitem;
		} else {
			var thisPost = tinyMCE.get('postitem').getContent();
		}

		var found = false;
		var checkWords = new Array();
		checkWords[0] = '<span class="';
		checkWords[1] = 'font-size:';
		checkWords[2] = 'MsoPlainText';
		checkWords[3] = 'MsoNormal';
		checkWords[4] = 'mso-layout-grid-align';
		checkWords[5] = 'mso-pagination';
		checkWords[6] = 'white-space:';
		checkWords[7] = 'font-family';

		for (i=0;i<checkWords.length;i++)
		{
			if(thisPost.match(checkWords[i]) != null)
			{
				found = true;

			}
		}
		if(found)
		{
			reason += "<strong>" + msg6 + "</strong><br />";
		}
	}

	if (reason != "") 
	{
		var target = document.getElementById('sfvalid');
		target.innerHTML = "<br />" + msg0 + ":<br /><br />" + reason + "<br /><br />";
		var box = hs.htmlExpand(document.getElementById('sfsave'), {contentId: 'my-content', preserveContent: true});

		return false;
	}
	return true;
}

/* ----------------------------------
Validatation support routines
-------------------------------------*/
function sfjvalidateThis(theField, errorMsg)
{
	var error = "";
	if (theField.value.length == 0) 
	{
		error = "<strong>" + errorMsg + "</strong><br />";
	}
	return error;
}

function sfjvalidateTiny(thisField, errorMsg)
{
	var error = "";
	var stuff = tinyMCE.get(thisField).getContent();
	if(stuff == '')
	{
		error = "<strong>" + errorMsg + "</strong><br />";
	}
	return error;
}

/* ----------------------------------
Display Users Email Address
-------------------------------------*/
function sfjshowUserMail(label, address, id)
{
	var param1 = 'sfmail'+id;
	var param2 = 'mail-content'+id;
	var param3 = 'sfshowmail'+id;
	
	var target = document.getElementById(param1);
	target.innerHTML = label + "<br />" + address;
	var box = hs.htmlExpand(document.getElementById(param3), {contentId: param2});
}

/* ----------------------------------
Display Post Permalink
-------------------------------------*/
function sfjshowPostLink(label, url, id)
{
	var param1 = 'sfpostlink'+id;
	var param2 = 'link-content'+id;
	var param3 = 'sfshowlink'+id;
	
	var target = document.getElementById(param1);
	target.innerHTML = label + "<br /><p>" + url + "</p>";
	var box = hs.htmlExpand(document.getElementById(param3), {contentId: param2});
}

/* ----------------------------------
Open and Close of hidden divs
-------------------------------------*/
function sfjtoggleLayer(whichLayer)
{
	if (document.getElementById)
	{
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	}
		else if (document.all)
	{
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
		else if (document.layers)
	{
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	var obj = document.getElementById(whichLayer);
	if (whichLayer == 'sfpostform' || whichLayer == 'sfsearchform')
	{
		obj.scrollIntoView(false);
	}
}

/* ----------------------------------
Quote Post insertion
-------------------------------------*/
function sfjquotePost(postid, intro, rte)
{
	sfjtoggleLayer('sfpostform');	
	var postcontent = document.getElementById(postid).innerHTML;
	document.addpost.postitem.value = '<blockquote>'+intro+postcontent+'</blockquote><hr />';

	if (rte)
	{
		tinyMCE.get('postitem').getBody().innerHTML = '<blockquote>'+intro+postcontent+'</blockquote><hr /><p><br /></p>';
	}
}

/* ----------------------------------
Enable Save buttons on Math entry
-------------------------------------*/
function sfjsetPostButton(result, val1, val2, gbuttontext, bbuttontext) 
{
	var button = document.addpost.newpost;
	
	if(result.value == (val1+val2))
	{
		button.disabled=false;
		button.value = gbuttontext;
	} else {
		button.disabled=true;
		button.value = bbuttontext;
	}
}

function sfjsetTopicButton(result, val1, val2, gbuttontext, bbuttontext)
{
	var button = document.addtopic.newtopic;
	
	if(result.value == (val1+val2))
	{
		button.disabled=false;
		button.value = gbuttontext;
	} else {
		button.disabled=true;
		button.value = bbuttontext;
	}
}

/* ----------------------------------
Enable Register after policy accept
-------------------------------------*/

/* CAN BE REMOVED IF BUGFIX WORKS ON IE */
function sfjtoggleRegister(cBox)
{
	var button = document.getElementById("regbutton");

	if(cBox.checked == true)
	{
//		button.style.display = "block";
		button.disabled=false;
	} else {
//		button.style.display = "none";
		button.disabled=true;
	}
}

/* ----------------------------------
Trigger redirect on drop down
-------------------------------------*/
function sfjchangeURL(menuObj)
{
	var i = menuObj.selectedIndex;
	
	if(i > 0)
	{
	if(menuObj.options[i].value != '#')
		{
			window.location = menuObj.options[i].value;
		}
	}
}

/* ----------------------------------
URL redirect
-------------------------------------*/
function sfjreDirect(url)
{
	window.location = url;
}

/* ----------------------------------
Post ratings
-------------------------------------*/
function sfjRatePost(postid, url)
{
	var ratingpost = 'sfpostrating-' + postid;
	ahahRequest(url, ratingpost);
}

function sfjstarhover(postid, stars, img_src)
{
	for(i=stars;i>0;i--)
	{
	   	var img_name = 'star-' + postid + '-' + i;
  		document.getElementById(img_name).src = img_src;
	}
}

function sfjstarunhover(postid, stars, img1_src, img2_src)
{
	for(i=5; i>stars; i--)
	{
	   	var img_name = 'star-' + postid + '-' + i;
  		document.getElementById(img_name).src = img2_src;
	}

	for(i=stars;i>0;i--)
	{
	   	var img_name = 'star-' + postid + '-' + i;
  		document.getElementById(img_name).src = img1_src;
	}
}

/* ----------------------------------
Load up categories for linking
-------------------------------------*/
function sfjgetCategories(imageFile, url, checked)
{
	var cat = document.getElementById('sfcats');
	
	if(checked)
	{
		cat.style.display="block";
		cat.innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
		ahahRequest(url, 'sfcats');
	} else {
		cat.style.display="none";
	}
}

/* ----------------------------------
Sets topic status in topic view
-------------------------------------*/
function sfjsetStatus(selectedStatus, url)
{
	if(selectedStatus.options[selectedStatus.selectedIndex].value != 0)
	{
		var tsurl;
		var tsheader;
		var tstopic;
		var tsaddpform;
		var tsupheader;
		var tspform;
		
		tsheader = document.getElementById('ts-header');
		tstopic = document.getElementById('ts-topic');
		tsaddpform = document.getElementById('ts-addpform');
		tsupheader = document.getElementById('ts-upheader');
		tspform = document.getElementById('ts-pform');
		
		tsheader.style.display = 'block';
		tstopic.style.display = 'block';
		tsaddpform.style.display = 'block';
		
		tsurl = url+ '&newtext='+selectedStatus.options[selectedStatus.selectedIndex].text +'&newvalue='+selectedStatus.selectedIndex;
		ahahRequest(tsurl, 'ts-upinline');
	
		tsupheader.innerHTML = selectedStatus.options[selectedStatus.selectedIndex].text;	
		tspform.innerHTML = selectedStatus.options[selectedStatus.selectedIndex].text;
	}
}

/* ----------------------------------
Remove buddy for PM
-------------------------------------*/
function sfjremoveBuddy(url, rowid)
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

/* ----------------------------------
Show member profile
-------------------------------------*/
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

/* ----------------------------------
Error and Success message line
-------------------------------------*/
function sfjmDisplay()
{
	var d=document;
	var commDiv = d.getElementById('sfcomm');
	
	jQuery('#sfcomm').show('slow');
	if (navigator.appName == "Microsoft Internet Explorer")
	{
		sfjopacity(commDiv.style,99,0,10,function(){ commDiv.parentNode.removeChild(commDiv);});
	} else {
		sfjopacity(commDiv.style,399,0,10,function(){commDiv.parentNode.removeChild(commDiv);});
	}
}

/* ----------------------------------
Set Opacity on fade outs
-------------------------------------*/
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

/* ---------------------------------------
Drop Down/ Overlapping Content
 Dynamic Drive (www.dynamicdrive.com)
------------------------------------------*/
function sfjgetposOffset(sfjboxOverlay, offsettype)
{
	var totaloffset=(offsettype=="left")? sfjboxOverlay.offsetLeft : sfjboxOverlay.offsetTop;
	var parentEl=sfjboxOverlay.offsetParent;
	while (parentEl!=null)
	{
		totaloffset=(offsettype=="left")? totaloffset+parentEl.offsetLeft : totaloffset+parentEl.offsetTop;
		parentEl=parentEl.offsetParent;
	}
	return totaloffset;
}

function sfjboxOverlay(curobj, subobjstr, opt_position)
{
	if (document.getElementById)
	{
		var subobj=document.getElementById(subobjstr);
		subobj.style.display=(subobj.style.display!="block")? "block" : "none";
		var xpos=sfjgetposOffset(curobj, "left")+((typeof opt_position!="undefined" && opt_position.indexOf("right")!=-1)? -(subobj.offsetWidth-curobj.offsetWidth) : 0);
		var ypos=sfjgetposOffset(curobj, "top")+((typeof opt_position!="undefined" && opt_position.indexOf("bottom")!=-1)? curobj.offsetHeight : 0);
		subobj.style.left=xpos+"px";
		subobj.style.top=ypos+"px";
		return false;
	}
		else 
	{
		return true;
	}
}

/* ----------------------------------
Announce New Post Tag
-------------------------------------*/
var oInterval;

function sfjNewPostCheck(url, target, timer)
{
	var sfInterval = window.setInterval("ahahRequest('" + url + "', '" + target + "')", timer);
}

/* ----------------------------------
Post/Inbox/Quicklinks/Watch Updates
-------------------------------------*/
function sfjAutoUpdate(url, timer)
{
	var sfInterval = window.setInterval("sfjperformUpdates('" + url + "')", timer);
}

function sfjperformUpdates(url)
{
	/* still logged in? */
	var targetIDUser = document.getElementById("sfthisuser");
	if(targetIDUser != null)
	{
		var userid = targetIDUser.innerHTML;
		if(userid == null || userid == '')
		{
			userid = '0';
		}
		var userCheckUrl = url + "?target=checkuser&thisuser=" + userid;
		jQuery('#sflogininfo').load(userCheckUrl);
	}
	
	/* inbox */ 
	var targetIDInbox = document.getElementById("sfinboxcount");
	if(targetIDInbox != null)
	{
		var inBoxUrl = url + "?target=inbox";
	
		jQuery('#sfinboxcount').load(inBoxUrl);
		
		var pmbox = document.getElementById('pmview');
		if(pmbox != null)
		{
			pmboxUrl = url + "?target=pmview&show=" + pmbox.innerHTML;
			jQuery('#pmcontainer').load(pmboxUrl);
		}
	}
	
	/* newpost counts */
	var targetIDNewpost = document.getElementById("sfpostnumbers");
	if(targetIDNewpost != null)
	{
		var newPostsUrl = url + "?target=newposts";
		jQuery('#sfpostnumbers').load(newPostsUrl);
	}
	
	/* top quicklinks */
	var targetIDQuicklinksTop = document.getElementById("sfqlposts");
	if(targetIDQuicklinksTop != null)
	{
		var quickLinksUrl = url + "?target=quicklinks";
		jQuery('#sfqlposts').load(quickLinksUrl);
	}

	/* bottom quicklinks */
	var targetIDQuicklinksBottom = document.getElementById("sfqlpostsbottom");
	if(targetIDQuicklinksBottom != null)
	{
		var quickLinksUrl = url + "?target=quicklinks";
		jQuery('#sfqlpostsbottom').load(quickLinksUrl);
	}

}

/* ----------------------------------
Embed smily into post form
-------------------------------------*/
function sfjLoadSmiley(file, title, path, code, editor)
{
	if(editor == 1)
	{
		/* tinymce editor */
		tinyMCE.execCommand('mceInsertContent',false, '<img src="'+path+file+'" title="'+title+'" alt="'+title+'" />');	
	} else {
		/* all the rest */
		var postField = document.getElementById("postitem");
		
		/* IE support */
		if (document.selection) {
			postField.focus();
			sel = document.selection.createRange();
			sel.text = code;
			postField.focus();
		}
		/* MOZILLA/NETSCAPE support */
		else if (postField.selectionStart || postField.selectionStart == '0') {
			var startPos = postField.selectionStart;
			var endPos = postField.selectionEnd;
			postField.value = postField.value.substring(0, startPos)
		              + code
                      + postField.value.substring(endPos, postField.value.length);
			postField.focus();
			postField.selectionStart = startPos + code.length;
			postField.selectionEnd = startPos + code.length;
		} else {
			postField.value += code;
			postField.focus();
		}
	}
}

/* ----------------------------------
jah master routines (replace ahah)
Allows for concurrent ahah calls
-------------------------------------*/
var jah_targets = new Array();

function jah(url,target) 
{
	if (window.XMLHttpRequest) 
	{
		var idx = jah_targets.length;
		jah_targets[idx] = new XMLHttpRequest();
		jah_targets[idx].onreadystatechange = function() {jahDone(target, idx);};
		jah_targets[idx].open("GET", url, true);
		jah_targets[idx].send(null);
	} else if (window.ActiveXObject) 
	{
		jah_targets[idx] = new ActiveXObject("Microsoft.XMLHTTP");
		if (jah_targets[idx]) 
		{
			jah_targets[idx].onreadystatechange = function() {jahDone(target);};
			jah_targets[idx].open("GET", url, true);
			jah_targets[idx].send();
		}
	}
}

function jahDone(target, idx)
{
	if (jah_targets[idx].readyState == 4)
	{
		if (jah_targets[idx].status == 200) 
		{
			results = jah_targets[idx].responseText;
			document.getElementById(target).innerHTML = results;
		} else {
			document.getElementById(target).innerHTML="Error:\n" +
			jah_targets[idx].statusText + " (status=" +
			jah_targets[idx].status + ", readyState=" +
			jah_targets[idx].readyState + ")";
		}
	}
}

/* ----------------------------------
AHAH master routines
-------------------------------------*/
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
   if (req.readyState == 4) {
       if (req.status == 200 || req.status == 304) {
           results = req.responseText;
           document.getElementById(target).innerHTML = results;
       } else {
           document.getElementById(target).innerHTML="ahah error:\n" + req.status + ' ' + req.statusText;
       }
   }
}

/* ----------------------------------
Load up admins new post list
-------------------------------------*/
function sfjgetNewPostList(url, numbersurl, fixed)
{
	jQuery('#sfbarspinner').show();
	
	/* newpost counts */
	var targetID = document.getElementById("sfpostnumbers");
	if(targetID != null)
	{
		var newPostsUrl = numbersurl + "?target=newposts";
		jQuery('#sfpostnumbers').load(newPostsUrl);
	}

	/* position */
	if(fixed=='0')
	{
		var targetdiv = 'sfadminpostlist';
	} else { 
		var targetdiv = 'sfadminpostlistfixed';
	}

	var dropdown = document.getElementById(targetdiv);

	if(dropdown.style.display == 'block')
	{
		jQuery('#'+targetdiv).hide('normal', function () {
			dropdown.style.display = "none";
		});
	} else {
		jQuery('#'+targetdiv).load(url, function () {		
			if(fixed) {
				jQuery('#'+targetdiv).css("max-height", (window.outerHeight-80));
			}
			jQuery('#'+targetdiv).show('normal');
			dropdown.style.display = "block";
		});
	}
}

/* ----------------------------------
Post moderation and unread
-------------------------------------*/
function sfjmoderatePost(loadMsg, posturl, url, canRemove, postid, forumid, topicid, poststatus, action)
{
	var spot=document.getElementById('sfmsgspot');
	spot.innerHTML = '';

	jQuery('#sfmsgspot').fadeIn('fast');
	
	var thistopic = 'thistopic' + topicid;
	var topicrow = 'topicrow' + topicid;
	var modpostrowid = 'modpostrow' + topicid;
	var topics = 'tcount' + forumid;
	var posts = 'pcount' + topicid;
	var postsmod = 'pcountmod' + topicid;
	var postsord = 'pcountord' + topicid;
	var forumrow = 'forumrow' + forumid;
	var thispost = 'thispost' + postid;
	var thispostcon = 'thispostcon' + postid;
	
	var topicCount = document.getElementById(topics);
	var postcount = document.getElementById(posts);
	var postcountMod = document.getElementById(postsmod);
	var postcountOrd = document.getElementById(postsord);

	/* reduce topic count by one unless deleting a post with more than one in topic */
	if((action != 2) || (action == 2 && postcount.value == 1))
	{
		topicCount.value--;
	}

	/* if deleting a post where there is nore than one post in topic then just remove post rows. */	
	if(action == 2 && postcount.value != 1)
	{
		var target1 = document.getElementById(thispost);
		var target2 = document.getElementById(thispostcon);
	} else {
		var target1 = document.getElementById(modpostrowid);
		var target2 = document.getElementById(topicrow);
	}
	
	var targetf = document.getElementById(forumrow);

	if (navigator.appName == "Microsoft Internet Explorer")
	{
		sfjopacity(target1.style,9,0,10,function(){sfjremoveIt(target1);});
		sfjopacity(target2.style,9,0,10,function(){sfjremoveIt(target2);});
		if(topicCount.value == 0)
		{
			sfjopacity(targetf.style,9,0,10,function(){sfjremoveIt(targetf);});
		}
	} else {
		sfjopacity(target1.style,199,0,10,function(){sfjhideIt(target1);});
		sfjopacity(target2.style,199,0,10,function(){sfjhideIt(target2);});
		if(topicCount.value == 0)
		{
			sfjopacity(targetf.style,99,0,10,function(){sfjhideIt(targetf);});
		}
	}

	/* Call the moderation/approval/delete code */
	if(posturl != '')
	{
		jQuery('#sfmsgspot').load(url, function() {
			var spot=document.getElementById('sfmsgspot');
		});
	} else {
		jQuery('#sfmsgspot').load(url);
	}

	/* set up count vars */
	var removeMod = new Number(0);
	var removeOrd = new Number(0);	
	
	/* if it's delete change the action for ease */
	if(action == 2)
	{
		postcount.value--;
		if(poststatus == 1)
		{
			action = 0;
			postcountMod.value--;
			removeMod = 1;
		} else {
			action = 1;
			postcountOrd.value--;
			removeOrd = 1;
		}
	} else {
		removeMod = postcountMod.value;
		removeOrd = postcountOrd.value;
	}
	
	if(canRemove)
	{
		if(action == 1 || removeOrd != 0)
		{
			var counter = document.getElementById('sfunread');
			var mastercount = parseInt(counter.innerHTML);
			if(isNaN(mastercount)) 
			{
				mastercount = 0;
			} else {
				mastercount = (mastercount-removeOrd);
			}
			counter.style.color = '#ffffff';
			counter.innerHTML = mastercount;
		} 

		if(action == 0 || action == 9 || removeMod != 0)
		{
			var counter = document.getElementById('sfmod');	
			var mastercount = parseInt(counter.innerHTML);
			if(isNaN(mastercount)) 
			{
				mastercount = 0;
			} else {
				mastercount = (mastercount-removeMod);
			}
			counter.style.color = '#ffffff';
			counter.innerHTML = mastercount;
		}
	}

	/*  have we finished them all? */	
	if(document.getElementById('sfunread').innerHTML== '0' && document.getElementById('sfmod').innerHTML == '0')
	{
		var mainDiv = '#sfadminpostlist';
		var targetdiv = document.getElementById('sfadminpostlist');
		if(targetdiv == null)
		{
			mainDiv = '#sfadminpostlistfixed';
		}
		jQuery(mainDiv).fadeOut(3000);
	}

	var spot=document.getElementById('sfmsgspot');
	if(posturl != '')
	{
		spot.innerHTML = loadMsg;
		if(posturl == window.location)
		{
			window.location.reload();
		} else {
			window.location = posturl;
		}
	} else {
		jQuery('#sfmsgspot').fadeOut(6000, function () {
		});
	}
}

function sfjsaveQuickReply(theForm, saveurl, modurl, postid, forumid, topicid, poststatus, action)
{
	var mText = theForm.elements['postitem'+topicid].value;
	var cText = mText.replace(/\n/g, "<br />");

	cText=encodeURIComponent(cText);

	url = saveurl +  "&status="+theForm.elements['statvalue'].value + "&watch="+theForm.elements['watchtopic'+topicid].checked +"&postitem="+cText;
	jah(url, 'sfmsgspot');
	sfjmoderatePost('', '', modurl, '1', postid, forumid, topicid, poststatus, action);
	
	return false;
}


/* ----------------------------------
General purpose table row removal
-------------------------------------*/
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
	target.style.display="none";
}

function sfjcloseIt(target)
{
	target.style.display="none";
}