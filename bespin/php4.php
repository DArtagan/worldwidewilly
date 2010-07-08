<?
if(!function_exists('scandir'))
{
  function scandir($dir, $sortorder = 0)
  {
    if(is_dir($dir))
    {
      $dirlist = opendir($dir);
      $files = array();
      while( ($file = readdir($dirlist)) !== false)
      {
        if(!is_dir($file))
        {
          $files[] = $file;
        }
      }

      ($sortorder == 0) ? sort($files) : rsort($files);

      return $files;
    }
    else
    {
      return FALSE;
      break;
    }
  }
}
if(!function_exists('file_put_contents'))
{
  define('FILE_APPEND', 1);
  function file_put_contents($n, $d, $flag = false) 
  {
    $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
    $f = fopen($n, $mode);
    if ($f === false) 
    {
      return 0;
    } 
    else 
    {
      if (is_array($d)) 
        $d = implode($d);
      
      $bytes_written = fwrite($f, $d);
      fclose($f);
      return $bytes_written;
    }
  }
}
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) 
      return 'null';

    if ($a === false) 
      return 'false';

    if ($a === true) 
      return 'true';

    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}
