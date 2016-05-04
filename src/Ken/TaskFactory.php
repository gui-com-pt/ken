<?hh

namespace Ken;

use Ken\Interfaces\TaskInterface,
	Ken\Interfaces\TaskFactoryInterface;




/**
 * TaskFactory is responsible for creating tasks and return then to add new tasks
 */
class TaskFactory implements TaskFactoryInterface {

	/**
	 * List of callable functions that resolve the cache
	 * The user only adds an action
	 */
	public array $tasks;
	
	protected EventManager $em;

	public function __construct(
		protected Application $app
	)
	{
		$this->em = $app->em ?: new EventManager();
		$this->tasks = array();
	}

	public function create(string $name, $prerequisites = null, $action = null, $class = null) : void
	{
		if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
		$app = $this->app;
		$fn =function($name) use ($app, $name, $prerequisites, $action, $class) : TaskInterface {
			
	    	if ($app->taskIsDefined($name)) {
	            $task = $app->getTask($name);
	            if($task === null) {
	            	throw new \Exception('Task is registered but counldnt be accessed in TaskController');
	            }
	        } else {
	            $task = new $class($name, $app);
	            $app->addTask($task);
	        }

	        $task->register($prerequisites, $action);

	        return $task;
        };

        return $fn;
    }
}