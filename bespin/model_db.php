<?

class model_db
{
  var $users = false; 
  
  //-------------------//
  // User manipulation //
  //-------------------//  

  // Get an array of all usernames
  function get_users()
  {
    //Unimplemented
  } 

  // Return true is the user exists
  function is_user( $user )
  {
    //Unimplemented
  }  

  // Get info of a user
  function get_user_info( $user, $key )
  {
    //Unimplemented
  }

  // Change the info of a user
  function change_user_info( $user, $key, $config )
  {
    //Unimplemented
  }

  // Adds an user
  function add_user( $username, $email, $password )
  {
    //Unimplemented
  }

  // returns true if username and password correspond
  function login_user( $user, $password )
  {
    //Unimplemented
  }

  // Get info of a user
  function get_user_settings( $user )
  {
    //Unimplemented
  }

  // Get info of a user
  function get_user_setting( $user, $key = false )
  {
    //Unimplemented
  }

  // Change the info of a user. If $value is false, it will remove the setting
  function set_user_setting( $user, $key, $value )
  {
    //Unimplemented
  }

  //----------------------//
  // Project manipulation //
  //----------------------//

  // Return an array of all projects
  // Keys are the projects name and the value is the project type. 
  //  array( "example" => "local",
  //         "example2" => "ftp" );
  function get_projects( $user )
  {
    //Unimplemented
  }

  // Returns true when the project exists
  function is_project( $user )
  {
    //Unimplemented
  }

  // Returns true when the project exists
  function get_project_type( $user, $project )
  {
    //Unimplemented
  }

  // Creates a project. 
  // BE CAREFULL!! You must also call the create_project function of the 'controller_project'
  function add_project( $user, $project, $type="local")
  {
      //Unimplemented
  }

  // Deletes a project. 
  // BE CAREFULL!! You must also call the delete function of the 'controller_project'
  function delete_project( $user, $project )
  {
    //Unimplemented
  }

  // Renames a project to a new name. 
  // BE CAREFULL!! You must also call the rename function of the 'controller_project'
  function rename_project( $user, $project, $new_name )
  {
    //Unimplemented
  }

  //------------------------//
  // Openfiles manipulation //
  //------------------------//

  // Return the list of opened files of a user
  function list_opened( $user )
  {
    //Unimplemented
  }

  // Will mark a file opened. $path must contain the full path including the filename
  function mark_opened( $user, $project, $path )
  {
    //Unimplemented
  }

  // Unmark a file that has been opened before
  function unmark_opened( $user, $project, $path )
  {
    //Unimplemented
  }
}

$model = new model_db();
