<?php

namespace deit\filesystem;

/**
 * Finder iterator
 * @author James Newell <james@digitaledgeit.com.au>
 */
class FinderIterator implements \Iterator, \Countable {

	/**
	 * The path to search in
	 * @var     string
	 */
	private $path;

	/**
	 * The iterator
	 * @var     \Iterator
	 */
	private $iterator;

	/**
	 * Constructs the iterator
	 * @param   string          $path           The folder path
	 * @param   int             $depth          The depth to recourse
	 * @param   callable[]      $filters        An array of filters used to restrict the resulting list of files or folders
	 */
	public function __construct($path, $depth = null, array $filters = array()) {

		//TODO: verify path exists and filters are callable
		$this->path = (string) $path;

		//create the iterator
		$flags = \FilesystemIterator::SKIP_DOTS;

		if ($depth === 0) {
			$this->iterator = new \EmptyIterator();
		} else if ($depth === 1) {
			$this->iterator = new \DirectoryIterator($path);
			$this->iterator = new \CallbackFilterIterator($this->iterator, function($path) {
				return !$path->isDot();
			});
		} else {
			$this->iterator = new \RecursiveDirectoryIterator($path);
			$this->iterator->setFlags($flags);
			$this->iterator = new \RecursiveIteratorIterator($this->iterator, \RecursiveIteratorIterator::SELF_FIRST);
			if ($depth) $this->iterator->setMaxDepth($depth - 1);
		}

		//decorate iterator with filters
		if (count($filters)) {
			$this->iterator = new \CallbackFilterIterator($this->iterator, function($path) use($filters) {
				foreach ($filters as $filter) {
					if (!$filter($path)) {
						return false;
					}
				}
				return true;
			});
		}

	}

	/**
	 * Gets the path
	 * @return  string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @inheritdoc
	 */
	public function current() {
		return $this->iterator->current();
	}

	/**
	 * @inheritdoc
	 */
	public function next() {
		return $this->iterator->next();
	}

	/**
	 * @inheritdoc
	 */
	public function key() {
		return $this->iterator->key();
	}

	/**
	 * @inheritdoc
	 */
	public function valid() {
		return $this->iterator->valid();
	}

	/**
	 * @inheritdoc
	 */
	public function rewind() {
		return $this->iterator->rewind();
	}

	/**
	 * @inheritdoc
	 */
	public function count() {
		return iterator_count($this->iterator);
	}

}