<?php
namespace GenBankGen;

class ControllerSymbiotaAuth {
    public function __construct($req) {
        $this->Request = $req;
        $this->ini = $this->Request->get('base_path')."/config/symbini.php";

        $this->required_libs = array(
            $this->Request->get('base_path')."/config/dbconnection.php",
            $this->Request->get('base_path')."/classes/ProfileManager.php",
            $this->Request->get('base_path')."/classes/PermissionsManager.php",
        );
    }

    private function _setUid($id) {
	if ($id != 0) {
            $this->SYMB_UID = $id;
	}
    }

    private function _check_cookie() {
        if (isset($_COOKIE['SymbiotaBase'])){
            parse_str($_COOKIE['SymbiotaBase'],$param_arr);
            if (isset($param_arr['uid'])) {
                $this->_setUid($param_arr['uid']);
            }
        }
    }

    private function _check_global($SYMB_UID) {
    	if (isset($SYMB_UID)) {
    	    if ($SYMB_UID != 0) {
    		    $this->SYMB_UID = $SYMB_UID;
    	    }
    	}
    }

    private function _load_ini() {
        $config = $this->ini;
        $this->_check_cookie();

        if (is_file($config)) {
            ob_start();
            include $config;
            ob_end_clean();
            $this->_check_global($SYMB_UID);
        }
    }

    private function _load_libs() {
        include $this->ini;

        if(!isset($CLIENT_ROOT) && isset($clientRoot)) $CLIENT_ROOT = $clientRoot;
        if(!isset($SERVER_ROOT) && isset($serverRoot)) $SERVER_ROOT = $serverRoot;

        foreach($this->required_libs as $lib) {
            if (file_exists($lib)) {
                include_once $lib;
            }
        }
    }

    private function _has_filter() {
        if (count($this->Request->get('auth_filter')) == 0) { return false; }
        if (!in_array($this->loggedin['username'], $this->Request->get('auth_filter'))) {
            $this->error_msg = "You do not have permission to access this information.";
            return true;
        }
        return false;
    }

    private function _check_permissions($uid) {
        $this->loggedin = $this->_get_user($uid);
        if($this->_has_filter()){ return false; }
        return $this->loggedin;
    }

    public function isLoggedIn ($uid=0) {
        if ($uid != 0) {
            return $this->_check_permissions($uid); 
        }

        // attempt to check loggin
        $this->_load_ini();
        $this->_load_libs();
        if (! isset($this->SYMB_UID)) {
            $this->error_msg = "You must be logged in to access this information.";
        }
        return $this->_check_permissions($this->SYMB_UID);
    }

    private function _get_user($uid) {
        $this->_load_libs();
        $userId = $uid;
        if(!is_numeric($userId)) $userId = 0;

        $pHandler = new \ProfileManager();
        $pHandler->setUid($userId);
        $person = $pHandler->getPerson();

        $um = new \PermissionsManager();
        $user = $um->getUser($userId);

        if (method_exists($person, "getMiddleInitial")) {
            $user['middleinitial'] = $person->getMiddleInitial();
        }
        $user['department'] = $person->getDepartment();
        $user['address']    = $person->getAddress();
        $user['phone']      = $person->getPhone();

        return $user;
    }

    public function getById($id) {
        return $this->_get_user($id);
    }
}
