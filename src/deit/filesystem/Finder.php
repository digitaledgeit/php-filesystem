<?php

namespace deit\filesystem;
use Traversable;

/**
 * Finder
 * @author James Newell <james@digitaledgeit.com.au>
 */
class Finder implements \IteratorAggregate, \Countable {

	const TYPE_FILE     = 'files';
	const TYPE_FOLDER   = 'folders';

	/**
	 * The path to search in
	 * @var     string
	 */
	private $path;

	/**
	 * The recursive depth
	 * @var     int
	 */
	private $depth;

	/**
	 * An array of filters used to restrict the resulting list of files or folders
	 * @var     callable[string]
	 */
	private $filters        = [];
	private $filterByType   = [];
	private $filterByName   = [];

	/**
	 * Constructs the finder
	 * @param   string      $path   The path to search
	 * @throws
	 */
	public function __construct($path) {

		//check if the path exists
		if (!is_dir($path)) {
			throw new \InvalidArgumentException("Path \"$path\" does not exist or is not a folder");
		}

		$this->path = $path;
	}

	/**
	 * Restricts the depth of the iterator
	 * @param   int $depth
	 * @return  $this
	 */
	public function depth($depth) {
		$this->depth = (int) $depth;
		return $this;
	}

	/**
	 * Restricts the results to only contain files matching the specified pattern
	 * @param   string $pattern
	 * @return  $this
	 */
	public function named($pattern) {
		$this->filterByName[] = $pattern;
		return $this;
	}

	/**
	 * Restricts the results to only contain files
	 * @return  $this
	 * @throws
	 */
	public function files() {
		$this->filterByType[] = self::TYPE_FILE;
		return $this;
	}

	/**
	 * Restricts the results to only contain folders
	 * @return  $this
	 * @throws
	 */
	public function folders() {
		$this->filterByType[] = self::TYPE_FOLDER;
		return $this;
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

			if ($srcPath->isDir()) {
				$fs->mkdir($destPath);
			} else {
				$fs->copy($srcPath, $destPath);
			}

		}

		return $this;
	}

	/**
	 * Deletes the files and folders
	 * @return  $this
	 */
	public function remove() {
		$fs = new Filesystem();

		foreach ($this->getIterator() as $srcPath) {
			$fs->remove($srcPath);
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator() {

		//filter by path type
		if ($this->filterByType) {
			$types = $this->filterByType;
			$this->filter(function($path /** @var \SplFileInfo $path */) use($types) {

				if (in_array(self::TYPE_FILE, $types) && $path->isFile()) {
					return true;
				}

				if (in_array(self::TYPE_FOLDER, $types) && $path->isDir()) {
					return true;
				}

				return false;
			});
		}

		//filter by name
		if (count($this->filterByName)) {
			$named = $this->filterByName;
			$this->filter(function($path /** @var \SplFileInfo $path */) use ($named) {
				foreach ($named as $name) {
					if (preg_match($name, $path->getFilename()) > 0) {
						return true;
					}
				}
				return false;
			});
		}

		return new FinderIterator($this->path, $this->depth, $this->filters);
	}

	/**
	 * @inheritdoc
	 */
	public function count() {
		return $this->getIterator()->count();
	}

}
 