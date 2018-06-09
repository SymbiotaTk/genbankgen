<?php
namespace GenBankGen;

class Files {
    public function __construct(){ }

    public function error($args){
        $args = (object) $args;
        if (! isset($args->msg)) { return false; }
        return $args->msg;
    }

    private function _dir_exists($path){
        if (is_dir($path)){ return true; }
        return false;
    }

    private function _scandir_track_level(){
        if ($this->scandir_levels == 0) { return true; }
        if ($this->scandir_levels > $this->scandir_current_level){
            $this->scandir_current_level++;
            return true;
        }
        return false;
    }

    private function _scandir($path){
        $listing = array();
        $objects = scandir($path);
        foreach ($objects as $object) {
          if ($object != "." && $object != "..") {
              $full = $path.DIRECTORY_SEPARATOR.$object;
              $entry = (object) array( "path" => $full );
              $entry->type = "file";
              if (is_dir($full)){
                $entry->type = "dir";
                if ($this->_scandir_track_level()) {
                    $entry->children = $this->_scandir($full);
                }
              }
              array_push($listing, $entry);
          }
        }
        return $listing;
    }

    public function scandir($args){
        $args = (object) $args;
        if ($this->_dir_exists($args->path)) {
            $args->msg = "Directory found.";
            $this->scandir_levels = $args->levels;
            $this->scandir_current_level = 0;
            $listing = $this->_scandir($args->path);
            return $listing;
        }
        return $this->error($args);
    }

    public function mkdir($args) {
        $args = (object) $args;
        if(!isset($args->permissions)) { $args->permissions = 0777; }
        if(!isset($args->recursive)) { $args->recursive = false; }
        if(is_dir($args->path)){
            $args->msg = "Directory already exists.";
            return $this->error($args);
        };
        if (!mkdir($args->path, $args->permissions, $args->recursive)) {
            die('Failed to create folders...'.$args->path);
        }
        if (is_dir($args->path)) { return true; }
        return $this->error($args);
    }

    public function rmdir($args) {
        $args = (object) $args;
        if(!is_dir($args->path)){
            return $this->error($args);
        };
        $args->levels = 1;
        $children = $this->scandir($args);
        if (count($children) > 0){
            $args->msg = "Directory is NOT empty.";
            return $this->error($args);
        }
        rmdir($args->path);
        if (!is_dir($args->path)) {
            return true;
        }
        return false;
    }

    private function _write($path, $content) {
        $this->mkdir(array("path" => dirname($path), "recursive" => true));
        file_put_contents($path, $content);
    }

    public function write($args) {
        $args = (object) $args;
        if (! is_file($args->path)){
            $this->_write($args->path, $args->content);
            return true;
        }
        return false;
    }

    public function read($args) {
        $args = (object) $args;
        if (is_file($args->path)) {
            return file_get_contents($args->path);
        }
        return false;
    }

    public function info($args) {
        $args = (object) $args;
        if (is_file($args->path)) {
            $path_parts = pathinfo($args->path);
            $stat = stat($args->path);
            $info = (object) array(
                "path" => $args->path,
                "size" => filesize($args->path),
                "mtime" => $stat['mtime'],
                "ctime" => $stat['ctime'],
                "name" => basename($args->path),
                "type" => mime_content_type($args->path),
                "extension" => $path_parts['extension'],
            );
            return $info;
        }
        return false;
    }

    public function rename($args) {
        $args = (object) $args;
        if (is_file($args->path)) {
            if (! is_file($args->destination)) {
                $this->mkdir(array("path" => dirname($args->destination), "recursive" => true));
                rename($args->path, $args->destination);
                return true;
            }
        }
        return false;
    }

    public function copy($args) {
        $args = (object) $args;
        if (is_file($args->path)) {
            if (! is_file($args->destination)) {
                $this->mkdir(array("path" => dirname($args->destination), "recursive" => true));
                copy($args->path, $args->destination);
                return true;
            }
        }
        return false;
    }

    public function delete($args) {
        $args = (object) $args;
        if (is_file($args->path)) {
            unlink($args->path);
            return true;
        }
        return false;
    }
}
