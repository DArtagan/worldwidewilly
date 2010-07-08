<?

class framework
{
  var $actions;
  var $controller;
  var $config;
  var $model;

  //----------------//
  // Core functions //
  //----------------//
  
  // init this framework
  function framework()
  {
    $this->set_session_handling();
    $this->set_error_handling();
    $this->set_no_caching();
    $this->decode_url();
    $this->load_config();
    $this->load_model();
    $this->load_controller();
    $this->controller->go( $this->actions );
  }

  // Decodes the url and sets the $this->actions variable
  function decode_url()
  {
    $this->actions = explode("/", $_GET['action']);

    # Dirty fix: the rewrite of .htaccess adds the 'path info' twice => so remove it
    if( is_file("installed") )
      $this->actions = array_slice( $this->actions, 0, ((count($this->actions)-1)/2)+1); 
    # RewriteRule ^(.*)$ frontend/$1 does:
    #   /bespin/register/new/nikos => /bespin/frontend/register/new/nikos/new/nikos
    # explanation (rewriteLog):
    #   add path info postfix: /var/www/sites/bespin/register -> /var/www/sites/bespin/register/new/nikos
    #   strip per-dir prefix: /var/www/sites/bespin/register/new/nikos -> register/new/nikos
    #   applying pattern '^(.*)$' to uri 'register/new/nikos'
    #   rewrite 'register/new/nikos' -> 'frontend/register/new/nikos'
    #   add per-dir prefix: frontend/register/new/nikos -> /var/www/sites/bespin/frontend/register/new/nikos
    #   add path info postfix: /var/www/sites/bespin/frontend/register/new/nikos -> /var/www/sites/bespin/frontend/register/new/nikos/new/nikos
    # Todo: rewrite .htaccess
  }

  // Checks if the user is logged in
  function check_login()
  {
    if( !isset($_SESSION['username']) || $this->model->is_user( $_SESSION['username'] ) == false)
    {
      header("HTTP/1.1 401 Unauthorized");
      exit();
    }
  }

  // Load and parse the config.ini file
  function load_config()
  {
    $this->config = parse_ini_file("config.ini");
  }

  // Load the right model
  function load_model()
  {
    require $this->config['model'].".php";
    $this->model = $model;
  }

  // Load the controller
  function load_controller()
  {
    require "controller.php";
    $this->controller = new Controller( $this, $this->model );
  }

  //----------------//
  // Error handling // 
  //----------------// 

  // We want to do our own error handling
  function set_error_handling()
  {    
    error_reporting(E_ALL);
    set_error_handler(array($this,"on_error"));
  }

  // Give a nice 'Internal Server Error' on errors/bugs
  function on_error() 
  {
    // If error is surpressed with an @    
    if( error_reporting() == 0 )
      return false;
         
    header("HTTP/1.1 500 Internal Server Error");  

    // The normal error handler may take it over from here
    // and print the actual error message.
    return false;
  }

  //------------------//
  // Session handling // 
  //------------------//

  // Turns on session handling and output buffering
  function set_session_handling()
  {
    // Safety before everything. Session may not be saved in the url
    ini_set("session.use_only_cookies","1");

    // Start output buffering
    ob_start();
  
    // Start the sessions
    session_start();
  }

  // Don't cache the frameworks output, else you get funny scenario's where
  // you delete a file and still see it.
  function set_no_caching()
  {
    header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, private");
    header("Pragma: no-cache");
  }
   
}

