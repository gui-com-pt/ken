<?hh

namespace Ken;

use Ken\Interfaces\TaskInterface,
	Pi\EventManager;




class Task implements TaskInterface {

	const EVENT_PREFIX = 'task::';

	protected array $actions;

	/**
	 * The task dependencies
	 * 
	 * Dependencies are passed at the task registration
	 * The task callable only receive a Application instance, the dependencies are resolved with $this->dependencies
	 * 
	 * If a task depends on other task, then the other task is executed before this
	 */
	protected Set $dependencies;

	/**
	 * The Event Manager
	 */
	protected EventManager $em;

	protected string $description;

	public function __construct(
		public string $name,
		protected Application $app,
		?EventManager $em = null
	)
	{
		$this->clear();
		$this->em = $em ?: new EventManager();
		$this->description = TaskController::$lastDescription;
		TaskController::$lastDescription = '';
	}

	/**
	 * Register a new action
	 */
	public function register(?mixed $dependencies = null, $action = null) : void
	{
		if($dependencies !== null && $this->dependencies instanceof \Set) {
			$this->dependencies->addAll($dependency);
			//foreach ($dependencies as $dependency) {
		//		$this->dependencies->add($dependency);
	//		}

			
		} else {
			$this->dependencies = new Set($dependencies);
		}

		if(is_callable($action)) {
			$this->actions[] = $action;
		} else {
			
		}
	}

	public function getTaskDependencies()
	{

	}
	
	/**
	 * Invoke
	 */ 
	public function invoke() : void
	{
		$app = $this->app;
		
		if($this->dependencies !== null && $this->dependencies->count() > 0) {
			$deps = $this->dependencies->filter($dep ==> {
				if($app->taskIsDefined((string) $dep)) {
					$app->taskController[(string) $dep]->invoke();	
				}
			});
			
		}

        $this->execute();
	}

	/**
	 * Dispose the task
	 */
	public function dispose() : void
	{
		$this->clear();
	}

	/**
	 * The name of task, used internally
	 */ 
	public function __toString() : string
	{
		return $this->name;
	}

	protected function clear() : void
	{
		$this->actions = array();
		$this->dependencies = null;
	}

	protected function execute() : void
	{
		foreach ($this->actions as $action) {
			call_user_func($action, $this);
		}
	}
}