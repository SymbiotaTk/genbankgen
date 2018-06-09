<?php
namespace GenBankGen;

class Plugin {
    public function __construct($req) {
        $this->Request = $req;
        $this->Response = (object) array();

        $this->charset = "utf-8";
        $this->auth = $this->Request->get("auth_engine");
        $this->auth_msg = "You must be logged in to access this information.";

        $this->required_libs = array(
            array(
                "type"  => "lib",
                "name"  => "EmbeddedFileManager",
                "rpath" => "/lib/EmbeddedFileManager.class.php",
            ),
            array(
                "type"  => "pkg",
                "name"  => "Tbl2asn",
                "rpath" => "/elements/tbl2asn.pkg",
            ),
            array(
                "type"  => "pkg",
                "name"  => "Form",
                "rpath" => "/elements/form.pkg",
            ),
            array(
                "type"  => "pkg",
                "name"  => "SymbiotaAuth",
                "rpath" => "/elements/symbiota_auth.pkg",
            ),
            array(
                "type"  => "pkg",
                "name"  => "SymbiotaOccurrenceData",
                "rpath" => "/elements/symbiota_occurrence_data.pkg",
            ),
        );
        $this->_register_libs();
    }

    private function _register_libs() {
        $plugin_path = $this->Request->get("plugin_path");

        foreach ($this->required_libs as $obj) {
            $obj = (object) $obj;
            switch ($obj->type) {
                case "lib":
                    $label = "lib_".$obj->name;
                    $this->$label = $plugin_path.$obj->rpath;
                    break;
                case "pkg":
                    $label = "pkg_".$obj->name;
                    $this->$label = $plugin_path.$obj->rpath."/controller.php";
                    break;
                default:
                    break;
            }
        }
    }

    public function setHeaderPlain() {
        header("Content-Type: text/plain; charset=".$this->charset);
    }

    public function setHeaderHtml() {
        header("Content-Type: text/html; charset=".$this->charset);
    }

    public function _error($msg) {
        $this->_exit($msg);
    }

    private function _exit($msg) {
        echo $msg;
        // exit;
    }

    private function _hasMethod() {
        $allowed_methods = $this->Request->get("allowed_methods");

        if (!in_array($this->command, array_keys($allowed_methods))) {
            $this->_error("ERROR: access to this method is not allowed. ($this->command)");
        }
    }

    private function _method() {
	$command = "_".$this->command;

        if(method_exists(get_called_class(), $command))
        {
                $this->_hasMethod();
                $this->{$command}();
                return 1;
        }
        $this->_error("ERROR: method does not exist. ($this->command)");
    }

    public function run() {
        if (isset($_GET['c'])) {
            $this->command = $_GET['c'];
            $this->_method();
        }
        if (isset($_POST['c'])) {
            $this->command = $_POST['c'];
            $this->_method();
        }
    }

    public function isLoggedIn($quiet=false) {
        $uid = 0;
        if (isset($this->Request->SYMB_UID)) { $uid = $this->Request->SYMB_UID; }
        $this->Response->loggedin = $this->auth->isLoggedIn($uid);
        if (! $this->Response->loggedin) {
            $msg = $this->auth->error_msg;
            if ($quiet) { $msg = ""; }
            return $this->_error($msg);
        }
        return $this->Response->loggedin;
    }

    private function _getCurrentUser() {
        if (!isset($this->Response->loggedin)) {
          $this->isLoggedIn();
        }
        return $this->Response->loggedin;
    }

    private function _test() {
        $this->setHeaderHtml();
        if ($this->isLoggedIn()) {
            echo var_dump($this->Response->loggedin);
            echo var_dump($this->_getCurrentUser());
            return 1;
        }
        $this->_error($this->auth_msg);
    }

    private function _user() {
        $valid_id_msg = "ERROR: Please provide a valid user 'id'.";
        $this->setHeaderPlain();
        if (! $this->isLoggedIn()) { return $this->_error($this->auth_msg); }
        if (isset($_GET['id'])) {
          $uid = (int) $_GET['id'];
          $data = $this->Request->user_data->getById($uid);
          if ($data) {
            echo json_encode($data);
            return 1;
          }
        }
        return $this->_error($valid_id_msg);
    }

