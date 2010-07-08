<?

class controller
{
  var $framework = false;
  var $model = false; 
  var $actions_without_login = array("register","install");  
  
  // init this controller
  function controller( &$framework, &$model )
  {
    $this->framework = &$framework;
    $this->model = &$model;
  }  

  // Start this controller. The controller will find the right function correspondant 
  // with the request.
  function go( $actions )
  {
    // Check if the specified action need a logged in user.    
    if( !in_array( $actions[0], $this->actions_without_login) )
      $this->framework->check_login();    

    $args = array();
    
    // Search for the existing controller method
    // for example:
    //    action = array("register","login","me")
    // Will search for methods:
    //    register_login_me( $args );
    //    register_login( $args );
    // And execute register_login() because that's an existing method
    while( !empty($actions) && !method_exists($this, implode("_",$actions) ) )
      array_unshift( $args, array_pop( $actions ));
    
    // The controller method isn't found
    if( empty($actions) )
      trigger_error("Didn't find controller: ".implode("/",$args), E_USER_ERROR);

    // Calling the controller method
    $actions = implode("_", $actions);
    $this->$actions( $args );
  }

  // When something goes wrong, you can stop and print some error code
  function respond_with_error( $error, $http_code = "400 Bad Request")
  {
    header("HTTP/1.1 ".$http_code);
    exit( $error );
  }

  // Stop and respond some data. The data will automatically be json encoded
  function respond( $respond = null )
  {
    if( $respond === null )
      exit("{}");
    else    
      exit( json_encode($respond) );
  }

  // Stop and respond some plain data
  function plain_respond( $respond )
  {
    exit( $respond );
  }

  //--------------------//
  // Project controller //
  //--------------------//

  // Loads the project controller
  function load_project_controller( $user, $project, $type = "local" )
  {
    // If the project exists, get the real project type    
    if( $this->model->is_project( $user, $project ) )
      $type = $this->model->get_project_type( $user, $project );

    if( !isset($this->framework->config['project_'.$type]) || $this->framework->config['project_'.$type] == "false")
      $this->respond_with_error("Project type '".$type."' isn't supported");
    
    include "controller_project_".$type.".php";
    $controller_project->init( $this->framework, $this->model, $user, $project );
    return $controller_project;
  }

  //--------------------//
  // REGISTER functions // 
  //--------------------//
   
  function register_new( $args )
  {
    if( !isset( $args[0] ) )
      $this->respond_with_error("Username required");
    if( !isset( $_POST['email'] ) )
      $this->respond_with_error("Email required");
    if( !isset( $_POST['password'] ) )
      $this->respond_with_error("Password required");

    if( empty( $args[0] ) )
      $this->respond_with_error("Username is empty");
    if( empty( $_POST['email'] ) )
      $this->respond_with_error("Email is empty");
    if( empty( $_POST['password'] ) )
      $this->respond_with_error("Password is empty");

    $succes = $this->model->add_user( $args[0], $_POST['email'], $_POST['password']);
    
    if( !$succes )
      $this->respond_with_error("User already exists");
    
    // Logs in the user
    $_SESSION['username'] = $args[0];

    // Create BespinSettings project
    $ctrl = $this->load_project_controller($_SESSION['username'], "BespinSettings" );
    $ctrl->create_project();

    // Populate the BespinSettings project
    function populate( $dir, &$ctrl )
    {
      $ctrl->save_file_contents( explode("/",$dir."/"), "");
          
      $dirs = scandir("bespin_settings_template/".$dir);
      for( $i=0; isset($dirs[$i]); $i++ )
        if( $dirs[$i] != "." && $dirs[$i] != ".." )
        {
          // Saving the file          
          if( is_file("bespin_settings_template/".$dir."/".$dirs[$i]) )
            $ctrl->save_file_contents( explode("/",$dir."/".$dirs[$i]), file_get_contents("bespin_settings_template/".$dir."/".$dirs[$i]) );
          // Do the dir recursive          
          else
            populate( $dir.$dirs[$i]."/", $ctrl );
        }
    }
    populate("", $ctrl); 
    
    $this->respond();
  }

