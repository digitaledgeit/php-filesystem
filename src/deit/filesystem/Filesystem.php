<?php

namespace deit\filesystem;

/**
 * Filesystem
 * @author James Newell <james@digitaledgeit.com.au>
 */
class Filesystem {

	/**
	 * Gets the system drive
	 * @return 	string
	 */
	public function getSystemDrive() {
		if (PHP_OS == 'WINNT') {
			return exec('echo %SystemDrive%');
		} else {
			return '/';
		}
	}

	/**
	 * Gets the relative path
	 * @param   string $path
	 * @param   string $context
	 * @return  string
	 */
	public function getRelativePath($path, $context) {
		$rParts 	= [];

		$path       = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$context    = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $context);

		$cToParts 	= explode(DIRECTORY_SEPARATOR, realpath($context));
		$cToPart 	= current($cToParts);

		$cThisParts	= explode(DIRECTORY_SEPARATOR, realpath($path));
		$cThisPart 	= current($cThisParts);

		//while parts match
		while ($cToPart == $cThisPart && $cToPart !== false) {
			$cToPart 	= next($cToParts);
			$cThisPart 	= next($cThisParts);
		}

		//while more to parts
		while ($cToPart !== false) {
			$rParts[] 	= '..';
			$cToPart 	= next($cToParts);
		}

		//while more this parts
		while ($cThisPart !== false) {
			$rParts[] 	= $cThisPart;
			$cThisPart 	= next($cThisParts);
		}

		return join(DIRECTORY_SEPARATOR, $rParts);
	}

	/**
	 * Gets whether the path exists on disk
	 * @param   string $path
	 * @return  bool
	 */
	public function exists($path) {
		return file_exists($path);
	}

	/**
	 * Creates the directory
	 * @param   string  $path
	 * @param   int     $perms
	 * @return  $this
	 * @throws  \RuntimeException
	 */
	public function mkdir($path, $perms = 0754) {
		if (!is_dir($path)) {
			if (!mkdir($path, $perms, true)) {
				throw new \RuntimeException("Unable to create directory \"$path\".");
			}
		}
		return $this;
	}

	/**
	 * Copies the source file or directory to the destination
	 * @param   string     $src    The source path(s)
	 * @param   string     $dest   The destination path
	 * @return  $this
	 * @throws
	 */
	public function copy($src, $dest) {

		if (is_dir($src)) {

			$this->mkdir($dest);

			$f = new Finder($src);
			$f->depth(1);
			foreach ($f as $path) {
				$this->copy($path->getPathname(), $dest.DIRECTORY_SEPARATOR.$this->getRelativePath($path->getPathname(), $src));
			}

			return $this;
		}

		if (file_exists($src)) {

			//if the destination is a folder then we'll create a new file with the same name as the source file
			if (is_dir($dest)) {
				$dest .= DIRECTORY_SEPARATOR.basename($src);
			}

			//check the parent folder exists
			$parent = dirname($dest);
			if (!is_dir($parent)) {
				throw new \Exception("Parent directory $parent does not exist");
			}

			if (!copy($src, $dest)) {
				throw new \RuntimeException("Unable to copy file \"$src\" to \"$dest\".");
			}

			return $this;

		} else {
			throw new \InvalidArgumentException("Path \"$src\" does not exist.");
		}

		return $this;
	}

	/**
	 * Moves the source file or directory to the destination
	 * @param   string     $src    The source path(s)
	 * @param   string     $dest   The destination path
	 * @return  $this
	 * @throws
	 */
	public function move($src, $dest) {

		if (is_dir($src)) {

			$f = new Finder($src);
			$f->depth(1);
			foreach ($f as $path) {
				$this->move($path->getPathname(), $dest.DIRECTORY_SEPARATOR.$this->getRelativePath($path->getPathname(), $src));
			}

			return $this;
		}

		if (file_exists($src)) {

			//if the destination is a folder then we'll create a new file with the same name as the source file
			if (is_dir($dest)) {
				$dest .= DIRECTORY_SEPARATOR.basename($src);
			}

			//check the parent folder exists
			$parent = dirname($dest);
			if (!is_dir($parent)) {
				throw new \Exception("Parent directory $parent does not exist");
			}

			if (!rename($src, $dest)) {
				throw new \RuntimeException("Unable to move file \"$src\" to \"$dest\".");
			}

			return $this;

		} else {
			throw new \InvalidArgumentException("Path \"$src\" does not exist.");
		}

		return $this;
	}

	/**
	 * Deletes the specified file or folder
	 * @param   string  $path
	 * @return  $this
	 * @throws
	 */
	public function remove($path) {
		$paths = (array) $path;

		if (is_dir($path)) {

			//remove the directory contents
			$it = new \DirectoryIterator($path);
			foreach ($it as $p) {
				if (!$p->isDot()) {
					$this->remove($p->getPathname());
				}
			}

			//remove the folder
			if (!rmdir($path)) {
				throw new \Exception(sprintf('Unable to delete folder "%s".', $path));
			}

		} else {

			//remove the file
			if (!unlink($path)) {
				throw new \Exception(sprintf('Unable to delete file "%s".', $path));
			}

		}

		return $this;
	}

}
 