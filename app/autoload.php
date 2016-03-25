<?php

namespace TMT;

/**
 * Autoloader for the TMT
 *
 * This is the main autoloader class for the TMT. It allows
 * directories to be associated to prefixes and follows the 
 * PSR-4 standard for autoloading. This class will be used
 * whenever a class is called for that is not present in the
 * existing file.
 *
 * @package team-management-tool 
 */
class Autoloader 
{
	/**
	 * An associative array that tracks prefixes and associated directories
	 *
	 * @var array
	 */
	private $prefixes = array();

	/**
	 * Default Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
	
	}

	/**
	 * Register the loader to the SPL autoload stack
	 *
	 * @return void
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));	
	}

	/**
	 * Adds a base directory for a namespace prefix
	 *
	 * @param string $prefix The namespace prefix
	 * @param string $dir The base directory for the classes in the namespace
	 * @param bool $prepend If true, prepend to array so the directory is searched first
	 * @return void
	 */
	public function addNamespace($prefix, $dir, $prepend = false)
	{
		// remove leading and trailing \ and append a \
		$prefix = trim($prefix, '\\').'\\';		

		// make sure there is a trailing /
		$dir = rtrim($dir, '/') . '/';

		// initialize an array for the namespace if doesn't exist
		if (isset($this->prefixes[$prefix]) === false) {
			$this->prefixes[$prefix] = array();
		} 

		// store the base directory for the namespace
		if ($prepend) {
			array_unshift($this->prefixes[$prefix], $dir);
		} else {
			array_push($this->prefixes[$prefix], $dir);
		}
		
	}

	/**
	 * Loads the class file for the given class name
	 *
	 * @param string $class The fully-qualified class name
	 * @return mixed The filename on success, or boolean false on failure
	 */
	public function loadClass($class)
	{
		// the current namespace prefix
		$prefix = $class;

		// works backwards through the namespace of the fully-qualified
		// class name to find an existing filename
		while (false !== $pos = strrpos($prefix, '\\')) {
			
			// retain the trailing namespace separator in the prefix
			$prefix = substr($class, 0, $pos+1);

			// the rest of the class name
			$relativeClass = substr($class, $pos+1);

			// try to load a file with the given prefix and relative class
			$file = $this->loadFile($prefix, $relativeClass);
			if ($file) {
				return $file;
			}

			// remove the trailing namespace separator for the next iteration
			$prefix = rtrim($prefix, '\\');
		}

		// never found a file
		return false;
	
	}

	/**
	 * Load the file for a namespace prefix and relative class
	 *
	 * @param string $prefix The namespace prefix
	 * @param string $relativeClass The relative class name
	 * @return mixed Boolean false if no file can be loaded, or name of the file
	 * that was loaded. 
	 */
	private function loadFile($prefix, $relativeClass)
	{
		// are there any base directories for the given prefix?
		if (isset($this->prefixes[$prefix]) === false) {
			return false;
		}

		// look through each of the given base directories for the given prefix
		foreach ($this->prefixes[$prefix] as $baseDir) {
			
			// replace the prefix with the base directory
			// replace namespace separators with directory separators
			// append .php to relative class name
			$file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

			// if the file exists require it
			if ($this->requireFile($file)) {
				// file found
				return $file;
			}
		}

		// file was never found
		return false;
	
	}

	/**
	 * Requires the given file if it exists
	 *
	 * @param string $file The file to require
	 * @return bool True if file exists, false if not. 
	 */
	private function requireFile($file)
	{
		if (file_exists($file)) {
			require $file;
			return true;
		}

		return false;
	}
}
