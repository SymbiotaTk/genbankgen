<?php
namespace GenBankGen;

class ControllerSymbiotaOccurrenceData {
    public function __construct($req) {
        $this->Request = $req;
        $this->db_conf = $this->Request->get('base_path')."/config/dbconnection.php";
        $this->ini = $this->Request->get('base_path')."/config/symbini.php";
        $this->required_libs = array(
            $this->Request->get('base_path')."/classes/OccurrenceIndividualManager.php",
        );
}

    private function _load_libs() {
        include $this->ini;
        if(!isset($CLIENT_ROOT) && isset($clientRoot)) $CLIENT_ROOT = $clientRoot;
        if(!isset($SERVER_ROOT) && isset($serverRoot)) $SERVER_ROOT = $serverRoot;

        foreach($this->required_libs as $lib) {
            include_once $lib;
        }
    }

    public function getById($id) {
        return $this->_get_object_query($id);
    }

    private function _query() {
        include_once $this->db_conf;
        $db = new \MySQLiConnectionFactory();
        $this->conn = $db->getCon("readonly");
    }

    private function _get_object_query($oid) {
        $this->occid = (int) $oid;
        $this->_query();
        return $this->_load_occur_data();
    }

    private function _get_request_protocol() {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';
        return $REQUEST_PROTOCOL;
    }

    private function _get_hostname() {
        return $_SERVER['HTTP_HOST'];
    }

    private function _get_app_offset() {
        return implode(DIRECTORY_SEPARATOR, array_intersect(explode(DIRECTORY_SEPARATOR,$this->Request->get('base_url')), explode(DIRECTORY_SEPARATOR,$this->Request->get('base_path'))));
    }

    private function _get_record_reference($meta) {
        if (isset($meta['guid'])) {
            return "?guid=".$meta['guid'];
        }
        if (isset($meta['occid'])) {
            return "?occid=".$meta['occid'];
        }
        return "";
    }

    private function _construct_guid_url($meta) {
        $http = $this->_get_request_protocol();
        $host = $this->_get_hostname();
        $offset = $this->_get_app_offset();
        $guid = $this->_get_record_reference($meta);

        $this->ref_host = "$http://$host";
        $record_url = "$http://$host$offset/collections/individual/index.php$guid";
        return $record_url;
    }

    private function _load_occur_data(){
        $config = $this->ini;
        if (is_file($config)) {
            ob_start();
            include $config;
            ob_end_clean();
        }
        if(isset($QUICK_HOST_ENTRY_IS_ACTIVE)) {
            $GLOBALS['QUICK_HOST_ENTRY_IS_ACTIVE'] = true;
        }
        $this->_load_libs();
        $o = new \OccurrenceIndividualManager();
        $o->setOccid($this->occid);
        $occArr = $o->getOccData();
        $collMetadata = $o->getMetadata();

        $url = $this->_construct_guid_url($occArr);
        return (object) array( "meta-data" => $occArr, "collection-meta-data" => $collMetadata, "url" => $url, "host" => $this->ref_host );
    }

}
