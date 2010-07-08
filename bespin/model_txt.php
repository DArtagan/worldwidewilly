<?

class model_txt
{
  var $users = false; 
  var $install_key = false;
  //-------------------//
  // User manipulation //
  //-------------------//  

  // Get an array of all usernames
  function get_users()
  {
    $this->load_config();
    return array_keys( $this->users );
  } 

  // Return true is the user exists
  function is_user( $user )
  {
    $this->load_config();
    return isset( $this->users[$user] );
  }  

  // Get info of a user
  function get_user_info( $user, $key )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Key don't exist
    if( !isset($this->users[ $user ][ $key ]) )
      return false;

    return $this->users[ $user ][ $key ];
  }

  // Change the info of a user
  function change_user_info( $user, $key, $config )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Key don't exist
    if( !isset($this->users[ $user ][ $key ]) )
      return false;

    $this->users[ $user ][ $key ] = $config ;

    $this->save_config();
    return true;
  }

  // Adds an user
  function add_user( $username, $email, $password )
  {
    $this->load_config();
    
    // Don't overwrite existing user
    if( isset($this->users[ $username ]) )
      return false;

    $user = array("username" => $username,
                  "email" => $email,
                  "password" => md5($password),
                  "openfiles" => array(),
                  "projects" => array(),
                  );
    $user['settings'] = array( "_username" => $username,
                               "autocomplete" => "off",
                               "collaborate" => "off", 
                               "fontsize" => "10", 
                               "language" => "auto", 
                               "preview"=> "window",
                               "tabarrow" => "on",
                               "tabmode"=> "off",
                               "tabsize" => "2", 
                               "smartmove" => "on",
                               "strictlines" => "on",
                               "syntax" => "auto",
                               "syntaxengine" => "simple",
    );
    
    $this->users[ $username ] = $user;

    $this->save_config();
    return true;
  }

  // returns true if username and password correspond
  function login_user( $user, $password )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;
    
    return $this->users[ $user ]['password'] == md5($password);
  }

  // Get info of a user
  function get_user_settings( $user )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    return $this->users[ $user ]['settings'];
  }

  // Get info of a user
  function get_user_setting( $user, $key = false )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Key don't exist
    if( !isset($this->users[ $user ]['settings'][ $key ]) )
      return false;

    return $this->users[ $user ]['settings'][ $key ];
  }

  // Change the info of a user. If $value is false, it will remove the setting
  function set_user_setting( $user, $key, $value )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    if( $value === FALSE )
      unset( $this->users[ $user ]['settings'][ $key ] );
    else
      $this->users[ $user ]['settings'][ $key ] = $value ;

    $this->save_config();
    return true;
  }

  //----------------------//
  // Project manipulation //
  //----------------------//

  // Return an array of all projects
  function get_projects( $user )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    return array_keys( $this->users[$user]['projects'] );
  }

  // Returns true when the project exists
  function is_project( $user, $project )
  {
    $this->load_config();
    
    if( !isset($this->users[ $user ]['projects'][ $project ]) )
      return false;
    else
      return true;
  }

  // Returns true when the project exists
  function get_project_type( $user, $project )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exists
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

    return $this->users[ $user ]['projects'][$project]['type'];
  }

  // Get info of a project
  function get_project_setting( $user, $project, $key )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exists
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

    // Setting don't exists
    if( !isset($this->users[ $user ]['projects'][$project]['settings'][$key]) )
      return false;

    return $this->users[ $user ]['projects'][$project]['settings'][$key];
  }

  // Change the info of a project. If $value is false, it will remove the setting
  function set_project_setting( $user, $project, $key, $value)
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exists
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

    // Setting don't exists
    if( $value === false && !isset($this->users[ $user ]['projects'][$project]['settings'][$key]) )
      return false;

    if( $value === false )
      unset( $this->users[ $user ]['projects'][$project]['settings'][$key] );
    else
      $this->users[ $user ]['projects'][$project]['settings'][$key] = $value;

    $this->save_config();
    return true;
  }

  // Creates a project. 
  // BE CAREFULL!! You must also call the create_project function of the 'controller_project'
  function add_project( $user, $project, $type="local")
  {
      $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project exists
    if( isset($this->users[ $user ]['projects'][$project]) )
      return false;

    // Creating.
    $this->users[ $user ]['projects'][$project] = array( "type" => $type, "settings" => array() );
    
    $this->save_config();
    return true;
  }

  // Deletes a project. 
  // BE CAREFULL!! You must also call the delete function of the 'controller_project'
  function delete_project( $user, $project )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exist
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

    // Deleting...
    unset( $this->users[ $user ]['projects'][$project] );
    
    $this->save_config();
    return true;
  }

  // Renames a project to a new name. 
  // BE CAREFULL!! You must also call the rename function of the 'controller_project'
  function rename_project( $user, $project, $new_name )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exist
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

     // Project with name: $newName already exist
    if( isset($this->users[ $user ]['projects'][$new_name]) )
      return false;

    // Renaming
    $this->users[ $user ]['projects'][$new_name] = $this->users[ $user ]['projects'][$project];
    unset( $this->users[ $user ]['projects'][$project] );
    
    $this->save_config();
    return true;
  }

  //------------------------//
  // Openfiles manipulation //
  //------------------------//

  // Return the list of opened files of a user
  function list_opened( $user )
  {
    $this->load_config();
    return $this->users[ $user ]['openfiles'];
  }

  // Will mark a file opened. $path must contain the full path including the filename
  function mark_opened( $user, $project, $path )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exist
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

    // Mark opened
    $this->users[ $user ]['openfiles'][ $project ][ $path ] = array("mode" => "rw") ;

    $this->save_config();
    return true;
  }

  // Unmark a file that has been opened before
  function unmark_opened( $user, $project, $path )
  {
    $this->load_config();
    
    // User don't exist
    if( !isset($this->users[ $user ]) )
      return false;

    // Project don't exist
    if( !isset($this->users[ $user ]['projects'][$project]) )
      return false;

    // File isn't open
    if( !isset($this->users[ $user ]['openfiles'][$project][$path]) )
      return false;

    // Remove
    unset( $this->users[ $user ]['openfiles'][$project][$path] );
    
    $this->save_config();
    return true;
  }

  //--------------------------//
  // Install key manipulation //
  //--------------------------//

  function generate_install_key()
  {
    $this->load_config();
    $this->install_key = md5( time().$_SERVER['REMOTE_ADDR'] );
    $this->save_config();
    
    return $this->install_key;
  }

  function get_install_key()
  {
    $this->load_config();
    
    return $this->install_key;
  }

  function check_install_key( $key )
  {
    $this->load_config();
    sleep(1); 
    
    return $key == $this->install_key;
  }

  //-------------------//
  // Load/Store config //
  //-------------------//

  // Load the config
  function load_config()
  {
    // Don't load the config twice
    if( $this->users !== false )
      return;

    if( is_file("users.conf") )
      include "users.conf";
    else
    {
      $users = array();
      $install_key = false;
    }

    $this->users = $users;
    $this->install_key = $install_key;
  }

  // Saves the config
  function save_config()
  {
    if( is_file('installed') )  
      $file = 'users.conf';
    else
      $file = 'backend/php/users.conf';
      
    file_put_contents($file, '<?$users='.var_export($this->users,true).';$install_key='.var_export($this->install_key,true).';' );
  }
}

$model = new model_txt();
