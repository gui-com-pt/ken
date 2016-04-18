<?hh

namespace Test;

class TestExtensions {
	
	static string $testProjectDirectory = null;

	static function init()
	{
		self::$testProjectDirectory = dirname(__DIR__.'/../');
	}
}