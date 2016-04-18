<?hh

namespace Ken\Interfaces;

use Ken\Application;




interface TaskLibraryInterface {
	
    function register(Application $app);
    
    function boot(Application $app);
}