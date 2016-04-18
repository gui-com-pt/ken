<?hh

namespace Ken;

abstract class AbstractClient {

	protected string $defaultConfigPath;

	protected Application $app;

	public function __construct()
	{
		$this->app = new Application();
		$this->defaultConfigPath = WORK_DIR.'/ken_config.php';
	}

	static function findConfigFile($filename, $cwd)
    {
        if (!is_dir($cwd)) {
            throw new \InvalidArgumentException(sprintf(
                '%s is not a directory', $cwd
            ));
        }

        # Look for the definition name in the $cwd
        # until one is found.
        while (!$rp = realpath("$cwd/$filename")) {
            # Go up the hierarchy
            $cwd .= '/..';

            # We are at the filesystem boundary if there's
            # nothing to go up to.
            if (realpath($cwd) === false) {
                break;
            }
        }

        return $rp;
    }

	public function execute(string $taskName) : void
	{
		
	}

	public function executeTasks() : void
	{
		if($this->loadConfigurationFile()) {
			// executed the default tasks
		}
	}

	public function invokeTask(TaskInterface $task) : void
	{
		return $this->app->execute(array($task));
	}

	/**
	 * Invoke an collection of Tasks
	 */
	public function invokeTasks(array $tasks) : void
	{
		$this->app->execute($tasks);
	}

	protected function executeConfigurationFile(string $file)
	{
		
	}

	/**
	 * Return the default configuration file or null if not exists
	 */
	protected function loadConfigurationFile() : bool
	{
		if(file_exists($this->defaultConfigPath)) {
			require $this->defaultConfigPath;
			return true;
		}

		return false;
	}

  /**
   * Initialize a new Ken project creating ken_config.php
   */
  	protected function initProject(string $configFile = "ken_config.php", $namespace = "Ken\Runtime") : void
  	{
    	if (file_exists($configFile)) {
        	fwrite(STDERR, "Project already has a ken_config.php\n");
	        return false;
	    }

	    $config = <<<'EOF'
<?hh

namespace Ken\Runtime;

task('default', array('example'));

desc('Write Hello World to STDOUT');
task('example', function() {
	println("Example task executed");
});
EOF;

	    @file_put_contents($configFile, $config);

	    printf("Initialized project at \"%s\"\n", getcwd());
	    return true;

	}
}