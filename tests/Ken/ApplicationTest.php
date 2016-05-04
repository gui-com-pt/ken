<?hh

namespace Test;

use Ken\Application,
	Ken\Library\ComposerLibrary,
	Ken\Interfaces\TaskLibraryInterface;




class MockLibrary implements TaskLibraryInterface {

	static $initialized = 0;

	public function register(Application $app)
    {
    	self::$initialized = 1;
    }
    
    public function boot(Application $app)
    {
    	$app->task('key', function($task) {
    		MockLibrary::$initialized = 2;
    	});
    }
}

class ApplicationTest extends \PHPUnit_Framework_TestCase {
	
	public function setUp()
	{
		TestExtensions::init();
	}

	public function testCanRegisterComposerLibrary()
	{
		$app = new Application();
		$app->register(new MockLibrary());
		$app->init();
		$this->assertEquals(MockLibrary::$initialized, 1);
		$app->execute(array('key'));
		$this->assertEquals(MockLibrary::$initialized, 2);

	}

	public function testCanCreateDependencies()
	{
		$app = new Application();
		$this->assertNotNull($app->container);
		$this->assertNotNull($app->taskController);
		$this->assertNotNull($app->taskFactory);
		$this->assertNotNull($app->em);
	}

	public function testCanFindConfigFile()
	{
		chdir(TestExtensions::$testProjectDirectory);
		$file = Application::findConfigFile(TestExtensions::$testProjectDirectory, getcwd());
		$this->assertTrue(is_string($file) && !empty($file));
	}

	public function testCanInitAndLoadConfiguration()
	{
		$app = new Application();
		$path = dirname(__DIR__.'/../');
		chdir($path);
		$app->init();
	}

	public function testCanExecuteTask()
	{
		chdir(TestExtensions::$testProjectDirectory);
		$app = new Application();
		$executed = false;
		$app->task('some-name', function($app) use(&$executed) {
			$executed = true;
		});
		$app->task('some-name-after', array('some-name'));
		$app->task('some-name-before', 'some-name'); // non array
		$app->task('dont-execute', function($app) {
			throw new \Exception("Shouldn't execute this task");
		});
		$this->assertFalse($executed);
		$app->init();
		$app->execute(array('some-name'));
		$this->assertTrue($executed);
		$executed = false;
		$app->execute(array('some-name-before'));
		$this->assertTrue($executed);
		$executed = false;
		$app->execute(array('some-name-after'));
		$this->assertTrue($executed);
	}
}