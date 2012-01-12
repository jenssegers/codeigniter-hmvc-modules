<?php
/**
 * @name		CodeIgniter HMVC Modules
 * @author		Jens Segers
 * @link		http://www.jenssegers.be
 * @license		MIT License Copyright (c) 2011 Jens Segers
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class HMVC_Loader extends CI_Loader {
    
    /**
     * List of paths to load controllers from
     *
     * @var array
     * @access protected
     */
    protected $_ci_controller_paths = array();
    
    /**
     * List of loaded controllers
     *
     * @var array
     * @access protected
     */
    protected $_ci_controllers = array();
    
    /**
     * List of loaded modules
     *
     * @var array
     * @access protected
     */
    protected $_ci_modules = array();
    
    /**
     * Constructor
     *
     * Add the current module to all paths permanently
     */
    public function __construct() {
        parent::__construct();
        
        // Add default controller path
        $this->_ci_controller_paths = array(APPPATH);
        
        // Get current module from the router
        $router = & $this->_ci_get_component('router');
        if ($router->module) {
            $this->add_module($router->module);
        }
    }
    
    /**
     * Controller Loader
     *
     * This function lets users load and hierarchical controllers to enable HMVC support
     *
     * @param	string	the uri to the controller
     * @return	void
     */
    public function controller($uri) {
        $params = array_slice(func_get_args(), 1);
        
        // Detect module
        if (list($module, $uri2) = $this->detect_module($uri)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                $this->_load_controller($uri2, $params);
            }
            
            // Add module
            $this->add_module($module);
            
            // Load controller
            $void = $this->_load_controller($uri2, $params);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            return $this->_load_controller($uri, $params);
        }
    }
    
    /**
     * Class Loader
     *
     * This function lets users load and instantiate classes.
     * It is designed to be called from a user's app controllers.
     *
     * @param	string	the name of the class
     * @param	mixed	the optional parameters
     * @param	string	an optional object name
     * @return	void
     */
    public function library($library = '', $params = NULL, $object_name = NULL) {
        if (is_array($library)) {
            foreach ($library as $class) {
                $this->library($class, $params);
            }
            return;
        }
        
        // Detect module
        if (list($module, $class) = $this->detect_module($library)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::library($class, $params, $object_name);
            }
            
            // Add module
            $this->add_module($module);
            
            // Let parent do the heavy work
            $void = parent::library($class, $params, $object_name);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            return parent::library($library, $params, $object_name);
        }
    }
    
    /**
     * Model Loader
     *
     * This function lets users load and instantiate models.
     *
     * @param	string	the name of the class
     * @param	string	name for the model
     * @param	bool	database connection
     * @return	void
     */
    public function model($model, $name = '', $db_conn = FALSE) {
        if (is_array($model)) {
            foreach ($model as $babe) {
                $this->model($babe);
            }
            return;
        }
        
        // Detect module
        if (list($module, $class) = $this->detect_module($model)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::model($class, $name, $db_conn);
            }
            
            // Add module
            $this->add_module($module);
            
            // Let parent do the heavy work
            $void = parent::model($class, $name, $db_conn);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            return parent::model($model, $name, $db_conn);
        }
    }
    
    /**
     * Load View
     *
     * This function is used to load a "view" file.  It has three parameters:
     *
     * 1. The name of the "view" file to be included.
     * 2. An associative array of data to be extracted for use in the view.
     * 3. TRUE/FALSE - whether to return the data or load it.  In
     * some cases it's advantageous to be able to return data so that
     * a developer can process it in some way.
     *
     * @param	string
     * @param	array
     * @param	bool
     * @return	void
     */
    public function view($view, $vars = array(), $return = FALSE) {
        // Detect module
        if (list($module, $class) = $this->detect_module($view)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::view($class, $vars, $return);
            }
            
            // Add module
            $this->add_module($module);
            
            // Let parent do the heavy work
            $void = parent::view($class, $vars, $return);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            return parent::view($view, $vars, $return);
        }
    }
    
    /**
     * Loads a config file
     *
     * @param	string
     * @param	bool
     * @param 	bool
     * @return	void
     */
    public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE) {
        // Detect module
        if (list($module, $class) = $this->detect_module($file)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::config($class, $use_sections, $fail_gracefully);
            }
            
            // Add module
            $this->add_module($module);
            
            // Let parent do the heavy work
            $void = parent::config($class, $use_sections, $fail_gracefully);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            parent::config($file, $use_sections, $fail_gracefully);
        }
    }
    
    /**
     * Load Helper
     *
     * This function loads the specified helper file.
     *
     * @param	mixed
     * @return	void
     */
    public function helper($helper = array()) {
        if (is_array($helper)) {
            foreach ($helper as $help) {
                $this->helper($help);
            }
            return;
        }
        
        // Detect module
        if (list($module, $class) = $this->detect_module($helper)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::helper($class);
            }
            
            // Add module
            $this->add_module($module);
            
            // Let parent do the heavy work
            $void = parent::helper($class);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            return parent::helper($helper);
        }
    }
    
    /**
     * Loads a language file
     *
     * @param	array
     * @param	string
     * @return	void
     */
    public function language($file = array(), $lang = '') {
        if (is_array($file)) {
            foreach ($file as $langfile) {
                $this->language($langfile, $lang);
            }
            return;
        }
        
        // Detect module
        if (list($module, $class) = $this->detect_module($file)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::language($class, $lang);
            }
            
            // Add module
            $this->add_module($module);
            
            // Let parent do the heavy work
            $void = parent::language($class, $lang);
            
            // Remove module
            $this->remove_module();
            
            return $void;
        } else {
            return parent::language($file, $lang);
        }
    }
    
    /**
     * Add Module
     *
     * Allow resources to be loaded from this module path
     *
     * @param	string
     * @param 	boolean
     * @return	void
     */
    public function add_module($module, $view_cascade = TRUE) {
        
        // Mark module as loaded
        array_unshift($this->_ci_modules, $module);
        
        $path = APPPATH . 'modules/' . rtrim($module, '/') . '/';
        
        // Add controller path
        array_unshift($this->_ci_controller_paths, $path);
        
        // Add package path
        return parent::add_package_path($path, $view_cascade);
    }
    
    /**
     * Remove Module
     *
     * Remove a module from the allowed module paths
     *
     * @param	type
     * @param 	bool
     * @return	type
     */
    public function remove_module($module = '', $remove_config = TRUE) {
        if ($module == '') {
            // Mark module as not loaded
            array_shift($this->_ci_modules);
            
            // Remove controller path
            array_shift($this->_ci_controller_paths);
            
            // Remove package path
            return parent::remove_package_path('', $remove_config);
        } else if (($key = array_search($module, $this->_ci_modules)) !== FALSE) {
            // Mark module as not loaded
            unset($this->_ci_modules[$key]);
            
            $path = APPPATH . 'modules/' . rtrim($module, '/') . '/';
            
            // Remove controller path
            if (($key = array_search($path, $this->_ci_controller_paths)) !== FALSE) {
                unset($this->_ci_controller_paths[$key]);
            }
            
            // Remove package path
            $path = APPPATH . 'modules/' . rtrim($module, '/') . '/';
            return parent::remove_package_path($path, $remove_config);
        }
    }
    
    /**
	 * Controller loader
	 *
	 * This function is used to load and instantiate controllers
	 *
	 * @param	string
	 * @param	array
	 * @return	object
	 */
    private function _load_controller($class = '', $params = array()) {
        $method = "index";
        
        if (($first_slash = strpos($class, '/')) !== FALSE) {
            $class = substr($class, 0, $first_slash);
            $method = substr($class, $first_slash + 1);
        }
        
        if (!array_key_exists(strtolower($class), $this->_ci_controllers)) {
            // Check controller folders for matching controller file
            foreach ($this->_ci_controller_paths as $path) {
                $filepath = $path . 'controllers/' . $class . '.php';
                
                if (file_exists($filepath)) {
                    // Load the controller file
                    include_once ($filepath);
                    break;
                }
            }
            
            $name = ucfirst($class);
            $class = strtolower($class);
            
            if (!class_exists($class)) {
                log_message('error', "Non-existent class: " . $name);
                show_error("Non-existent class: " . $class);
            }
            
            // Create a controller object
            $this->_ci_controllers[$class] = new $name();
        }
        
        $controller = $this->_ci_controllers[$class];
        
        if (method_exists($controller, $method)) {
            ob_start();
            $output = call_user_func_array(array($controller, $method), $params);
            $buffer = ob_get_clean();
            return ($output !== NULL) ? $output : $buffer;
        }
    }
    
    /**
     * Detects the module from a string
     * 
     * @param	string
     * @return	array
     */
    private function detect_module($class) {
        $class = str_replace('.php', '', trim($class, '/'));
        if (($first_slash = strpos($class, '/')) !== FALSE) {
            $module = substr($class, 0, $first_slash);
            $class = substr($class, $first_slash + 1);
            
            if (is_dir(APPPATH . 'modules/' . $module)) {
                return array($module, $class);
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }
}