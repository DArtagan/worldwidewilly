/* ---------------------------------
Simple:Press Forum - Version 4.0
Private Messaging Extension Javascript

$LastChangedDate: 2008-10-08 18:17:21 +0100 (Wed, 08 Oct 2008) $
$Rev: 714 $
------------------------------------ */

/* ----------------------------------
Toggle a thread open and closed
-------------------------------------*/
function sfjtoggleThread(cCell, targetDiv, rowIndex)
{
	sfjtoggleLayer(targetDiv);
	var n1 = cCell.parentNode;
	var cRow = n1.parentNode;

	if(cRow.className == "sfpmshow")
	{
		cRow.className = "sfpmread";
	} else {
		cRow.className = "sfpmshow";
	}
}

/* ----------------------------------
Load PM message text in inbox
-------------------------------------*/
function sfjgetPMText(imageFile, url, pmId, box, status)
{

	/* status == 0 means unread */

	var messageTarget = 'sfpm'+pmId;
	var infoTarget = 'sfpminfo'+pmId;
	
	var content = document.getElementById(messageTarget);
	var info = document.getElementById(infoTarget);
	
	if (content.innerHTML == '')
	{
		content.style.display = 'block';
		info.style.display = 'block';

		content.innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
		
		infoUrl = url + 'pminfo=' + pmId + '&pmaction=' + box;
		jah(infoUrl, infoTarget);
		
		messageUrl = url + 'pmshow=' + pmId + '&pmaction=' + box;
		jah(messageUrl, messageTarget);

		if(box == "inbox")
		{
			document.getElementById('pmreply-'+pmId).style.display="block";
			document.getElementById('pmquote-'+pmId).style.display="block";
			var replyall = document.getElementById('pmreplyall-'+pmId);
			var quoteall = document.getElementById('pmquoteall-'+pmId);
			if(replyall != null)
			{
				replyall.style.display="block";
				quoteall.style.display="block";
			}
		}
		
		/* can we reduce counter */
		if(status == '0')
		{
			var counter = document.getElementById('sfunreadpm');
			var pcount = parseInt(counter.innerHTML);
			if(isNaN(pcount) || pcount == 0) 
			{
				pcount = 0;
			} else {
				pcount--;
			}
			counter.style.color = '#ffffff';
			counter.innerHTML = pcount;
		}				
	} 
		else
	{
		content.innerHTML = '';
		content.style.display = 'none';

		info.innerHTML = '';
		info.style.display = 'none';
		
		if(box == "inbox")
		{
			document.getElementById('pmreply-'+pmId).style.display="none";
			document.getElementById('pmquote-'+pmId).style.display="none";
			var replyall = document.getElementById('pmreplyall-'+pmId);
			var quoteall = document.getElementById('pmquoteall-'+pmId);
			if(replyall != null)
			{
				replyall.style.display="none";
				quoteall.style.display="none";
			}
		}
	}
}

/* ----------------------------------
Send PM to selected user
-------------------------------------*/
function sfjsendPMTo(recipient, name, title, reply, slug)
{
	var toField = document.getElementById('pmmembers');
	var titleField = document.getElementById('pmtitle');
	var slugfield = document.getElementById('pmslug');
	var isreply = document.getElementById('pmreply');

	toField.value = recipient;
	titleField.value = title;
	slugfield.value = slug;
	isreply.value = reply;

	var targetnames = document.getElementById('pmtonamelist');
	var targetids = document.getElementById('pmtoidlist');
	var found = false;

	nameList = name.split(",");
	idList = recipient.split(",");

	for(x=idList.length-1;x>=0;x--)
	{

		for(i=targetnames.options.length-1;i>=0;i--)
		{
			if (targetnames.options[i].text == nameList[x])
			{
				found = true;
			}
		}
		if(!found)
		{
			var thisOption = new Option(nameList[x], idList[x], true, true);
			var positionOption = targetnames.length;
			targetnames.options[positionOption] = thisOption;
		
			if (targetids.value.length > 0)
			{
				targetids.value += "-";
			}
			targetids.value += idList[x];
	
			if(targetnames.options[0].value == '')
			{
				targetnames.remove(targetnames.options[0]);
			}
		}
	}

	if(document.getElementById('sfpostform').style.display != "block")
	{
		sfjtoggleLayer('sfpostform');
	}
}

/* ----------------------------------
Send PM to selected user - quoted
-------------------------------------*/
function sfjquotePM(recipient, pmid, intro, rte, name, title, reply, slug)
{
	var postcontent = document.getElementById(pmid).innerHTML;
	document.addpm.postitem.value = '<blockquote>'+intro+postcontent+'</blockquote><hr />';

	if (rte)
	{
		tinyMCE.get('postitem').getBody().innerHTML = '<blockquote>'+intro+postcontent+'</blockquote><hr /><p><br /></p>';
	}

	sfjsendPMTo(recipient, name, title, reply, slug);
}

