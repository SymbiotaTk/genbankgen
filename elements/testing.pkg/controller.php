<?php
namespace GenBankGen;

class ControllerTesting {
    public function __construct($mode) {
        $this->mode = $mode;
        $this->plugin_path = dirname(__FILE__);
        $this->user_data = $this->plugin_path."/data/user.data";
        $this->object_data = $this->plugin_path."/data/object.data";
    }

    public function isLoggedIn() {
        return $this->_read($this->user_data);
        // return false;
    }

    private function _get_users() {
        return $this->user_data;
    }

    private function _get_objects() {
        return $this->object_data;
    }

    private function _read($file) {
        return json_decode(file_get_contents($file));
    }

    private function _has($name, $id, $array) {
        if (isset($array->$name)) {
            if ($id == $array->$name) {
                return $array;
            }
        }
        return false;
    }

    public function getById($id) {
        $mode = $this->mode;
        switch ($mode) {
          case 'user':
              // 463
              return $this->_has("uid", $id, $this->_read($this->user_data));
              break;

          case 'object':
              // 2751741
              $array = $this->_read($this->object_data);
              if($this->_has("occid", $id, $array[0]->{'meta-data'})) {
                  return $array[0];
              }
              break;

          default:
              return false;
              break;
        }
    }
}
