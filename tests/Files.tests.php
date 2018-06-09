<?php
namespace GenBankGen;

$base = dirname(dirname(__FILE__));

include_once $base."/lib/TestSuite.class.php";

class myTests extends TestSuite {
	public function _new_controller() {
		include_once $this->BASE."/lib/Files.class.php";
		return new Files();
	}

	public function _test_test ($args) {
		return true;
	}

	public function _test_error ($args) {
    $fs = $this->_new_controller();
		return $fs->error($args);
	}

  public function _test_scandir($args) {
    $fs = $this->_new_controller();
    $listing = $fs->scandir($args);
    return basename($listing[0]->children[0]->children[0]->path);     // Return GenBank
  }

  public function _test_mkdir($args) {
    $fs = $this->_new_controller($args);
    return $fs->mkdir($args);
  }

  public function _test_rmdir($args) {
    $fs = $this->_new_controller($args);
    return $fs->rmdir($args);
  }

  public function _test_write($args) {
    $fs = $this->_new_controller($args);
    return $fs->write($args);
  }

  public function _test_read($args) {
    $fs = $this->_new_controller($args);
    return $fs->read($args);
  }

  public function _test_info($args) {
    $fs = $this->_new_controller($args);
    return $fs->info($args);
  }

  public function _test_rename($args) {
    $fs = $this->_new_controller($args);
    return $fs->rename($args);
  }

  public function _test_delete($args) {
    $fs = $this->_new_controller($args);
    return $fs->delete($args);
  }

  public function _test_cleanup($args) {
    $args = (object) $args;
    $fs = $this->_new_controller($args);
    foreach($args->paths as $path) {
        $full = $args->path.DIRECTORY_SEPARATOR.$path;
        if (is_dir($full)) {
            $fs->rmdir(array("path" => $full));
        }
    }
  }
}

$tests = new myTests();
$tests->BASE = $base;
$test_01 = $tests->run("test", array(), true);
$test_02 = $tests->run("error", array("msg" => "Error message returned."), "Error message returned.");
$test_03 = $tests->run("scandir", array("path" => $base."/files", "levels" => 2, "msg" => "Directory NOT found."), "GenBank");
// $test_04 = $tests->run("mkdir", array("path" => $base."/files/test/1", "recursive" => true, "msg" => "Cannot create directory."), "Directory already exists.");
// $test_05 = $tests->run("rmdir", array("path" => $base."/files/test", "msg" => "Directory NOT found."), "Removed.");
// $test_06 = $tests->run("write", array("path" => $base."/files/test/1/test.json", "content" => json_encode($tests), "create_dirs" => true, "method" => false, "msg" => "File does not exist." ), "File created.");
// $test_07 = $tests->run("read", array("path" => $base."/files/test/1/test.json", "msg" => "File does not exist." ), true);
// $test_08 = $tests->run("info", array("path" => $base."/files/test/1/test.json", "msg" => "File does not exist." ), true);
// $test_09 = $tests->run("rename", array("path" => $base."/files/test/1/test.json", "destination" => $base."/files/test/2/test_again.json", "msg" => "File does not exist." ), true);
$test_10 = $tests->run("delete", array("path" => $base."/files/test/2/test_again.json", "msg" => "File does not exist." ), false);
$test_11 = $tests->run("cleanup", array("path" => $base."/files", "paths" => array("test/1", "test/2", "test"), "msg" => "File does not exist." ), false);


/*
    class Files
        [*] error (msg)
        [*] scandir (path, [recursive levels])
        [*] mkdir (path, [0|1])   // strict or recursive
        [*] rmdir (path)
        [+] write (path, content, [create_dirs[false|true]], [false|overwrite|append)
        [+] read (path, [bytes])
        [*] info (path)
        [*] rename (path, destination)
        [*] delete (path)
*/
