Ken
====================

Ken is a project automation tool in Hacklang and can be used as a general build tool as well.

Ken exists because of [Bob](https://github.com/CHH/bob), a great PHP tool it's based on. I haven't forked because the project changed too much.

## How it works

Ken doesn't use XML/JSON for task files. Instead, tasks are registered with the PHP language itself using the [TaskLibraryInterface](src/Ken/Interfaces/TaskLibraryInterface.php) or configuration files.

[Application](src/Ken/Application.php) is the core object used by developers to register task files or libraries.

The tasks are created by [TaskFactory](src/Ken/TaskFactory.php).

## Getting Started

If not specified, the default configuration file is loaded from ken_config.php

If the folder ken_tasks exists, then all files inside it are threated as configuration files.

A configuration file is required at runtime and uses 2 functions:

+ task(name, dependencies, action) - register a new task
..+ task(name, dependencies)
..+task(name, action)
..+task(action)
+ desc(text) - provides the description for the next task defined

The configuration file has also a global instance of [Application](src/Ken/Application.php) that can be accesed outside the task closure, internally know as **action**.

```php
desc('Concat clientside code');
task('concat', function(Application $app) {
	// concat files
});

desc('Minify clientside code');
task('minify', function(Application $app) {
	// minify files
});

task('default', ['concat', 'minify'])
```

To provide a better OOO approach, you can define tasks instead with **TaskLibraryInterface**.

Libraries are registered in **Application** with:

```php
register(TaskLibraryInterface $taskLib, array $parameters = array())
```

To create a new library you must implement the **TaskLibraryInterface**. For an example, check the [ComposerLibrary](src/Ken/Library/ComposerLibrary.php) which manage composer packages. 


## Client Interfaces

The [ClientInterface](src/Ken/Interfaces/ClientInterface.php) represents the entry point for developers to use the **Ken** library.

For now there're two implementations for **ClientInterface**:

+ Cli: The CLI implementation to be used with HHVM client
+ Host: An implementation to be used inside another framework or existing project (you're able to execute tasks from inside your code easily)

While the CLI is used to execute tasks directly from the command line, the Host is used to be consumed inside other code.


Both Cli and Host initialize the client dependencies creating a new **Application** then the method **run** is invoked.

### Cli

The CLI is pretty straightford and you can access it through `bin/ken`.

When called the **run** method, the Cli executes the tasks.

### Host

The Host client must be **run** at Application Startup, before any consumption to **Ken** is done. 
To execute the tasks, the developer invokes the **task** method.


## Install

### Pre Requesites

Ken needs at least **hhvm 3.8** to run.

### Composer

Add the `guilhermegeek/ken` package to the `require-dev` section in your composer

```javascript
{
	"require-dev": {
		"guilhermegeek/ken": "master"
	}
}
```

Then run `composer install -dev`

You can invoke ken with:

```
hhvm bin/ken
```