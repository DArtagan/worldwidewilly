<?

include "controller_project_local.php";

class controller_project_onserver extends controller_project_local
{
  // Inits the controller_project. $user is name of the active user
  function init( &$framework, &$model, $user, $project )
  {
    $this->framework = &$framework;
    $this->model = &$model;    
    $this->user = $user;
    $this->project = $project;
    
    $this->base_path = $this->model->get_project_setting($this->user, $this->project, "path");
  }

  // Creates a project
  function create_project( $path )
  {
    if( $this->model->is_project( $this->project ) )
      return "Project already exists";

    if( !is_dir($path) )
      return "Path is undefined";
  
    // Create project
    $this->model->add_project( $this->user, $this->project, "onserver" );
    $this->model->set_project_setting($this->user, $this->project, "path", $path);
    $this->base_path = $this->model->get_project_setting("path");
    
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
    if( !$this->model->is_project( $this->user, $this->project ) )
      return false;
    
    $this->model->delete_project( $this->user, $this->project);    
  }
}

$controller_project = new controller_project_onserver();
