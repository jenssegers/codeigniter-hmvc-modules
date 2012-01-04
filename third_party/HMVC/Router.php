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

class HMVC_Router extends CI_Router {
    
    /**
     * Current module name
     *
     * @var string
     * @access public
     */
    var $module = '';
    
    /**
     * Validates the supplied segments.  Attempts to determine the path to
     * the controller.
     *
     * @access	private
     * @param	array
     * @return	array
     */
    function _validate_request($segments) {
        if (count($segments) == 0) {
            return $segments;
        }
        
        // Locate the controller with modules support
        if ($located = $this->locate($segments)) {
            return $located;
        }
        
        // Is there a 404 override?
        if (!empty($this->routes['404_override'])) {
            $segments = explode('/', $this->routes['404_override']);
            if ($located = $this->locate($segments)) {
                return $located;
            }
        }
        
        // Nothing else to do at this point but show a 404
        show_404($segments[0]);
    }
    
    /**
     * Parse Routes
     *
     * This function matches any routes that may exist in
     * the config/routes.php file against the URI to
     * determine if the class/method need to be remapped.
     *
     * @access	private
     * @return	void
     */
    function _parse_routes() {
        $module = $this->uri->segment(0);
        
        // Apply the current module's routing config
        if ($module && is_file(APPPATH . 'modules/' . $module . '/config/routes.php')) {
            include (APPPATH . 'modules/' . $module . '/config/routes.php');
            
            $route = (!isset($route) or !is_array($route)) ? array() : $route;
            $this->routes = array_merge($this->routes, $route);
            unset($route);
        }
        
        // Let parent do the heavy routing
        return parent::_parse_routes();
    }
    
    /**
     * The logic of locating a controller is grouped in this function
     * 
     * @param	array
     * @return	array
     */
    private function locate($segments) {
        list($module, $directory, $controller) = array_pad($segments, 3, NULL);
        
        // Does a module exist? (/modules/xyz/controllers/)
        if (is_dir($source = APPPATH . 'modules/' . $module . '/controllers/')) {
            $this->module = $module;
            $this->directory = '../modules/' . $module . '/controllers/';
            
            // Module root controller?
            if ($directory && is_file($source . $directory . '.php')) {
                return array_slice($segments, 1);
            }
            
            // Module sub-directory?
            if ($directory && is_dir($source . $directory . '/')) {
                $source = $source . $directory . '/';
                $this->directory .= $directory . '/';
                
                // Module sub-directory controller?
                if (is_file($source . $directory . '.php')) {
                    return array_slice($segments, 1);
                }
                
                // Module sub-directory  default controller?
                if (is_file($source . $this->default_controller . '.php')) {
                    $segments[1] = $this->default_controller;
                    return array_slice($segments, 1);
                }
                
                // Module sub-directory sub-controller? 
                if ($controller && is_file($source . $controller . '.php')) {
                    return array_slice($segments, 2);
                }
            }
            
            // Module controller?
            if (is_file($source . $module . '.php')) {
                return $segments;
            }
            
            // Module default controller?
            if (is_file($source . $this->default_controller . '.php')) {
                $segments[0] = $this->default_controller;
                return $segments;
            }
        }
        
        // Root folder controller?
        if (is_file(APPPATH . 'controllers/' . $module . '.php')) {
            return $segments;
        }
        
        // Sub-directory controller?
        if ($directory && is_file(APPPATH . 'controllers/' . $module . '/' . $directory . '.php')) {
            $this->directory = $module . '/';
            return array_slice($segments, 1);
        }
        
        // Default controller?
        if (is_file(APPPATH . 'controllers/' . $module . '/' . $this->default_controller . '.php')) {
            $segments[0] = $this->default_controller;
            return $segments;
        }
    }
}