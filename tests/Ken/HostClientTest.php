<?hh

namespace Test;

use Ken\Application,
	Ken\HostClient,
	Ken\Library\ComposerLibrary,
	Ken\Interfaces\TaskLibraryInterface;




class Mock2Library implements TaskLibraryInterface {

	static $initialized = 0;

	public function register(Application $app)
    {
    	
    }
    
    public function boot(Application $app)
    {
    	$app->task('mock2', function($task) {
    		Mock2Library::$initialized = 1;
    	});
    }
}

class HostClientTest extends \PHPUnit_Framework_TestCase {
	
	protected $client;

	public function setUp()
	{
		TestExtensions::init();
		$this->client = new HostClient();
	}

	public function tearDown()
	{
		$this->client->dispose();
	}

	public function testCanRegisterComposerLibrary()
	{
		$this->client->app->register(new Mock2Library());
		$this->client->run('mock2');
		$this->assertEquals(Mock2Library::$initialized, 1);
	}
}