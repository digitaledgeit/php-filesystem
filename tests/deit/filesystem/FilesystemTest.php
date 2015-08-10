<?php

namespace deit\filesystem;

/**
 * Filesystem test
 * @author Greg Bell <greg@bitwombat.com.au>
 */

class FilesystemTest extends \PHPUnit_Framework_TestCase {

	public function rmdir_recursive($dir) {
		foreach(scandir($dir) as $file) {
			if ('.' === $file || '..' === $file) continue;
			if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
			else unlink("$dir/$file");
		}
		rmdir($dir);
	}

	public function test_symlink_removal() {

		$test_dir = 'tests/test-data/symlinks';
		$test_link = $test_dir . '/points_nowhere';

		if ( is_dir($test_dir) ) {
			$this->rmdir_recursive( $test_dir );
		}
		mkdir( $test_dir );
		symlink( "does_not_exist", $test_link );

		$fs = new Filesystem();
		$fs->remove( $test_dir );
		$this->assertFalse ( is_link($test_link) || is_file($test_link) );

	}
}
