<?

class controller_project_local
{
  var $framework = false;
  var $model = false;  
  var $user = false;
  var $project = false;

  var $base_path = false;
  
  // Inits the controller_project. $user is name of the active user
  function init( &$framework, &$model, $user, $project )
  {
    $this->framework = &$framework;
    $this->model = &$model;    
    $this->user = $user;
    $this->project = $project;

    $this->base_path = "localprojects/".$this->user."/".$this->project."/";
  }

  // Creates a project
  function create_project()
  {
    if( $this->model->is_project( $this->user, $this->project ) )
      return "Project already exists"; 
    
    if( is_dir( $this->base_path ) ) 
      return "Project already exists (2)";
  
    // Create project
    $this->model->add_project( $this->user, $this->project );

    if( !is_dir("localprojects") )
      mkdir("localprojects");

    if( !is_dir("localprojects/".$this->user) )
      mkdir("localprojects/".$this->user);
    
    mkdir( $this->base_path );
    return "";
  }

  // Renames a currently existing project
  function rename_project( $new_name )
  {
    $new_path = "localprojects/".$this->user."/".$new_name;
    
    if( !is_dir( $this->base_path ) ) 
      return false;
  
    if( is_dir( $new_path ) ) 
      return false;

    if( !$this->model->is_project($this->user, $this->project) )
      return false;
   
    if( $this->model->is_project($this->user, $new_name) )
      return false;

    rename( $this->base_path, $new_path );
    $this->model->rename_project( $this->user, $this->project, $new_name);

    $this->project = $new_name;
    $this->base_path = $new_path;
    
    return true;
  }

  // Deletes a project
  function delete_project()
  {
    if( !$this->model->is_project( $this->user, $this->project ) )
      return false; 
    
    if( !is_dir( $this->base_path ) ) 
      return false;
   
    $succes = $this->delete_dir( array() );

    if( !$succes )
      return false;

    $this->model->delete_project( $this->user, $this->project);    
  }

  // Deletes a directory (recusive). $path is an array
  function delete_dir( $path )
  {
    $path = implode("/",$path);   
    
    if( !is_dir( $this->base_path.$path ) ) 
      return false;
    
    rmdir_r( $this->base_path.$path);

    return true;
  }

  // Return the files and directories of a path. $path is an array
  function get_files( $path )
  {
    $path = implode("/",$path);   
    
    if( !is_dir( $this->base_path.$path ) )
      return false;

    $files = scandir( $this->base_path.$path );
    for( $i=0; isset($files[$i]); $i++)
      if( $files[$i] == "." || $files[$i] == "..")
        unset($files[$i]);
      elseif( is_dir( $this->base_path.$path."/".$files[$i] ))
        $files[$i] .= "/"; 

    return array_values($files);
  }

  // Return the contents of a file. $path is an array
  function get_file_contents( $path )
  {
    $path = implode("/",$path);   
     
    if( !is_file( $this->base_path.$path ) )
      return false;

    return file_get_contents( $this->base_path.$path );
  }
  
  // Saves the contents of a file. $path is an array
  function save_file_contents( $path, $contents )
  {
    $file = array_pop($path); 

    // Search paths that already exist
    $tmp_path = "";
    while( !empty($path) && is_dir( $this->base_path.$tmp_path ) )
      $tmp_path .= array_shift($path)."/";
    
    // Create paths that don't exist
    while( !is_dir( $this->base_path.$tmp_path ) ){
      mkdir( $this->base_path.$tmp_path );
      $tmp_path .= array_shift($path)."/";
    }

    // Saving file
    if( !empty($file) )
      file_put_contents( $this->base_path.$tmp_path.$file, $contents );
    
    return true;
  }

  // Removes a file. $path is an array
  function delete_file( $path )
  {
    $file = array_pop( $file );    
    $path = implode("/",$path);   
    
    if( !empty($file) && !is_file( $this->base_path.$path."/".$file) )
      return false;
    
    if( empty($file) && !is_dir($this->base_path.$path) ) 
      return false;

    if( !empty($file) )
      unlink( $this->base_path.$path."/".$file);
    else
      rmdir_r( $this->base_path.$path);

    
    return true;
  }
}

$controller_project = new controller_project_local();