  function register_login( $args )
  {
    if( empty($args[0]) )
      $this->respond_with_error("No login name");
    if( empty($_POST['password']) )
      $this->respond_with_error("No password");

    if( !$this->model->login_user( $args[0], $_POST['password'] ) )
      $this->respond_with_error("Invalid login", "401 Not Authorized");
    
    $_SESSION['username'] = $args[0];
    $this->respond();
  }

  function register_logout( $args )
  {
    unset($_SESSION['username']);
    $this->plain_respond("Logged out");
  }

  function register_userinfo( $args )
  {
    // Need to check manually, because 'register' is defined in $this->actions_without_login  
    $this->framework->check_login();   
        
    $this->respond( array( "username"=> $_SESSION['username'],
                           "amountUsed" => 0,   // TODO: calculate the real amount used
                           "quota" => 0         // TODO: define a quota
                  )      );
  }

  //-------------------//
  // SETTING functions // 
  //-------------------//

  function settings( $args )
  {
    switch( $_SERVER['REQUEST_METHOD'] )    
    { 
      case "GET":

        if( !empty($args[0]) )
        {
          $value = $this->model->get_user_setting( $_SESSION['username'], $args[0] );          
          if( $value !== FALSE ) 
            $this->respond( $value ); 
          else
            $this->respond_with_error("Setting not found", "404 Not Found");
        }  
        else
          $this->respond( $this->model->get_user_settings( $_SESSION['username'] ) );
        
        break;
      case "POST":
        
        foreach( $_POST as $name => $value )
          $this->model->set_user_setting( $_SESSION['username'], $name, $value ); 

        $this->respond();
        break;
      case "DELETE":
        $success = $this->model->set_user_setting( $_SESSION['username'], $args[0], false );

        if( !$success )
          $this->respond_with_error();
        else
          $this->respond();        
        break;
    }
  }

  //----------------//
  // FILE functions // 
  //----------------//

  function file_listopen( $args )
  {
    $this->respond( $this->model->list_opened( $_SESSION['username'] ) );
  }
  
  function file_at( $args )
  {
    $project = array_shift($args);
    
    // Put/Delete project
    if( empty($args[0]) )
    {
      switch( $_SERVER['REQUEST_METHOD'] )    
      { 
        case "GET":
          $this->respond_with_error("404");
          break;

        // Create project
        case "PUT":
          $ctrl = $this->load_project_controller( $_SESSION['username'], $project, "local" );
          $error = $ctrl->create_project();
          
          if( !empty( $error ) )
            $this->respond_with_error($error);
          
          $this->respond();
          break;

        // removes project
        case "DELETE":
          $ctrl = $this->load_project_controller( $_SESSION['username'], $project );
          $succes = $ctrl->delete_project();

          if( $succes === FALSE )
            $this->respond_with_error("Project don't exist");
          
          $this->respond();
          break;
      }

      
    }
    // Put/Get/Remove files from project
    else
    {  
      if( !$this->model->is_project( $_SESSION['username'], $project ) )
        $this->respond_with_error("Project don't exists", "404 Not Found");
       
      $ctrl = $this->load_project_controller( $_SESSION['username'], $project );
      switch( $_SERVER['REQUEST_METHOD'] )    
      { 
        // Get file contents        
        case "GET":
          $file = $ctrl->get_file_contents( $args );
          if( $file === FALSE )
            $this->respond_with_error("File don't exists", "404 Not Found");
                    
          $this->model->mark_opened( $_SESSION['username'], $project, implode("/",$args) );
          $this->plain_respond($file);
          break;
      
        // Save file contents
        case "PUT":
          $file = $ctrl->save_file_contents( $args, file_get_contents("php://input") );
          if( $file === FALSE )
            $this->respond_with_error("Path don't exists");
          else
            $this->plain_respond("");
          break;

        // Remove file
        case "DELETE":
          $file = $ctrl->delete_file( $args );
          if( $file === FALSE )
            $this->respond_with_error("File don't exists", "404 Not Found");
          else
            $this->respond();
          break;
      }
    }
  }

