<?PHP

/* Filesystem module for the DNA Framework system
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */

class FileSystem {
	public static function RemoveDir($dir) {
		if ($handle = opendir($dir)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					if (is_dir($dir . "/" . $entry) === true) {
						self::RemoveDir($dir . "/" . $entry);
					} else {
						self::Remove($dir . "/" . $entry);
					}
				}
			}
			closedir($handle);
			rmdir($dir);
		}
	}

	public static function Remove($file) {
		if (file_exists($file) && is_file($file)) {
			return unlink($file);
		} else {
			return true;
		}
	}

	public static function CreateDirectory($path) {
		$components = explode('/', $path);

		$currentPath = '';

		$old_umask = umask(0);

		foreach ($components as $component) {
			$currentPath .= ($currentPath ? '/' : '') . $component;

			if (!file_exists($currentPath)) {
				mkdir($currentPath, 0775);
			}
		}

		umask($old_umask);
	}

	public static function Write($file, $data, $append = false) {
		if (!is_resource($file) && is_string($file)) {
			$fileHandle = fopen($file, file_exists($file) && $append ? 'a' : 'w');
		} else {
			$fileHandle = $file;
		}
		fwrite($fileHandle, $data);
		fclose($fileHandle);

		if (is_string($file)){
			chmod($file, 0775);
		}
	}

	public static function Read($file) {
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return null;
		}
	}

	public static function Copy($path, $destination, $permissions = 0775) {
		// Check for symlinks
		if (is_link($path)) {
			return symlink(readlink($path), $destination);
		}

		// Simple copy for a file
		if (is_file($path)) {
			return copy($path, $destination);
		}

		// Make destination directory
		if (!is_dir($destination)) {
			mkdir($destination, $permissions);
		}

		// Loop through the folder
		$dir = dir($path);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			self::Copy("$path/$entry", "$destination/$entry", $permissions);
		}

		// Clean up
		$dir->close();

		return true;
	}

	public static function Zip($path, $destination, $pathToReplace = '', $zip = null) {
		$isRootCall = false;
		if (!$zip) {
			$isRootCall = true;
			$zip        = new ZipArchive();
			$ret        = $zip->open($destination, ZipArchive::OVERWRITE);
			if ($ret !== true) {
				printf('Failed to create zip file with code %d', $ret);
			}

			$path          = trim($path, "/");
			$pathToReplace = trim($pathToReplace, "/");
		}

		// Simple copy for a file
		$finalPath = str_replace($pathToReplace, '', $path);
		$finalPath = ltrim($finalPath, "/");

		if (is_file($path)) {
			$zip->addFile($path, $finalPath);
		} else if (is_dir($path)) {
			if ($finalPath) {
				$zip->addEmptyDir($finalPath);
			}

			// Loop through the folder
			$dir = dir($path);
			while (false !== $entry = $dir->read()) {
				// Skip pointers
				if ($entry == '.' || $entry == '..') {
					continue;
				}

				// Deep copy directories
				self::Zip("$path/$entry", $destination, $pathToReplace, $zip);
			}

			// Clean up
			$dir->close();
		}

		if ($isRootCall) {
			$zip->close();
		}

		return true;
	}

	public static function Download($url, $saveTo) {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

		$raw = curl_exec($ch);
		curl_close($ch);

		if (file_exists($saveTo)) {
			unlink($saveTo);
		}

		$fp = fopen($saveTo, 'x');
		fwrite($fp, $raw);
		fclose($fp);
	}
}

?>