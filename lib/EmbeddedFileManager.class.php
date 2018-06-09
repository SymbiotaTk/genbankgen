<?php
namespace GenBankGen;

class EmbeddedFileManager {
    public function __construct($req, $res) {
        $this->Request = $req;
        $this->Response = $res;
    }

    public function render() {
        return $this->_view();
    }

    private function _view() {
        $this->base_query = $this->Request->get("base_query");
        $this->pkg_path = $this->Request->get("plugin_path")."/elements/fm.pkg";
        include_once $this->pkg_path."/controller.php";
        $p = new \ControllerEmbededFileManager($this->Request, $this->Response);
        
        $loggedin = (object) $this->Response->loggedin;
        $username = $loggedin->username;
        $p->setRootFolder($this->Request->get("storage_path")."/".$username);
        $p->setBaseUrl($this->Request->get("storage_path")."/".$username);
        echo $p->render();
    }
}