  function file_list( $args )
  {
    //  Show projects    
    if( empty($args[0]))
    {
      $projects = $this->model->get_projects( $_SESSION['username'] );      
      
      $response = array();
      for($i=0; isset($projects[$i]); $i++)
        $response[] = array("name"=>$projects[$i]."/");
      
      $this->respond( $response );
    }
    // Show list of files inside a project
    else
    {
      $project = array_shift($args);
        
      if( !$this->model->is_project( $_SESSION['username'], $project ) )
        $this->respond_with_error("Project don't exists", "404 Not Found");
       
      $ctrl = $this->load_project_controller( $_SESSION['username'], $project );
      $files = $ctrl->get_files( $args );
      
      if( $files === FALSE )
        $this->respond_with_error("Path don't exists", "404 Not Found");

      $return = array();
      for($i=0; isset($files[$i]); $i++)
        $return[] = array("name"=>$files[$i]);

      $this->respond( $return );
    }
  }

  function file_close( $path )
  {
    $project = array_shift( $path );

    $success = $this->model->unmark_opened( $_SESSION['username'], $project, implode("/",$path) );
    
    if( !$success ) 
      $this->respond_with_error("Can't close an unopened file");
    else
      $this->respond();
  }

  //-------------------//
  // PROJECT functions // 
  //-------------------//

  function create_project( $args )
  {
    $project = array_shift($args);
    
    if( $args[0] == "local" )
    {
      $ctrl = $this->load_project_controller( $_SESSION['username'], $project, "local" );
      $error = $ctrl->create_project();
    }
    elseif( $args[0] == "ftp" )
    {
      $ctrl = $this->load_project_controller( $_SESSION['username'], $project, "ftp");
      $error = $ctrl->create_project();
    }
    elseif( $args[0] == "onserver" )
    {
      $ctrl = $this->load_project_controller( $_SESSION['username'], $project, "onserver");
      $error = $ctrl->create_project();
    }
    else
    {
      $this->respond_with_error("Didn't find a controller for your advanced project");
    }

    if( !empty( $error ) )
      $this->respond_with_error($error);
    
    $this->respond();
  }

  //---------------------//
  // MESSAGING functions // 
  //---------------------//

  function messages( $path )
  {
    // Unsupported    
    $this->respond( array() );
  }

 //-------------------//
  // INSTALL functions // 
  //-------------------//

  function install_install( $args )
  {
    if( !is_writable(".") )
      $this->respond_with_error("Please chmod all files and the parent directory to 0777");

    if( is_writable_r(".") !== TRUE )
      $this->respond_with_error("Please chmod all files and the parent directory to 0777 (including: ".is_writable_r(".").")");

    if( is_file("installed") )
      $this->respond_with_error("Bespin PHP is already installed");

    $files = scandir("./");
    for($i=0; isset($files[$i]); $i++)
      if( $files[$i] != "." && $files[$i] != ".." && !is_writable($files[$i]) )
        $this->respond_with_error("Please chmod all files to 0777 (including: ".$files[$i].")");

    // Mark installation as installed
    file_put_contents("installed","1");
    
    // Creating default folders
    mkdir("backend");
    chmod("backend",0777);
    mkdir("backend/php");
    chmod("backend/php",0777);
    mkdir("frontend");
    chmod("frontend",0777);
    
    // Moving files to backend subdirectory
    $files = scandir("./");
    for($i=0; isset($files[$i]); $i++)
      if( $files[$i] != "." && $files[$i] != ".." && $files[$i] != "htaccess" && $files[$i] != "backend" && $files[$i] != "frontend" )
        rename($files[$i], "backend/php/".$files[$i]);

    // Putting .htaccess file in place
    rename("htaccess", ".htaccess");
    
    // Generate install key
    $key = $this->model->generate_install_key();

    // Install the frontend
    $this->install_reinstall( array("frontend", $key) );
    
    $this->plain_respond("installed. Install key: ".$key);
  }

