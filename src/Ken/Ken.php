<?hh

namespace Ken;




class Ken {
	
	const VERSION = '0.0.1-dev';

	static $application;

	static function init(Application $instance)
	{
		self::$application = $instance;
	}
}