/* ----------------------------------
Delete a complete PM thread
-------------------------------------*/
function sfjdeleteThread(cCell, url, rowIndex, targetDiv)
{
	var pmTable = document.getElementById('sfmainpmtable');
	var target = pmTable.rows[rowIndex];

	var n1 = cCell.parentNode;
	var cRow = n1.parentNode;

	if(cRow.className == "sfpmshow")
	{
		sfjtoggleLayer(targetDiv);
	}
	
	if (navigator.appName == "Microsoft Internet Explorer")
	{
		sfjopacity(target.style,9,0,10,function(){sfjremoveIt(target);});
	} else {
		sfjopacity(target.style,199,0,10,function(){sfjhideIt(target);});
	}

	ahahRequest(url, 'sfdummy');
}

/* ----------------------------------
Delete a PM
-------------------------------------*/
function sfjdeletePM(cCell, messageUrl, threadUrl, rowIndex, threadRowIndex, threadDiv, threadSlug)
{
	var pmTable = document.getElementById('sfmessagetable-'+threadSlug);
	var target = pmTable.rows[rowIndex];

	msgDiv = document.getElementById('sfpm'+threadSlug);

	if (navigator.appName == "Microsoft Internet Explorer")
	{
		sfjopacity(target.style,9,0,10,function(){sfjremoveIt(target);});
	} else {
		sfjopacity(target.style,199,0,10,function(){sfjhideIt(target);});
	}
	/* try reducing the thread count */
	var threadCount = document.getElementById('pm-' + threadSlug + 'count');

	var pcount = parseInt(threadCount.innerHTML);
	if(isNaN(pcount))
	{
		return;
	} else {
		pcount--;
		threadCount.innerHTML = pcount;
	}
	
	if(pcount == 0)
	{
		/* delete thread */
		var threadCell = document.getElementById('pm-' + threadSlug+'delthread');
		sfjdeleteThread(threadCell, threadUrl, threadRowIndex, threadDiv);
	} else {
		/* delete message */
		ahahRequest(messageUrl, 'sfdummy');
	}
}

/* ----------------------------------
Delete complete box
-------------------------------------*/
function sfjdeleteMassPM(url)
{
	var pmTable = document.getElementById('sfmainpmtable');

	if (navigator.appName == "Microsoft Internet Explorer")
	{
		sfjopacity(pmTable.style,9,0,10,function(){sfjremoveIt(pmTable);});
	} else {
		sfjopacity(pmTable.style,199,0,10,function(){sfjhideIt(pmTable);});
	}

	ahahRequest(url, 'sfdummy');
}

/* ----------------------------------
Populate member list from filter
-------------------------------------*/
function sfpopulateMembers(url, action)
{
	if(action == 'all')
	{
		url += 'pop=all';
	} else {
		sChar = document.getElementById('asearch').value;
		if(sChar == null)
		{
			return;
		} else {
			url += 'pop='+sChar;
		}
	}
	
	jQuery('#pmmembers').load(url);
}

/* ----------------------------------
Add recipients to buddy list
-------------------------------------*/
function sfaddBuddies(url)
{
	var rList = document.getElementById('pmtoidlist').value;
	if((rList == null) || (rList == '0'))
	{
		return;
	}

	url += 'addbuddy='+rList;
	jah(url, 'pmbuddies');
}

/* ----------------------------------
Select Add recipients for PM
-------------------------------------*/
function sfjaddpmUser(source)
{
	var source = document.getElementById(source);
	var targetnames = document.getElementById('pmtonamelist');
	var targetids = document.getElementById('pmtoidlist');
	var found = false;

	for(i=targetnames.options.length-1;i>=0;i--)
	{
		if (targetnames.options[i].value == source.value)
		{
			found = true;
		}
	}
	if(!found)
	{
		var thisOption = new Option(source.options[source.selectedIndex].text, source.value, true, true);
		var positionOption = targetnames.length;
		targetnames.options[positionOption] = thisOption;

		if (targetids.value.length > 0)
		{
			targetids.value += "-";
		}
		targetids.value += source.value;

		if(targetnames.options[0].value == '')
		{
			targetnames.remove(targetnames.options[0]);
		}
	}
}

/* ----------------------------------
Select Remove recipients for PM
-------------------------------------*/
function sfjremovepmUser()
{
	var targetnames = document.getElementById('pmtonamelist');
	var targetids = document.getElementById('pmtoidlist');
	var i;

	targetnames.remove(targetnames.selectedIndex);
	
	targetids.value = '';
	for(i=targetnames.options.length-1;i>=0;i--)
	{
		targetnames.options[i].selected = true;
		if (targetids.value.length > 0)
		{
			targetids.value += "-";
		}
		targetids.value += targetnames.options[i].value;
	}
}
