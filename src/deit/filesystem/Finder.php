<?php

namespace deit\filesystem;
use Traversable;

/**
 * Finder
 * @author James Newell <james@digitaledgeit.com.au>
 */
class Finder implements \IteratorAggregate {

	const TYPE_FILE     = 'files';
	const TYPE_FOLDER   = 'folders';

	/**
	 * The path to search in
	 * @var     string
	 */
	private $path;

	/**
	 * An array of filters used to restrict the resulting list of files or folders
	 * @var     callable[string]
	 */
	private $filters = [];

	private $type;


	private $named = [];

	/**
	 * The depth
	 * @var     int
	 */
	private $depth;

	/**
	 * Constructs the finder
	 * @param   string      $path   The path(s) to search in
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * Adds a filter to restrict the resulting list of files or folders
	 * @param   callable    $filter
	 * @return  $this
	 */
	public function filter(callable $filter) {
		$this->filters[] = $filter;
		return $this;
	}

	/**
	 * Restricts the results to only contain files
	 * @return  $this
	 * @throws
	 */
	public function files() {

		if (!empty($this->type)) {
			throw new \InvalidArgumentException("Results have already limited to {$this->type}.");
		}

		$this->type = self::TYPE_FILE;
		return $this;
	}

	/**
	 * Restricts the results to only contain folders
	 * @return  $this
	 * @throws
	 */
	public function folders() {

		if (!empty($this->type)) {
			throw new \InvalidArgumentException("Results have already limited to {$this->type}.");
		}

		$this->type = self::TYPE_FOLDER;
		return $this;
	}

	/**
	 * Restricts the results to only contain files matching the specified pattern
	 * @param   string $pattern
	 * @return  $this
	 */
	public function named($pattern) {
		$this->named[] = $pattern;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator() {

		//filter out parent directories
		$this->filter(function($path /** @var \SplFileInfo $path */) {
			return $path->getFilename() !== '..';
		});

		//filter by path type
		if ($this->type) {
			$type = $this->type;
			$this->filter(function($path /** @var \SplFileInfo $path */) use($type) {
				if ($type == self::TYPE_FILE) {
					return $path->isFile();
				} else if ($type == self::TYPE_FOLDER) {
					return $path->isDir();
				} else {
					return false;
				}
			});
		}

		//filter by name
		if (count($this->named)) {
			$named = $this->named;
			$this->filter(function($path /** @var \SplFileInfo $path */) use ($named) {
				foreach ($named as $name) {
					if (preg_match($name, $path->getFilename()) > 0) {
						return true;
					}
				}
				return false;
			});
		}

		return new FinderIterator($this->path, $this->filters);
	}

	/**
	 * Copies the files and folders to the destination folder
	 * @param   string $dest The destination directory
	 * @return  $this
	 * @throws
	 */
	public function copyTo($dest) {
		$fs = new Filesystem();

		//check the destination directory exists
		if (!is_dir($dest)) {
			throw new \RuntimeException("Destination directory \"$dest\" does not exist.");
		}

		foreach ($this->getIterator() as $srcPath) {

			//create the destination path
			$destPath = $dest.DIRECTORY_SEPARATOR.$fs->getRelativePath($srcPath, $this->path);

			//create the parent folder in case it hasn't already been created
			$fs->mkdir(dirname($destPath));

			//copy the file/folder to the destination
			$fs->copy($srcPath, $destPath);

		}

		return $this;
	}

}
 