Codeigniter HMVC Modules
=========================================

This extension for CodeIgniter enables the use of the Hierarchical Model View Controller(HMVC) pattern and makes your application modular. This allows easy distribution of independent components (MVC) in a single directory across other CodeIgniter applications. All modules are grouped in their own folder and can have their own controller, model, view, library, config, helper and language files.

Installation
------------

Download the file from github and pace them into their corresponding folders in the application directory.

Functionallity
-------------

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

	class Hello extends MY_Controller {
		
		public function index() {
			// load a model from the current module
			$this->load->model("local_model");
			
			// load a model from another module
			$this->load->model("other_module/remote_model");
		}
	}
	
Because of the modified router, the module's controllers are accessible like a directory structure. Controllers may be loaded from the application/controllers sub-directories or the module/controllers sub-directories:

	/module/hello -> /module/controllers/hello.php (index method)
	/module/hello -> /module/controllers/hello/hello.php (index method)
	/module/hello -> /module/controllers/hello/(default_controller).php (hello method)
	/module/hello -> /module/controllers/module.php (hello method)
	/module/hello -> /module/controllers/(default_controller).php (hello method)