  function install_reinstall( $args )
  {
    if( !isset($args[1]) || !$this->model->check_install_key($args[1]) )    
      $this->respond_with_error("Please provide a correct install key");
    
    if( !is_writable("./") )
      $this->respond_with_error("Please chmod all files and the parent directory to 0777");

    if( is_writable_r("./") !== TRUE )
      $this->respond_with_error("Please chmod all files and the parent directory to 0777 (including: ".is_writable_r("./").")");

    if( !is_file("installed") && !is_file("backend/php/installed") )
      $this->respond_with_error("Bespin PHP isn't installed");

    // Determine where to place everything
    if( is_file("installed") )
      $path = "../../frontend";
    else
      $path = "frontend";
      
    // Location to the update server
    $server = $this->framework->config['update_server'];
    
    // Download and parse release config file
    file_put_contents("config.tmp", file_get_contents( $server."release_".$this->framework->config['version']) );
    $config = parse_ini_file("config.tmp");
    unlink("config.tmp");

    // Reinstall the frontend
    if( $args[0] == "frontend" || $args[0] == "all")
    {   
      //------ FRONTEND -------//

      // Open a file to write to
      $fp = fopen("frontend.zip","w");

      // Download the frontend
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $config['frontend_location']);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_FILE, $fp);
      curl_exec($curl);
      curl_close($curl);

      // Close the file
      fclose($fp);
      
      // Remove previous installed fronted
      rmdir_r($path);
      mkdir($path);
      chmod($path,0777);
  
      // Read the zip file
      $zip = zip_open("frontend.zip");
      $match = "^bespin-[^/]*/frontend/";
      while( $entry = zip_read($zip) )
      {
        $name = zip_entry_name($entry);
        
        // Only unpack the frontend files
        if( ereg($match, $name) )
        {
          $dir = explode("/",$name);
          $file = array_pop( $dir );
          
          // Remove bespin-****** and frontend directory
          array_shift($dir);
          array_shift($dir);

          // Create dirpath to file    
          $tmpDir = "";      
          while( !empty($dir) )
          {
              $tmpDir .= "/".array_shift($dir);
              if( !is_dir($path."/".$tmpDir) ) 
                mkdir($path."/".$tmpDir);
          }
        
          // Create file
          if( zip_entry_open ( $zip, $entry ) )
          {
            $fp = fopen( $path."/".$tmpDir."/".$file, "w");     
            while( $input = zip_entry_read( $entry) )
              fwrite($fp, $input);
          }
        }
      }

      // Delete the tmp files
      unlink("frontend.zip");

      //------ FRONTEND PATCH -------//

      // Include diff functionality
      if( is_file("installed") ) 
        require_once "diff.php";
      else
        require_once "backend/php/diff.php";
      
      $patcher = new PhpPatcher($path."/");

      // Get diff
      $diff = file_get_contents( $config['frontend_diff'] );
  
      // Apply diff
      $patcher->Merge($diff); 
      $patcher->ApplyPatch();
      
      //------ DOJO -------//

      // Open a file to write to
      $fp = fopen("dojo.zip","w");

