<?php

namespace deit\filesystem;

/**
 * Finder test
 * @author James Newell <james@digitaledgeit.com.au>
 */
class FinderTest extends \PHPUnit_Framework_TestCase {

	public function test_files() {

		$finder = new Finder('../../../');
		foreach ($finder->files() as $path) {
			if (!$path->isFile()) {
				$this->fail("Path {$path->getPathname()} is not a file");
			}
		}

	}

	public function test_files_named() {

		$finder = new Finder('../../../');
		foreach ($finder->files()->named('#\.json#') as $path) {
			if (!$path->isFile() || $path->getExtension() != 'json') {
				$this->fail("Path {$path->getPathname()} is not a JSON file");
			}
		}

	}

	public function test_folders() {

		$finder = new Finder('../../../');
		foreach ($finder->folders() as $path) {
			if (!$path->isDir()) {
				$this->fail("Path {$path->getPathname()} is not a folder");
			}
		}

	}

	public function test_copy() {

		$fs = new Filesystem();

		$f = new Finder('../../../');
		$f
			->files()
			->named('#\.json#')
			->copyTo('/Users/james/tmp/test')
		;

	}

}
