<?hh

namespace Ken;

use Ken\Interfaces\ClientInterface,
	CHH\Optparse\Parser,
	CHH\Optparse\Exception as OptException;




class Cli extends AbstractClient implements ClientInterface {

	public function __construct()
	{
    parent::__construct();
		$this->opts = new Parser();
		$this->opts
            ->addFlag('init', array("alias" => '-i'))
            ->addFlag('version')
            ->addFlag('help', array("alias" => '-h'))
            ->addFlag('tasks', array("alias" => '-t'))
            ->addFlag('chdir', array("alias" => '-C', "has_value" => true))
            ->addFlag('verbose', array("alias" => '-v'))
            ->addFlagVar('all', $this->showAllTasks, array("alias" => '-A'))
            ->addFlagVar('trace', $this->trace, array("alias" => '-T'))
            ->addFlagVar('force', $this->forceRun, array("alias" => '-f'));
	}
	
  /**
   * Run the client
   * Parse the input data and execute the action
   */
	public function run($argv = null) : void
	{
		try {
      $this->opts->parse($argv);
    } catch (OptException $e) {
      fwrite(STDERR, "{$e->getMessage()}\n\n");
      fwrite(STDERR, $this->formatUsage());
      return 1;
    }

    if($dir = $this->opts["chdir"]) {
      if(!is_dir($dir)) {
        throw new \InvalidArgumentException("The directory $dir doesnt exist");
      }
      chdir($dir);
    }

    if ($this->opts["init"]) {
        return $this->initProject() ? 0 : 1;
    }

    if ($this->opts["help"]) {
        fwrite(STDERR, $this->formatUsage());
        return 0;
    }

    try {
      $this->app->init();
    } catch(\Exception $ex) {
      fwrite(STDERR, $e);
      return 127;
    }


    if($this->opts['tasks']) {
        fwrite(STDERR, $this->formatTasks());
        return 0;
    }
    
    $this->runTasks();
	}

  /**
   * Run Tasks
   * Collects tasks from arguments and invoke them
   */
  protected function runTasks() : void
  {
    $args = $this->opts->args();

    foreach ($args as $arg) {
      if(in_array($arg, array('bin/ken', 'bin/./ken'))) { 
        continue;
      }

      if (preg_match('/^(\w+)=(.*)$/', $arg, $matches)) {
          $this->app->env[$matches[1]] = trim($matches[2], '"');
          continue;
      }

      $tasks[] = $arg;
    }

    if (empty($tasks)) {
        $tasks = array('default');
    }
    $this->invokeTasks($tasks);
  }

  /**
   * Output a list of tasks available
   */
  protected function formatTasks() : string
  {
    $tasks = $this->app->taskController->getArrayCopy();
    ksort($tasks);
    $text = '';
    $text .= "(in {$this->app->projectDirectory})\n";

    foreach ($tasks as $name => $task) {
        if ($name === 'default' || (!$task->description && !$this->showAllTasks)) {
            continue;
        }

        if ($task instanceof FileTask) {
            $text .= "File => {$task->name}";
        } else {
            $text .= "bob {$task->name}";
        }

        $text .= "\n";

        if ($task->description) {
            $text .= "\t{$task->description}\n";
        }
    }

    return $text;
  }

	protected function formatUsage() : string
	{
		$version = Ken::VERSION;
		$bin = basename($_SERVER['SCRIPT_NAME']);

		return <<<HELPTEXT
builder $version

Usage:
  $bin
  $bin [VAR=VALUE...] [TASK...]
  $bin --init
  $bin -t|--tasks
  $bin -h|--help

Arguments:
  TASK:
    One or more task names to run. Task names can be everything as
    long as they don't contain spaces.
  VAR=VALUE:
    One or more environment variable definitions.
    These get placed in the \$_ENV array.

Options:
  -i|--init:
    Creates an empty `bob_config.php` in the current working
    directory if none exists.
  -t|--tasks:
    Displays a fancy list of tasks and their descriptions
  -A|--all:
    Shows all tasks, even file tasks and tasks without description.
  -C|--chdir <dir>:
    Changes the working directory to <dir> before running tasks.
  -T|--trace:
    Logs trace messages to STDERR
  -f|--force:
    Force to run all tasks, even if they're not needed
  -v|--verbose:
    Be more verbose.
  -h|--help:
    Displays this message


HELPTEXT;
    }
}