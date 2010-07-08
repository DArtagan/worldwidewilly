<?

class controller_project_local
{
  var $framework = false;
  var $model = false;  
  var $user = false;
  var $project = false;

  var $host = false;
  var $username = false;
  var $password = false;
  var $path = false;
  
  // Inits the controller_project. $user is name of the active user
  function init( &$framework, &$model, $user, $project )
  {
    $this->framework = &$framework;
    $this->model = &$model;    
    $this->user = $user;
    $this->project = $project;

    $this->host = $this->model->get_project_setting($this->user, $this->project, "host");
    $this->username = $this->model->get_project_setting($this->user, $this->project, "username");
    $this->password = $this->model->get_project_setting($this->user, $this->project, "password");
    $this->path = $this->model->get_project_setting($this->user, $this->project, "path");
  }

  // Creates a project
  function create_project()
  {
    if( $this->model->is_project( $this->user, $this->project ) )
      return "Project already exists"; 
    
    if( empty($_POST['host']) )
      return "Host is empty";
    
    if( substr($_POST['host'],0,6) != "ftp://" )
      return "Host is should start with 'ftp://'";

    if( empty($_POST['username']) )
      return "Username is empty";
    
    if( empty($_POST['password']) )
      return "Password is empty";
  
    if( empty($_POST['path']) )
      return "Path is empty"; 
    
    $ftp = ftp_connect(substr($_POST['host'],6));
    
    if( $ftp === FALSE )
      return "Could not connect to host";

    if( !@ftp_login($ftp, $_POST['username'], $_POST['password']) )
      return "Username and password are possible wrong";
    
    if(!@ftp_chdir($ftp, $_POST['path']) )
      return "Path doesn't exists"; 

    ftp_close($ftp);  

    // Create project
    $this->model->add_project( $this->user, $this->project, "ftp" );
    $this->model->set_project_setting($this->user, $this->project, "host", substr($_POST['host'],6));
    $this->model->set_project_setting($this->user, $this->project, "username", $_POST['username']);
    $this->model->set_project_setting($this->user, $this->project, "password", $_POST['password']);
    $this->model->set_project_setting($this->user, $this->project, "path", $_POST['path']);
    
    return "";
  }

  // Renames a currently existing project
  function rename_project( $new_name )
  {
    if( !$this->model->is_project($this->user, $this->project) )
      return false;
   
    if( $this->model->is_project($this->user, $new_name) )
      return false;

    $this->model->rename_project( $this->user, $this->project, $new_name);
    $this->project = $new_name;
    
    return true;
  }

  // Deletes a project
  function delete_project()
  {
    if( !$this->model->is_project( $this->project ) )
      return false;
    
    $this->model->delete_project( $this->user, $this->project );    
  }

  // Deletes a directory (recusive). $path is an array
  function delete_dir( $path )
  {
    $ftp = ftp_connect($this->host);
    
    if( $ftp === FALSE )
      return false;

    if( !ftp_login($ftp, $this->username, $this->password) )
      return false;
    
    if(!@ftp_chdir($ftp, $this->path) )
      return false;
    
    $path = implode("/",$path); 
  
    if(!@ftp_chdir($ftp, "/".$this->path."/".$path) )
      return false;
    
    ftp_rmdir_r($ftp, "/".$this->path."/".$path);

    ftp_close($ftp);

    return true;
  }

  // Return the files and directories of a path. $path is an array
  function get_files( $path )
  {
    $ftp = ftp_connect($this->host);
    
    if( $ftp === FALSE )
      return false;

    if( !ftp_login($ftp, $this->username, $this->password) )
      return false;
    
    $path = implode("/",$path); 
    
    if(!@ftp_chdir($ftp, "/".$this->path."/".$path) )
      return false;

    $files = ftp_nlist( $ftp, "/".$this->path."/".$path );
    for( $i=0; isset($files[$i]); $i++)
      if( $files[$i] == "." || $files[$i] == "..")
        unset($files[$i]);
      elseif( @ftp_chdir( $ftp, "/".$this->path."/".$path."/".$files[$i] ))
        $files[$i] .= "/"; 

    ftp_close($ftp);
    
    return array_values($files);
  }

  // Return the contents of a file. $path is an array
  function get_file_contents( $path )
  {
    $ftp = ftp_connect($this->host);
    
    if( $ftp === FALSE )
      return false;

    if( !ftp_login($ftp, $this->username, $this->password) )
      return false;
    
    if(!@ftp_chdir($ftp, "/".$this->path) )
      return false;
    
    $path = implode("/",$path);

    // Open a tempory file to write to
    $tmp = tmpfile();

    // Write the file contents into the tmp file
    $succes = @ftp_fget( $ftp, $tmp, "/".$this->path."/".$path, FTP_ASCII );

    // Put the pointer of read again to the front
    rewind($tmp);
     
    // Read the contents
    $file = "";
    while( !feof($tmp) ) 
      $file .= fread($tmp,2024);
    
    ftp_close($ftp);

    if( $succes === FALSE )
      return false;
    else
      return $file;
  }
  
  // Saves the contents of a file. $path is an array
  function save_file_contents( $path, $contents )
  {
    $ftp = ftp_connect($this->host);
    
    if( $ftp === FALSE )
      return false;

    if( !ftp_login($ftp, $this->username, $this->password) )
      return false;

    $file = array_pop($path); 

    // Search paths that already exist
    $tmp_path = "";
    while( !empty($path) && @ftp_chdir( $ftp, "/".$this->path."/".$tmp_path ) )
      $tmp_path .= array_shift($path)."/";
    
    // Create paths that don't exist
    while( !@ftp_chdir( $ftp, "/".$this->path."/".$tmp_path ) ){
      ftp_mkdir( $ftp, "/".$this->path."/".$tmp_path );
      $tmp_path .= array_shift($path)."/";
    }

    // Saving file
    if( !empty($file) )
    {
      // Puth the file contents into a tempory file      
      $tmp = tmpfile();
      fwrite($tmp, $contents);
      rewind($tmp);       
      
      // Upload that file
      ftp_fput($ftp, "/".$this->path."/".$tmp_path."/".$file, $tmp, FTP_ASCII);
    }
    
    ftp_close($ftp);

    return true;
  }

  // Removes a file. $path is an array
  function delete_file( $path )
  {
    $ftp = ftp_connect($this->host);
    
    if( $ftp === FALSE )
      return false;

    if( !ftp_login($ftp, $this->username, $this->password) )
      return false;
    
    if(!@ftp_chdir($ftp, "/".$this->path) )
      return false;
    
    $file = array_pop($path);
    $path = implode("/",$path);

    if( !empty($file) )
      $succes = @ftp_delete( $ftp, "/".$this->path."/".$path."/".$file);
    else
      $succes = ftp_rmdir_r($ftp, "/".$this->path."/".$path);
    
    ftp_close($ftp); 

    return $succes;
  }
}

$controller_project = new controller_project_local();
