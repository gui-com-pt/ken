<?hh

namespace Ken;

use Ken\Interfaces\ClientInterface;




class HostClient extends AbstractClient implements ClientInterface  {

	public function __construct()
	{
    	parent::__construct();	
	}

	public function run($argv = null) : void
	{
		try {
	      $this->app->init();
	    } catch(\Exception $ex) {
	      fwrite(STDERR, $e);
	      return 127;
	    }

	    if(is_string($argv)) {
	    	$this->invokeTask($argv);
	    } else if(is_array($argv)) {
	    	$this->invokeTasks($argv);
	    } else {
	    	throw new \InvalidArgumentException();
	    }
	}
}