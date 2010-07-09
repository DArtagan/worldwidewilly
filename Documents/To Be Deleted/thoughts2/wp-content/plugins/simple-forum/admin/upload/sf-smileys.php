<?php
/*
Simple:Press Forum
Smileys Uploading
$LastChangedDate: 2009-01-10 02:20:31 +0000 (Sat, 10 Jan 2009) $
$Rev: 1161 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_upload_smiley()
{
	define('SMPATH', WP_CONTENT_DIR . '/forum-smileys/');
	
	# create file vars to make things easier to read.
	$filename = $_FILES['newsmileyfile']['name'];
	$filesize = $_FILES['newsmileyfile']['size'];
	$file_tmp = $_FILES['newsmileyfile']['tmp_name'];
	$file_err = $_FILES['newsmileyfile']['error'];
	$file_ext = strrchr($filename, '.');

	# check if user actually put something in the file input field.
	if (($file_err == 0) && ($filesize != 0))
	{
		# Check extension.
		if (!$file_ext)
		{
			unlink($file_tmp);
			return '1@'.__('SMILEY UPLOAD ERROR: File must have an extension', "sforum");
		}

		# check extension type
		if(!strpos(' .gif .GIF .png .PNG .jpg .JPG .jpeg .JPEG ', $file_ext))
		{
			return '1@'.__('SMILEY UPLOAD ERROR: Unrecognised File Extension', "sforum");
		}
		
		# check upload directory OK
		$handle = @opendir(SMPATH);
		if ($handle) 
		{
			closedir($handle);
		} else {
			return '1@'.__('SMILEY UPLOAD ERROR: Target folder cannot be opened', "sforum");
		}
		
		# extra check to prevent file attacks.
		if (is_uploaded_file($file_tmp))
		{
			# copy the file from the temporary upload directory
			if (@move_uploaded_file($file_tmp, SMPATH.$filename))
			{
				chmod(SMPATH.$filename, 0777);

				# success!
				return '0@'.__('Smiley Successfully Uploaded', "sforum");
			}
			else
			{
				# error moving file. check file permissions.
				unlink($file_tmp);
				return '1@'.__('SMILEY UPLOAD ERROR: Unable to move file to designated directory', "sforum");
			}
		}
		else
		{
			# file seems suspicious... delete file and error out.
			unlink($file_tmp);
			return '1@'.__('SMILEY UPLOAD ERROR: File does not appear to be a valid upload', "sforum");
		}
	}
	else
	{
		# Kill temp file, if any, and display error.
		if ($file_tmp != '')
		{
			unlink($file_tmp);
		}

		switch ($file_err)
		{
			case '0':
				$mess = '1@'.__('SMILEY UPLOAD ERROR: That is not a valid file. 0 byte length.', "sforum");
				break;

			case '1':
				$mess = '1@'.sprintf(__('SMILEY UPLOAD ERROR: This file, at %s bytes, exceeds the maximum allowed file size as set in <em>php.ini</em>.', "sforum"), $filesize);
				break;

			case '2':
				$mess = '1@'.__('SMILEY UPLOAD ERROR: This file exceeds the maximum file size specified.', "sforum");
				break;

			case '3':
				$mess = '1@'.__('SMILEY UPLOAD ERROR: File was only partially uploaded. This could be the result of a connection problem', "sforum");
				break;

			case '4':
				$mess = '1@'.__('SMILEY UPLOAD ERROR: No Smiley File Uploaded', "sforum");
				break;
		}
		return $mess;

	}
}

?>