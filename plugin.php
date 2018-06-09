<?php

// config #START#
$plugin_root = "/portal/webservices/plugins/genbankgen";

// config #END#

include_once "lib/Request.class.php";
include_once "lib/Plugin.class.php";
include_once "lib/Files.class.php";

include_once "elements/symbiota_auth.pkg/controller.php";
include_once "elements/symbiota_occurrence_data.pkg/controller.php";
include_once "elements/testing.pkg/controller.php";

$req = new \GenBankGen\Request();
if (isset($SERVER_ROOT)) {
    $req->here = dirname(__FILE__);
    $req->here_url = $plugin_root;
    $req->set("path_system", $SERVER_ROOT);
    $req->set("base_path", $SERVER_ROOT);
    $req->set("base_url", $req->here_url);
    $req->set("plugin_path", $req->here);
    $req->set("plugin_module_url", $req->here_url."/elements");
    $req->set("storage_path", $req->here."/files/storage");
    $req->set("storage_url", $req->here."/files/storage");
    $req->set_main_controller($req->controller);
}


$req->set("auth_engine", new \GenBankGen\ControllerSymbiotaAuth($req));
$req->set("user_data", new \GenBankGen\ControllerSymbiotaAuth($req));
$req->set("object_data", new \GenBankGen\ControllerSymbiotaOccurrenceData($req));

$defaults = $req;
