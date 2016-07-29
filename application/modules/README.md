CodeIgniter HMVC Modules
========================

This extension for CodeIgniter enables the use of the Hierarchical Model View Controller(HMVC) pattern and makes your application modular. This allows easy distribution of independent components (MVC) in a single directory across other CodeIgniter applications. All modules are grouped in their own folder and can have their own controller, model, view, library, config, helper and language files.

Installation
------------

Download the file from github and place them into their corresponding folders in the application directory.

_Note: to use the HMVC functionality, make sure the Controller and Router you use extend their HMVC class._

Next, add the location of your modules directory to the main config.php file:

    /*
    |--------------------------------------------------------------------------
    | Modules locations
    |--------------------------------------------------------------------------
    |
    | These are the folders where your modules are located. You may define an
    | absolute path to the location or a relative path starting from the root
    | directory.
    |
    */
    
    $config['modules_locations'] = array(APPPATH . 'modules/');

Functionallity
--------------

This is the basic structure of a HMVC module:

    /modules
        /module
           /controllers
           /config
           /helpers
           /language
           /libraries
           /models
           
From within a module you can load its own resources just like you always do. If on the other hand, you want to load resources from another module you can do this by adding the module's name like a directory structure:

    class Hello extends CI_Controller {
        
        public function index() {
            // load a model from the current module
            $this->load->model('local_model');
            
            // load a model from another module
            $this->load->model('other_module/model');

            // HMVC example
            $this->load->controller('module/controller/method', $params = array(), $return = FALSE);
        }
    }
    
Because of the modified router, the module's controllers are accessible like a directory structure. Controllers may be loaded from the application/controllers sub-directories or the module/controllers sub-directories:

    /module/hello -> /module/controllers/hello.php (index method)
    /module/hello -> /module/controllers/hello/hello.php (index method)
    /module/hello -> /module/controllers/hello/(default_controller).php (hello method)
    /module/hello -> /module/controllers/module.php (hello method)
    /module/hello -> /module/controllers/(default_controller).php (hello method)
    
If the requested module contains a routes.php config file it will automatically be added to the main routes.

Hierarchical controllers
------------------------

To load hierarchical controllers you use the `$this->load->controller()` method. This method works similar to loading views with CodeIgniter, this method accepts the following parameters:

 - URI: a URI string pointing to the requested controller (and method). This function uses the same locating technique as explained above.
 - Parameters: an array containing the arguments for the requested method.
 - Return : a boolean indicating whether the output should be returned or show on the screen (default).
 
For example, this is our main controller where we pass the request to a sub-controller in the same module:
 
    class Specials extends CI_Controller {
        
        public function index() {
            $this->load->controller('blogs/random', array('specials'));
        }
        
    }
    
And the sub-controller contains the real method:

    class Blogs extends CI_Controller {
    
        public function random($type) {
            ...
        }
        
    }
