<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = "welcome";
$route['404_override']       = '';

// Include routes.php all modules
$modules = config_item('modules_locations');

foreach ($modules as $routing) {
    if (is_dir($routing)) {
        if ($handle = opendir($routing)) {
            while (FALSE !== ($module = readdir($handle))) {
                if ((substr($module, 0, 1) != '.') AND (substr($module, 0, 2) != '..')) {
                    if (is_dir($dir = $routing . $module . '/config/')) {
                        if ($handle2 = opendir($dir)) {
                            while (FALSE !== ($file = readdir($handle2))) {
                                if ((substr($file, 0, 1) != '.') AND (substr($file, 0, 2) != '..') AND (substr($file, -4) == EXT)) {
                                    if (file_exists($is_file = $dir . $file)) {
                                        require_once($is_file);
                                    }
                                }
                            }
                            closedir($handle2);
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}