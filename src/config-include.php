<?hh

use Ken\Ken,
	Ken\TaskController,
    Symfony\Component\Process\Process;



function register(TaskLibraryInterface $taskLib, array $parameters = array())
{
    return Ken::$application->register($taskLib, $parameters);
}

function task($name, $prerequisites = null, $callback = null)
{
    return Ken::$application->task($name, $prerequisites, $callback);
}

function desc($desc)
{
    TaskController::$lastDescription = $desc;
}

function println($line, $stream = null)
{
    $line .= PHP_EOL;

    if (is_resource($stream)) {
        fwrite($stream, $line);
    } else {
        echo $line;

    }
}

function hhvm($argv, $callback = null, $options = array())
{
    $execFinder = new \Symfony\Component\Process\PhpExecutableFinder;
    $php = $execFinder->find();

    $argv = (array) $argv;
    array_unshift($argv, $php);

    return shell($argv, $callback, $options);
}

function logger()
{
    return Ken::$application->log();
}

function shell($cmd, $callback = null, $options = array())
{
    $cmd = join(' ', (array) $cmd);
    $showCmd = strlen($cmd) > 42 ? "..." . substr($cmd, -42) : $cmd;

    logger()->info("sh($showCmd)");

    if (func_num_args() == 2 and is_array($callback) and !is_callable($callback)) {
        $options = $callback;
        $callback = null;
    }

    $timeout     = @$options["timeout"];
    $failOnError = @$options["failOnError"] ?: @$options['fail_on_error'];

    $process = new Process($cmd);
    $process->setTimeout($timeout);

    $process->run(function($type, $output) {
        $type == 'err' ? fwrite(STDERR, $output) : print($output);
    });

    if (!$process->isSuccessful() and $failOnError) {
        failf("Command failed with status (%d) [%s]", array($process->getExitCode(), $showCmd));
    }

    if ($callback !== null) {
        call_user_func($callback, $process->isSuccessful(), $process);
    }
}