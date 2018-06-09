<?php

namespace GenBankGen;

class TestSuite {
	public function __construct(){
	}

	public function run($method, $args, $expected) {
		$my_method = "_test_".$method;
		if (method_exists($this, $my_method)){
			$result = $this->$my_method($args);
			if ($result == $expected) {
				echo "[ ".$method." ] test passed [OK]".PHP_EOL;
				return true;
			}
			echo PHP_EOL."[ ".$method." ] {expected}:";
			print_r($expected);
			echo PHP_EOL."{result}:";
			print_r($result);
			echo PHP_EOL."[ ".$method." ] test FAILED".PHP_EOL.PHP_EOL;
			return false;
		}	
		echo "[ ".$method." ] Method does not exist.".PHP_EOL;
	}
}	

class myTests extends TestSuite {
	public function _new_controller($req, $res) {
		include_once "controller.php";
		return new ControllerTbl2asn($req, $res);
	}

	public function _test_test ($args) {
		return true;
	}

	public function _test_paths ($args) {
		// [ ] $req->storage_path + <user> + "GenBank"
		// [ ] get test user login: $req->user_data
		// [ ] $bin_path = __FILE__."/bin/tbl2asn.linux.bin
		// [ ] unless `which tbl2asn` exists
		$req = (object) array();
		$res = (object) array();
		$c = $this->_new_controller($req, $res);
		return $c->run();
	}

	public function _test_request ($args) {
		$req = json_decode(file_get_contents("controller.test.data/sample_request.json"));
		$res = json_decode(file_get_contents("controller.test.data/sample_response.json"));
		$data = json_decode(file_get_contents("controller.test.data/sample_data.json"));
		$c = $this->_new_controller($req, $res);
		$c->Data = $data;
		return $c->run();
	}
}

$tests = new myTests();
// $test_01 = $tests->run("test", array(), true);
// $test_02 = $tests->run("paths", array(), false);
$test_03 = $tests->run("request", array(), false);