    private function _object() {
        $valid_id_msg = "ERROR: Please provide a valid object (occid) 'id'.";
        $this->setHeaderPlain();
        if (! $this->isLoggedIn()) { return $this->_error($this->auth_msg); }
        if (isset($_GET['id'])) {
          $oid = $_GET['id'];
          $data = $this->Request->object_data->getById($oid);
          if ($data) {
              echo json_encode($data);
              return 1;
          }
        }
        return $this->_error($valid_id_msg);
    }

    private function _display() {
        if (! $this->isLoggedIn()) { return $this->_error($this->auth_msg); }
        $loggedin = (array) $this->Response->loggedin;
        $this->Response->currentUser = $loggedin['username'];
        $this->Response->currentUserId = $loggedin['uid'];
        $this->Response->currentOccid = 2751741;

        include_once $this->pkg_Form;
        $d = new ControllerForm($this->Request, $this->Response);
        echo $d->view();
    }

    public function embed() {
        if (! $this->isLoggedIn(true)) { return $this->_error(""); }
        $loggedin = (array) $this->Response->loggedin;
        $this->Response->currentUser = $loggedin['username'];
        $this->Response->currentUserId = $loggedin['uid'];
        $this->Response->currentOccid = $_GET['occid'];

        include_once $this->pkg_Form;
        $d = new ControllerForm($this->Request, $this->Response);
        return $d->view();
    }

    private function _create() {
        $this->setHeaderPlain();

        if (isset($_POST['data'])) {
          include_once $this->pkg_Tbl2asn;
          $c = new ControllerTbl2asn($this->Request, $this->Response);
          $error = $c->run();

          if ($error) {
              echo "There was an error generating files.";
              return true;
          }

          echo "Your files have been generated.";

          exit;
          // return 1;
        }
        $this->_error("ERROR: POST response: requires->uid, occid, sequence, options.");
    }

    private function _view() {
        $this->setHeaderPlain();
        if (! $this->isLoggedIn()) { return $this->_error($this->auth_msg); }

        include_once $this->lib_EmbeddedFileManager;
        $c = new EmbeddedFileManager($this->Request, $this->Reponse);
        echo $c->render();
        return 1;
    }

    private function _fm() {
        $this->setHeaderHtml();
        if (! $this->isLoggedIn()) { return $this->_error($this->auth_msg); }

        $this->Request->set("base_query", "c=fm");

        include_once $this->lib_EmbeddedFileManager;
        $c = new EmbeddedFileManager($this->Request, $this->Response);
        echo $c->render();
        return 1;
    }

    private function _dir_list($dir, $bool = "dirs", $func = NULL) {
        if (is_dir($dir)) {
            $truedir = $dir;
            $dir = scandir($dir);
            if($bool == "files"){ // dynamic function based on second pram
                $direct = 'is_dir';
            }elseif($bool == "dirs"){
                $direct = 'is_file';
            }
            foreach($dir as $k => $v){
                if(($direct($truedir.$dir[$k])) || $dir[$k] == '.' || $dir[$k] == '..' ){
                    unset($dir[$k]);
                } else {
                    if (is_callable($func)) {
                        $dir[$k] = $func($v);
                    }
                }
            }
            $dir = array_values($dir);
            return $dir;
        }
        return array();
    }

    private function _fm_entries() {
        $this->setHeaderPlain();
        if (! $this->isLoggedIn()) { return $this->_error($this->auth_msg); }

        $loggedin = (array) $this->Response->loggedin;
        $format = function($v) { return urlencode("GenBank/".$v); };
        $dir_list = $this->_dir_list($this->Request->get("storage_path")."/".$loggedin['username']."/GenBank", "dirs", $format);
        echo json_encode($dir_list);
        return 1;
    }
}
