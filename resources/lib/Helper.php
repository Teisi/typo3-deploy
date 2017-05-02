<?php
error_reporting(E_ALL);
/**
 * [__construct description]
 */
class Helper {

  function __construct() {

  }

  /**
   * return the path to this class (helper.php) file
   * @return (string)
   */
  public function getClassPath() {
    return dirname((new ReflectionClass(static::class))->getFileName());
  }

  /**
   * return the path to the parent class file
   * @return (string)
   */
  public function getParentClassPath() {
    return dirname((new ReflectionClass(static::class))->getFileName()).DIRECTORY_SEPARATOR;
  }

  /**
   * deletes a file
   * @param (string) $filename - path to file incl. filename
   * @return (bool)
   */
  public function deleteFile($filename) {
    $filename = $this->escape_input($filename);
    $filename = $filename[0];

    $fileType = filetype($filename);

    switch($fileType) {
      case "file":
        unlink($filename);
        echo "<span class='successful'>File: {$filename} successfully deleted!</span>";
        return true;
        break;
      default:
        echo "<span class='error'>File: {$filename} is filetype {$filetype}</span>";
        if(file_exists($filename)) {
          return false;
        }
        return true;
    }
  }

  /**
   * deletes a folder recursive
   * @param (string) $src - path to dir incl. dirname
   * @return (bool)
   */
  public function deleteDir($src) {
    $filename = $this->escape_input($filename);
    $filename = $filename[0];

    $fileType = filetype($filename);

    switch ($fileType) {
      case 'dir':
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if(($file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if(is_dir($full)) {
                    $this->rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        if(rmdir($src)) {
          echo "<span class='successful'>Dir {$src} successfully deleted.</span>";
          return true;
        } else {
          echo "<span class='error'>Dir {$src} not deleted!</span>";
          return false;
        }
        break;
      default:
        echo "<span class='error'>File: {$filename} is filetype {$filetype}</span>";
        return false;
        break;
    }
  }

  /**
   * deletes a symlink file
   * @param (string) $filename - path to symlink incl. symlinkname
   * @return (bool)
   */
  public function deleteLink($filename) {
    $filename = $this->escape_input($filename);
    $filename = $filename[0];

    $fileType = filetype($filename);

    switch($fileType) {
      case "link":
        unlink($filename);
        echo "<span class='successful'>Link: {$filename} successfully deleted!</span>";
        return true;
        break;
      default:
        if(file_exists($filename)) {
          echo "<span class='error'>Link: {$filename} is filetype {$filetype}! File is not deleted!</span>";
          return false;
        } else {
          echo "<span class='success'>Link: {$filename} doesn't exists!</span>";
          return true;
        }
    }
  }

  /**
   * creates a symlink
   * @param (string) $filename - name of the symlink
   * @param (string) $target - path to the file where the symlink links to
   * @return (bool)
   */
  public function createSymlink($filename, $target) {
    $filename = $this->escape_input($filename);
    $filename = $filename[0];
    $target = $this->escape_input($target);
    $target = $target[0];

    if($this->deleteLink($filename) && symlink($target, $filename)) {
      echo "<span class='successful'>Link: {$filename} successfully created.</span>";
      return true;
    } else {
      echo "<span class='error'>Link: {$filename} not created!</span>";
      return false;
    }
  }

  /**
   * creates a folder(dir)
   * @param (string) $dirName - name of the folder without slashes
   * @param (string) $pathToDir - path where the folder creates with ending slash
   * @return (bool)
   */
  public function createDir($dirName, $pathToDir = false) {
    $pathToDir = $pathToDir ? $pathToDir : "./";

    $pathToDir = $this->escape_input($pathToDir);
    $pathToDir = $pathToDir[0];
    $dirName = $this->escape_input($dirName);
    $dirName = $dirName[0];

    if(!dir($pathToDir.$dirName)){
      if(mkdir($pathToDir.$dirName)) {
        echo "<span class='successful'>Folder: {$dirName} successfully created.</span>";
        return true;
      } else {
        echo "<span class='error'>Folder: {$dirName} can't created!</span>";
        return false;
      }
    } else {
      echo "<span class='warning'>Folder: {$dirName} in {$pathToDir} already exists!</span>";
      return true;
    }
  }

  /**
   * downloadExternalFile() - downloads a file from an external source
   * @param (string) $pathToExternalFile - path to external file (url) without filename
   * @param (string) $filename - name of the external file
   * @param (string) $pathToSafeFile - optional - path where to safe the file, with ending slash
   * @return (bool)
   */
  public function downloadExternalFile($pathToExternalFile, $filename, $pathToSafeFile = false) {
    if(!$pathToSafeFile){
      if($this->createDir("typo3_sources", "../../")) {
        $pathToSafeFile = "../../typo3_sources/";
      }
    } else {
      $pathToSafeFile = $pathToSafeFile;
    }

    //$pathToExternalFile = $this->escape_input($pathToExternalFile);
    //$pathToExternalFile = $pathToExternalFile[0];
    //$filename = $this->escape_input($filename);
    //$filename = $filename[0];
    //$pathToSafeFile = $this->escape_input($pathToSafeFile);
    //$pathToSafeFile = $pathToSafeFile[0];

    echo $pathToSafeFile.$filename;

    if (file_exists($pathToSafeFile.$filename)) {
      echo "<span class='warning'>File: {$filename} already exists in {$pathToSafeFile}.</span>";
      return true;
    } else {
      echo "<span class=''>File: {$filename} try to download to {$pathToSafeFile}.</span>";
      $newfname = $pathToSafeFile.$filename;
      $file = fopen($pathToExternalFile, 'rb');
      if($file) {
          $newf = fopen($newfname, 'wb');
          if($newf) {
              while(!feof($file)) {
                  fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
              }
          }
      }
      if ($file) {
        fclose($file);
      }
      if ($newf) {
        fclose($newf);
      }
      if(file_exists($pathToSafeFile.$filename)) {
        echo "<span class='successful'>File: {$filename} successfully downloaded to '{$pathToSafeFile}{$filename}'!</span>";
        return true;
      } else {
        echo "<span class='error'>File: {$pathToSafeFile}{$filename} can't downloaded from '{$pathToExternalFile}'!</span>";
        return false;
      }
    }
  }

  /**
   * extracts a zip file
   * @param  [string] $pathToZipFile - path to zip file incl. zip file name
   * @param  [string] $pathToExtract - path where to extract the zip file
   * @return [bool]
   */
  public function extractZipFile($pathToZipFile, $pathToExtract = false) {
    $pathToZipFile = $this->escape_input($pathToZipFile);
    $pathToZipFile = $pathToZipFile[0];
    $pathToExtract = $this->escape_input($pathToExtract);
    $pathToExtract = $pathToExtract[0];

    $pathToExtract = $pathToExtract ? $pathToExtract : "../../typo3_sources/";

    if (file_exists($pathToZipFile)) {
      $phar = new PharData($pathToZipFile);
      if($phar->extractTo($pathToExtract)) {
        echo "<span class='successful'>ZipFile: {$pathToZipFile} successfully extracted!</span>";
        return true;
      } else {
        echo "<span class='error'>ZipFile: {$pathToZipFile} not extracted! ZipFile corrupt?</span>";
        return false;
      }
    } else {
      echo "<span class='error'>ZipFile: {$pathToZipFile} not extracted! File dosen't exist</span>";
      return false;
    }
  }

  /**
   * escape's a given string or array
   * @param  [string or array] $data - array to escape
   * @return [array] - returns the escaped array
   */
  public function escape_input($data) {
    $tmpArray = is_array($data) ? $data : array($data);
    foreach ($tmpArray as &$arr) {
      $tmp_str_replace_orig = array('"', "'", "<", ">", " ");
      $tmp_str_replace_target = array('', "", "", "", "");
      $arr = str_replace($tmp_str_replace_orig, $tmp_str_replace_target, htmlspecialchars(stripslashes(trim($arr))));
    }

    return $tmpArray;
  }

  /**
   * [getDirList description]
   * @param  (string) $t3_sources_dir [description]
   * @return [type]        [description]
   */
  public function getDirList($t3_sources_dir = false) {
    $listdir = $t3_sources_dir ? dir($t3_sources_dir) : dir(dirname(__FILE__) . DIRECTORY_SEPARATOR ."..".DIRECTORY_SEPARATOR ."..".DIRECTORY_SEPARATOR ."typo3_sources".DIRECTORY_SEPARATOR);
    echo "listdir: ".$listdir;
    echo "<br />".dirname(__FILE__);
    echo "<ul class='dirlist'>";
    while(($fl = $listdir->read()) != false) {
        if($fl != "." && $fl != "..") {
           echo "<li>".$fl."</li>";
        }
    }
    $listdir->close();
    echo "</ul>";
  }
}
