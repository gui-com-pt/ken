<?hh

namespace Test;

use Ken\Application;




class ApplicationTest extends \PHPUnit_Framework_TestCase {
	
	public function setUp()
	{
		TestExtensions::init();
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
		$app->task('some-name-before', array('some-name'));
		$app->task('dont-execute', function($app) {
			throw new \Exception("Shouldn't execute this task");
		});
		$this->assertFalse($executed);
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