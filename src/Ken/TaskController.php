<?hh

namespace Ken;




class TaskController extends \ArrayObject {

	
	static string $lastDescription = '';

	public function contains(string $key) : bool
	{
		return(isset($this[$key]));
		return array_key_exists($key, $this);
	}
}