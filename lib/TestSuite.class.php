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

/*
class myTests extends TestSuite {
	public function _new_controller($req, $res) {
		include_once "controller.php";
		return new ControllerTbl2asn($req, $res);
	}

	public function _test_test ($args) {
		return true;
	}
}

$tests = new myTests();
$test_01 = $tests->run("test", array(), true);
*/
