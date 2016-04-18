<?hh

namespace Ken;

use Ken\Ken,
	Ken\Interfaces\TaskLibraryInterface,
	Ken\Interfaces\TaskInterface,
	Pi\EventManager,
	Pi\ContainerFactory,
	Pi\Logging\DebugLogFactory,
	Pi\Cache\ApcCacheProvider,
	Pi\Interfaces\IContainer,
	Pi\Interfaces\ICacheProvider,
	Pi\Interfaces\ILog,
	Pi\Interfaces\ILogFactory;




/**
 * The Application - Core object
 * 
 * Application is the core object responsible for creating dependencies amoung modules
 */
class Application {

	public IContainer $container;

	public TaskFactory $taskFactory;

	public TaskController $taskController;

	public EventManager $em;

	public ICacheProvider $cache;

	public ILogFactory $logFactory;

	public ILog $log;

	public string $defaultTaskClass;
	
	/**
	 * The working directory
	 */
	public string $workDir;

	/**
	 * The directory where the root config was found
	 */
	protected string $projectDirectory;

	/**
	 * The directory where the config are found
	 */
	protected string $configLoadPath;

	/**
	 * List of paths of all loaded config files
	 */
	protected Set<string> $loadedConfigs;

	/**
	 * If true, force tasks to be run even if they're not needed
	 */
	protected bool $forceRun = false;

	protected Set<TaskLibraryInterface> $taskLibraries;

	protected Set<string> $configFiles;

	protected array $env;

	public function __construct()
	{
		Ken::init($this);
		$this->logFactory = new DebugLogFactory();
		$this->log = $this->logFactory->getLogger(Application::class);
		$this->cache = new ApcCacheProvider();
		$this->env = array();
		$this->defaultTaskClass = Task::class;
		$this->workDir = __DIR__;
		$this->projectDirectory = __DIR__;
		$this->loadedConfigs = new Set<string>();
		$this->configFiles = new Set<string>(array('ken_config.php'));
		$this->configLoadPath = 'ken_tasts';
		$this->taskLibraries = new Set<TaskLibraryInterface>();
		$factory = new ContainerFactory(new \Pi\Cache\InMemoryCacheProvider());
		$this->container = $container = $factory->createContainer();
		$this->em = new EventManager();
		$this->taskController = new TaskController();
		$this->taskFactory = new TaskFactory($this);
		$this->log->debug('Application constructed.');
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

	public function init() : void
	{
		$this->loadConfiguration();
		// call boot on taskLibraries
		$this->log->debug('Application initialized.');
	}

	public function execute(array $tasks) : void
	{
		// Execute the tasks, calling the invoke. project->directory exists then chdir to call it
		if(!empty($this->projectDirectory)) {
			chdir($this->projectDirectory);
		}

		foreach ($tasks as $taskName) {
			
			if(!array_key_exists($taskName, $this->taskController) || !$task = $this->taskController[$taskName]) {
				throw new \InvalidArgumentException("The task $taskName wasnt registered");
			}
			$task->invoke();
		}
	}

	public function register(TaskLibraryInterface $taskLib, array $parameters = array())
	{
		$taskLib->register($this);
		$this->taskLibraries->add($taskLib);

		// register parameters
	}

	/**
	 * Register the Task
	 * 
	 * Called by TaskFactory, which is responsible for creating Tasks. Each task creation closure then invoked this method
	 */
	public function addTask(TaskInterface $task)
	{
		$this->taskController[$task->name] = $task;
	}

	public function getTask(string $name) : ?TaskInterface
	{
		$task = $this->taskController[$name];
		return $task($name);
	}

	/**
	 * Add a new Task
	 * This doesnt follow a proper OOO approeach
	 * The arguments are resolved with func_get_args and three arguments may be passed (instead of only $name): $name, $dependencies, $action
	 * 
	 * Name: the task name
	 * Dependencies: single or array of dependencies passed and available for task with $this->dependencies
	 * Action: callable function that access $dependencies as a property of itself
	 */
	public function task($name)
    {
    	$action = null;
        $prerequisites = null;
        $class = $this->defaultTaskClass;
        
        foreach (array_filter(array_slice(func_get_args(), 1)) as $arg) {

            switch (true) {
                case is_callable($arg):
                    $action = $arg;
                    break;
                case is_string($arg) and class_exists($arg):
                    $class = $arg;
                    break;
                case is_array($arg):
                case ($arg instanceof \Traversable):
                case ($arg instanceof \Iterator):
                    $prerequisites = $arg;
                    break;
                case is_string($arg) && isset($this->taskController[$arg]):
	                if(is_null($prerequisites)) {
	                	$prerequisites = array();
	                }
	                $prerequisites[] = $arg;
                
                break;
            }
        }
        
    	$task = $this->taskFactory->create($name, $prerequisites, $action, $class);
    	$task = $task($name);
    	$this->taskController[$name] = $task;

    }

    /**
     * Indicates if a task is defined at Application
     * @return boolean True if is defined, false if isn't
     */
    public function taskIsDefined(mixed $task) : bool
    {
    	if($task instanceof TaskInterface && !empty($task->name())) {
    		$task = $task->name;
    	}

    	return isset($this->taskController[$task]);
    }

    protected function loadConfiguration()
    {
    	$rootConfigPath = false;

        foreach ($this->configFiles as $file) {
            $rootConfigPath = self::findConfigFile($file, getcwd());

            if (false !== $rootConfigPath) {
                break;
            }
        }

        if (false === $rootConfigPath) {

            $this['log']->err(sprintf(
                "Filesystem boundary reached, none of %s found.\n",
                json_encode((array) $this['config.file'])
            ));
            return false;
        }

        include __DIR__.'/../config-include.php';

        $this->loadConfigurationFile($rootConfigPath);

        # Save the original working directory, the working directory
        # gets set to the project directory while running tasks.
        $this->originalDirectory = getcwd();

        # The project dir is the directory of the root config.
        $this->projectDirectory = dirname($rootConfigPath);

        return true;
    }

    protected function loadConfigurationFile(string $fileName)
    {
    	if($this->loadedConfigs->contains($fileName)) {
    		return;
    	}
    	include $fileName;
    	$this->loadedConfigs->add($fileName);
    }
}