      // Download the frontend
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $config['dojo_location']);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_FILE, $fp);
      curl_exec($curl);
      curl_close($curl);

      // Close the file
      fclose($fp);

      // Unpack the zip
      $zip = zip_open("dojo.zip");
      while( $entry = zip_read($zip) )
      {
        $name = zip_entry_name($entry);
        $dir = explode("/",$name);
        $file = array_pop( $dir );
          
        // Remove dojo-release-****** directory
        array_shift($dir);

        // Create dirpath to file    
        $tmpDir = "";      
        while( !empty($dir) )
        {
            $tmpDir .= "/".array_shift($dir);
            if( !is_dir($path."/js".$tmpDir) ) 
              mkdir($path."/js".$tmpDir);
        }
      
        // Create file
        if( zip_entry_open ( $zip, $entry ) && !is_dir($path."/js".$tmpDir."/".$file) )
        {
          $fp = fopen( $path."/js".$tmpDir."/".$file, "w");     
          while( $input = zip_entry_read( $entry) )
            fwrite($fp, $input);
        }
      }

      // Delete the tmp files
      unlink("dojo.zip");
    }
    
    // Reinstall the backend
    if( $args[0] == "backend" || $args[0] == "all")
    {
      // Open a file to write to
      $fp = fopen("backend.zip","w");

      // Download the backend
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $config['backend_location']);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_FILE, $fp);
      curl_exec($curl);
      curl_close($curl);

      // Close the file
      fclose($fp);

      // Path to tmp new backend
      $path = "../../backend/php2";
      mkdir($path);
      chmod($path,0777);

      // Extract zip
      $zip = zip_open("backend.zip");
      while( $entry = zip_read($zip) )
      {
        $name = zip_entry_name($entry);
        $dir = explode("/",$name);
        $file = array_pop( $dir );

        // Create dirpath to file    
        $tmpDir = "";      
        while( !empty($dir) )
        {
          $tmpDir .= "/".array_shift($dir);
          if( !is_dir($path.$tmpDir) ) 
          {
            mkdir($path.$tmpDir);
            chmod($path.$tmpDir,0777);
          }
        }
      
        // Create file
        if( zip_entry_open ( $zip, $entry ) && !is_dir($path.$tmpDir."/".$file) )
        {
          $fp = fopen( $path.$tmpDir."/".$file, "w");     
          while( $input = zip_entry_read( $entry) )
            fwrite($fp, $input);
          fclose($fp);
          chmod($path.$tmpDir."/".$file,0777);
        }
      }

      // Delete the tmp files
      unlink("backend.zip");

      // Copy configuration
      if( is_file("../php/users.conf") )
        rename("../php/users.conf","../php2/users.conf");
      if( is_dir("../php/localprojects") )
        rename("../php/localprojects","../php2/localprojects");
      if( is_file("../php/installed") )
        rename("../php/installed","../php2/installed");
      rename("../php/config.ini","../php2/config.ini");

      // Take new backend in use
      rmdir_r("../php");
      rename("../php2","../php");
    }
  }

  function install_clean( $args )
  {
    if( !isset($args[0]) || !$this->model->check_install_key($args[0]) )    
      $this->respond_with_error("Please provide a correct install key");
    
    if( !is_writable("./") )
      $this->respond_with_error("Please chmod all files and the parent directory to 0777");

    if( is_writable_r("./") !== TRUE )
      $this->respond_with_error("Please chmod all files and the parent directory to 0777 (including: ".is_writable_r("./").")");

    if( !is_file("installed") )
      $this->respond_with_error("Bespin PHP isn't installed yet");

    // Mark installation as 'not installed'
    unlink("installed");

    // Move files back to original location before installation
    $files = scandir("./");
    for($i=0; isset($files[$i]); $i++)
      if( $files[$i] != "." && $files[$i] != ".." && $files[$i] != "users.conf" && $files[$i] != "localprojects" )
        rename($files[$i], "../../".$files[$i]);

    // Remove all contents of backend and frontend
    rmdir_r("../../backend");
    rmdir_r("../../frontend");

    // Undo .htaccess
    rename("../../.htaccess","../../htaccess");

    $this->plain_respond("cleaned");
  }
}

