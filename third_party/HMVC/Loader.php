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
     * Constructor
     *
     * Add the current module to all paths permanently
     */
    public function __construct() {
        parent::__construct();
        
        $router = & $this->_ci_get_component('router');
        if ($router->module) {
            $path = APPPATH . 'modules/' . $router->module . '/';
            
            array_unshift($this->_ci_library_paths, $path);
            array_unshift($this->_ci_model_paths, $path);
            $this->_ci_view_paths[$path . 'views/'] = 1;
            
            $config = & $this->_ci_get_component('config');
            array_unshift($config->_config_paths, $path);
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
        if (list($class, $module) = $this->detect_module($library)) {
            // Add module as a package
            array_unshift($this->_ci_library_paths, APPPATH . 'modules/' . $module);
            
            // Let parent do the heavy work
            return parent::library($class, $params, $object_name);
            
            // Remove package again
            array_shift($this->_ci_library_paths);
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
        if (list($class, $module) = $this->detect_module($model)) {
            // Add module as a package
            array_unshift($this->_ci_model_paths, APPPATH . 'modules/' . $module);
            
            // Let parent do the heavy work
            return parent::model($class, $name, $db_conn);
            
            // Remove package again
            array_shift($this->_ci_model_paths);
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
        if (list($class, $module) = $this->detect_module($view)) {
            // Add module as a package
            $this->_ci_view_paths[APPPATH . 'modules/' . $module . '/views/'] = 1;
            
            // Let parent do the heavy work
            return parent::view($class, $vars, $return);
            
            // Remove package again
            unset($this->_ci_view_paths[APPPATH . 'modules/' . $module . '/views/']);
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
        if (list($class, $module) = $this->detect_module($file)) {
            // Add module as a package
            $config = & $this->_ci_get_component('config');
            array_unshift($config->_config_paths, APPPATH . 'modules/' . $module);
            
            // Let parent do the heavy work
            parent::config($class, $use_sections, $fail_gracefully);
            
            // Remove package again
            array_shift($config->_config_paths);
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
        if (list($class, $module) = $this->detect_module($helper)) {
            // Add module as a package
            array_unshift($this->_ci_helper_paths, APPPATH . 'modules/' . $module);
            
            // Let parent do the heavy work
            return parent::helper($class);
            
            // Remove package again
            array_shift($this->_ci_helper_paths);
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
        if (list($class, $module) = $this->detect_module($file)) {
            // Add module as a package
            array_unshift($this->_ci_library_paths, APPPATH . 'modules/' . $module);
            array_unshift($this->_ci_model_paths, APPPATH . 'modules/' . $module);
            
            // Let parent do the heavy work
            return parent::language($class, $lang);
            
            // Remove package again
            array_shift($this->_ci_library_paths);
            array_shift($this->_ci_model_paths);
        } else {
            return parent::language($file, $lang);
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
            $module = substr($class, 0, $first_slash + 1);
            $class = substr($class, $first_slash + 1);
            
            if (is_dir(APPPATH . 'modules/' . $module))
                return array($class, $module);
            else
                return FALSE;
        }
        return FALSE;
    }
}