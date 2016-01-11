<?php

namespace deit\filesystem;

/**
 * Finder test
 * @author James Newell <james@digitaledgeit.com.au>
 */

$total_files    = 5654;
$total_js_files = 682;
$total_folders  = 879;

class FinderTest extends \PHPUnit_Framework_TestCase {

	public function test_files() {

		$finder = new Finder('tests/test-data/bigtree');
		foreach ($finder->files() as $path) {
			if (!$path->isFile()) {
				$this->fail("Path {$path->getPathname()} is not a file");
			}
		}

	}

	public function test_files_named() {

		global $total_files;

		$finder = new Finder('tests/test-data/bigtree');

		$this->assertEquals($total_files, count($finder->files()));

		foreach ($finder->files()->named('#\.json$#') as $path) {
			if (!$path->isFile() || $path->getExtension() != 'json') {
				$this->fail("Path {$path->getPathname()} is not a JSON file");
			}
		}

	}

	public function test_folders() {

		global $total_folders;

		$finder = new Finder('tests/test-data/bigtree');

		$this->assertEquals($total_folders, count($finder->folders()));

		foreach ($finder->folders() as $path) {
			if (!$path->isDir()) {
				$this->fail("Path {$path->getPathname()} is not a folder");
			}
		}

	}

	public function test_copy() {

		$finder = new Finder('tests/test-data/bigtree');
		$finder
			->files()
			->named('#\.json#')
			->copyTo('tests/test-data-copy')
		;

	}

}
