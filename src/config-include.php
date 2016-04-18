<?hh

use Ken\Ken,
	Ken\TaskController;




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