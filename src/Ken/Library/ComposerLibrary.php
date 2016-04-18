<?hh

namespace Ken\Library;

use Ken\Interfaces\TaskLibraryInterface;




class ComposerLibrary implements TaskLibraryInterface {
	
    const string COMPOSER_INSTALL = 'composer:install';
    
    const string COMPOSER_UPDATE = 'composer:update';

    const string COMPOSER_LOCK = 'composer:lock';

    public function register(Application $app)
    {

    }
    
    public function boot(Application $app)
    {
    	$app->task('composer.phar', function($task) {
            if (file_exists($task->name)) {
                return true;
            }

            $src = fopen('http://getcomposer.org/composer.phar', 'rb');
            $dest = fopen($task->name, 'wb');

            stream_copy_to_stream($src, $dest);

        });

        $app->task(self::COMPOSER_INSTALL, function($task) {

        });

    }
}