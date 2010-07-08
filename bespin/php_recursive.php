<?

// Check recursively if a directory and its files are writable.
// Return TRUE when all files and directories are writable
// Return the file or directory of the first occurence that isn't writable
function is_writable_r( $dir )
{
  if( !is_dir( $dir ) && is_writable($dir) ) 
    return $dir;

  $files = scandir($dir);
  for( $i=0; isset($files[$i]); $i++)
    if( $files[$i] != "." && $files[$i] != "..")
    {
      if( is_dir( $dir."/".$files[$i] ) )
      {
        $ret = is_writable_r( $dir."/".$files[$i] );
        if( $ret !== TRUE )
          return $ret;
      }
      elseif( !is_writable( $dir."/".$files[$i] ) )
      {
        return $dir."/".$files[$i];
      }
    }

  return TRUE;
}

// Removes a directory recursive, namely the directory and all it's contents
function rmdir_r( $dir )
{
  if( !is_dir( $dir ) ) 
    return false;

  $files = scandir($dir);
  for( $i=0; isset($files[$i]); $i++)
    if( $files[$i] != "." && $files[$i] != "..")
    {
      if( is_dir( $dir."/".$files[$i] ) )
        rmdir_r( $dir."/".$files[$i] );
      else
        unlink( $dir."/".$files[$i] );
    }
  rmdir( $dir );
}

// Removes a ftp directory recursive, namely the directory and all it's contents
function ftp_rmdir_r( $ftp, $path )
{
  $files = ftp_nlist( $ftp, $path );
  for( $i=0; isset($files[$i]); $i++)
    if( $files[$i] != "." && $files[$i] != "..")
    {
      if( @ftp_chdir( $ftp, $path."/".$files[$i] ) )
        $succes = ftp_rmdir_r( $ftp, $path."/".$files[$i] );
      else
        $succes = ftp_delete( $ftp, $path."/".$files[$i] );

      if( $succes == false)
        return false;
    }

  return @ftp_rmdir($ftp, $path);
}
