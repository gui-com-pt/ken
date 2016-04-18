<?hh

namespace Ken\Interfaces;




/**
 * Each Task may contain more than one action/callback function
 * Tasks are registered in Application directly or with TaskRegistry
 * 
 * When is created, the task doesn't have any action yet
 * TaskRegistry is responsible for reusing the same task and push actions
 * Priority is also supported (?)
 */ 
interface TaskInterface {
	
	/**
	 * Invoke
	 */ 
	function invoke() : void;

	/**
	 * Dispose the task
	 */
	function dispose() : void;

	/**
	 * The name of task, used internally
	 */ 
	function __toString() : string;
}