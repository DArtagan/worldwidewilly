/* ----------------------------------*/
/* general redirect					 */
/* ----------------------------------*/

function sfjreDirect(url)
{
	window.location = url;
}

/* ----------------------------------*/
/* Basically changing re-direct      */
/* ----------------------------------*/

function sfjsetCredentials(baseURL, forumURL)
{
	var lForm = document.forms[0];
	switch(lForm.name)
	{
		case 'loginform':
			lForm.action = baseURL + "wp-login.php?action=login&view=forum";
			break;
			
		case 'registerform':
			lForm.action = baseURL + "wp-login.php?action=register&view=forum";
			var messDiv = document.getElementById('reg_passmail');
			messDiv.style.display="none";
			break;
			
		case 'lostpasswordform':
			lForm.action = baseURL + "wp-login.php?action=lostpassword&view=forum";
			break;
	}

	/* get rid of the nav block */
	var navDiv = document.getElementById('nav');
	if(navDiv != null)
	{
		navDiv.style.display = "none";
	}

	/* redirect on login form after a registration */
	var reDirect = document.getElementsByName("redirect_to");
	if(reDirect != null)
	{
		reDirect[0].value = forumURL;
	}
}

