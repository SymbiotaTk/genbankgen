<?php

include_once "plugin.php";
include_once "elements/symbiota_auth.pkg/controller.php";
include_once "elements/symbiota_occurrence_data.pkg/controller.php";
include_once "elements/testing.pkg/controller.php";

$req = new \GenBankGen\Request();
$req->set_main_controller(basename($_SERVER['PHP_SELF']));
$req->set("auth_engine", new \GenBankGen\ControllerTesting("user"));
$req->set("user_data", new \GenBankGen\ControllerTesting("user"));
$req->set("object_data", new \GenBankGen\ControllerTesting("object"));

// print_r($req);

// echo var_dump($defaults);
// echo var_dump($_SERVER);
$p = new \GenBankGen\Plugin($req);
$p->run();
