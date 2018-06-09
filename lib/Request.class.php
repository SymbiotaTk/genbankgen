<?php
namespace GenBankGen;

class Request {
    public function __construct() {
        $this->auth_engine = (object) array();
        $this->user_data = (object) array();
        $this->object_data = (object) array();
        $this->controller = "index.php";
        $this->here = dirname(dirname(__FILE__));
        $this->here_url = dirname($_SERVER['PHP_SELF']);
        if ($this->here_url == DIRECTORY_SEPARATOR) { $this->here_url = ""; }

        $this->set_main_controller($this->controller);
        $this->_init();
    }

    private function _init() {
        $controller = $this->controller;
        $this->defaults = array(
            "path_system"   => dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))),
            "base_path"     => dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))),
            "base_query"    => "",
            "base_url"      => $this->here_url,
            "plugin_path"   => $this->here,
            "plugin_module_url" => $this->here_url."/elements",
            "storage_path"  => $this->here."/files/storage",
            "storage_url"   => $this->here_url."/files/storage",

            "auth_filter"   => array(),
            "fm_disable_file_extension"  => array("php","pl","cgi"),
            "currentUser"   => "|not logged in|",
            "currentUserId" => 0,
            "currentOccid"  => 0,
            "allowed_methods" => array(
                "test"   => "test",
                "user"   => "user",
                "object" => "object",
                "display" => "display",
                "create" => "create",
                "view"   => "view",
                "fm"     => "fm",
                "fm_entries"     => "fm_entries",
            ),
        );

        foreach ($this->defaults as $env => $value) {
            if (! isset($this->$env)) {
                $this->$env = $value;
            }
        }
    }

    public function set_main_controller($controller) {
        $this->main_controller = $controller;
        $this->create_url      = $this->here_url."/".$controller."?c=create";
        $this->fm_entries_url  = $this->here_url."/".$controller."?c=fm_entries";
        $this->fm_base_url     = $this->here_url."/".$controller."?c=fm";
        $this->user_base_url   = $this->here_url."/".$controller."?c=user&id=";
        $this->object_base_url = $this->here_url."/".$controller."?c=object&id=";
    }

    public function get_pkg_info($path) {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $name = $parts[count($parts) - 1];
        $url = implode(DIRECTORY_SEPARATOR, array($this->get("plugin_module_url"), $name));
        return (object) array( "path" => $path, "url" => $url );
    }

    public function get($name) {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return false;
    }

    public function set($name, $value) {
        if (! method_exists($this, $name)) {
            if (isset($this->$name)) {
                $this->$name = $value;
            }
        }

    }
}
