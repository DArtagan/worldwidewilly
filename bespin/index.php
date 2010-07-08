<?

if( version_compare(PHP_VERSION, '5.0.0', '<') )
  include 'php4.php';
include 'php_recursive.php';

include "framework.php";
new framework();
