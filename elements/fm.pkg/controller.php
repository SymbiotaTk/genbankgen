<?php

class ControllerEmbededFileManager {
    public function __construct ($req, $res) {
        $this->file_model = "file_system";        // file_system (default) || indexed
        $this->pkg_path = dirname(__FILE__);
        $this->pkg_lib  = $this->pkg_path."/lib";
        $this->pkg_views = $this->pkg_path."/views";

        $this->Request = $req;
        $this->Response = $res;

        $this->file_filter_extensions  = $this->Request->get("fm_disable_file_extension");

        $this->pkg_base_query = $this->Request->get("base_query");

        $this->initialize_dirs = array();
        $this->_config();

        if(!session_id())
        {
            // session has NOT been started
            session_cache_limiter('');
            session_name('filemanager');
            session_start();
        }
    }

    private function _load_libs ($libs) {
        foreach ($libs as $lib) {
            $lib = $this->pkg_lib."/".$lib;
            if (!is_file($lib)) { return "Cannot open main class: ".$lib; }
            include_once $lib;
        }
    }

    private function _initialize_dirs($fm) {
        while(!empty($this->initialize_dirs)) {
            $dir = array_shift($this->initialize_dirs);
            $fm->mkdir($dir,1);
        }
    }

    private function render_secure() {
        echo "Secure directory.";
        $this->file_model = "indexed";
        $libs = array(
            "FileManager.class.php",
            "FM_Zipper.class.php",
        );
        $this->_load_libs($libs);
        $fm = new FileManager();
        $fm->template_header = $this->pkg_views."/FileManager.header.html";
        $fm->template_footer = $this->pkg_views."/FileManager.footer.html";
        $this->_initialize_dirs($fm);

        $this->_setHttpPaths($fm);
        $this->_setLanguages($fm);
        $this->_setGlobals($fm);

        $this->_action_check_path_and_parent($fm);
        $this->_showImage($fm);

        $this->_action_ajax_request($fm);
        $this->_action_delete_file_folder($fm);
        $this->_action_create_folder($fm);
        $this->_action_copy_folder_file($fm);
        $this->_action_mass_copy_files_folders($fm);
        $this->_action_rename($fm);
        $this->_action_download($fm);
        $this->_action_upload($fm);
        $this->_action_mass_deleting($fm);
        $this->_action_pack_files($fm);
        $this->_action_unpack($fm);
        $this->_action_change_perms($fm);
        $this->_action_upload_form($fm);
        $this->_action_copy_form_post($fm);
        $this->_action_copy_form($fm);
        $this->_action_file_viewer($fm);
        $this->_action_file_editor($fm);
        $this->_action_chmod($fm);

        $lang = $this->instance->config->lang;
        $files = $this->instance->files;
        $folders = $this->instance->folders;
        $path = $this->instance->path;
        $parent = $this->instance->parent;

        include_once $this->pkg_views."/FileManager.main.php";
    }

    public function render() {
        if ($_GET['p'] == "Secure") {
          $this->render_secure();
          return 1;
        }
        $libs = array(
            "FileManager.class.php",
            "FM_Zipper.class.php",
        );
        $this->_load_libs($libs);

        $fm = new FileManager();
        $fm->template_header = $this->pkg_views."/FileManager.header.html";
        $fm->template_footer = $this->pkg_views."/FileManager.footer.html";
        $this->_initialize_dirs($fm);

        $this->_setHttpPaths($fm);
        $this->_setLanguages($fm);
        $this->_setGlobals($fm);
        $this->_action_check_path_and_parent($fm);
        $this->_showImage($fm);
        $this->_action_ajax_request($fm);
        $this->_action_delete_file_folder($fm);
        $this->_action_create_folder($fm);
        $this->_action_copy_folder_file($fm);
        $this->_action_mass_copy_files_folders($fm);
        $this->_action_rename($fm);
        $this->_action_download($fm);
        $this->_action_upload($fm);
        $this->_action_mass_deleting($fm);
        $this->_action_pack_files($fm);
        $this->_action_unpack($fm);
        $this->_action_change_perms($fm);
        $this->_action_upload_form($fm);
        $this->_action_copy_form_post($fm);
        $this->_action_copy_form($fm);
        $this->_action_file_viewer($fm);
        $this->_action_file_editor($fm);
        $this->_action_chmod($fm);

        $lang = $this->instance->config->lang;
        $files = $this->instance->files;
        $folders = $this->instance->folders;
        $path = $this->instance->path;
        $parent = $this->instance->parent;

        include_once $this->pkg_views."/FileManager.main.php";

    }

    public function setRootFolder($path) {
        $this->instance->config->root_path = $path;
        array_push($this->initialize_dirs, $path);
    }

    public function setBaseUrl($path) {
        $this->instance->config->root_url = $path;
    }

    private function setAttribute($name, $value) {
        $this->instance->$name = $value;
    }

    private function _config() {
      $this->instance = (object) array();

      $config = (object) array(
          "lang" => 'en',
          "use_auth" => false,
          "auth_users" => array(
              'admin' => '21232f297a57a5a743894a0e4a801fc3', //admin {MD5}
              'user' => '827ccb0eea8a706c4c34a16891f84e7b', //12345  {MD5}
          ),
          "readonly_users" => array(
              'user'
          ),
          "show_hidden_files" => true,
          "use_highlightjs" => true,    // enable highlight.js
          "highlightjs_style" => 'vs',
          "edit_files" => true,         // enable ace.js
          "send_mail" => false,
          "toMailId" => "",             //yourmailid@mail.com
          "default_timezone" => 'Etc/UTC', // UTC - http://php.net/manual/en/timezones.php
          "root_path" => $_SERVER['DOCUMENT_ROOT'],    // root path for file manager
          "root_url" => '',
          "http_host" => $_SERVER['HTTP_HOST'],
          "iconv_input_encoding" => 'UTF-8',
          "datetime_format" => 'd.m.y H:i',    // date() format for file modification date
          "pkg_base_query" => $this->pkg_base_query,
          "pkg_path" => $this->pkg_path,
          "pkg_lib" => $this->pkg_lib,
          "pkg_views" => $this->pkg_views,
      );
      $this->setAttribute("config", $config);
    }

    private function _setHttpPaths ($fm) {
        $config = $this->instance->config;

        $is_https = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

        // clean and check $root_path
        $config->root_path = rtrim($config->root_path, '\\/');
        $config->root_path = str_replace('\\', '/', $config->root_path);
        if (!@is_dir($config->root_path)) {
            echo "<h1>Root path \"{$config->root_path}\" not found!</h1>";
            exit;
        }

        // clean $root_url
        $config->root_url = $fm->clean_path($config->root_url);

        // abs path for site
        defined('FM_SHOW_HIDDEN') || define('FM_SHOW_HIDDEN', $config->show_hidden_files);
        defined('FM_ROOT_PATH') || define('FM_ROOT_PATH', $config->root_path);
        defined('FM_ROOT_URL') || define('FM_ROOT_URL', ($is_https ? 'https' : 'http') . '://' . $config->http_host . (!empty($config->root_url) ? '/' . $config->root_url : ''));
        defined('FM_SELF_URL') || define('FM_SELF_URL', ($is_https ? 'https' : 'http') . '://' . $config->http_host . $_SERVER['PHP_SELF']);
    }

    private function _setLanguages ($fm) {
        $config = $this->instance->config;

        $this->setAttribute("languages", $fm->get_available_langs());
        defined('FM_LANG') || define('FM_LANG', $config->lang);
    }

    private function _showImage ($fm) {
        // Show image here
        if (isset($_GET['img'])) {
            $fm->show_image($_GET['img']);
        }
    }

    private function _setGlobals ($fm) {
        $config = $this->instance->config;
        $fm->base_query = '?'.$this->instance->config->pkg_base_query.'&p=';
        $fm->base_query_noqmark = substr($fm->base_query, 1);

        // define('FM_IS_WIN', DIRECTORY_SEPARATOR == '\\');
        define('FM_IS_WIN', false);

        // always use ?p=
        if (!isset($_GET['p'])) {
            $fm->redirect(FM_SELF_URL . $fm->base_query);
        }

        // get path
        $p = isset($_GET['p']) ? $_GET['p'] : (isset($_POST['p']) ? $_POST['p'] : '');

        // clean path
        $p = $fm->clean_path($p);

        if (!defined('FM_READONLY')) {
            define('FM_READONLY', false);
        }

        // instead globals vars
        define('FM_PATH', $p);
        define('FM_USE_AUTH', $config->use_auth);
        defined('FM_ICONV_INPUT_ENC') || define('FM_ICONV_INPUT_ENC', $config->iconv_input_encoding);
        defined('FM_USE_HIGHLIGHTJS') || define('FM_USE_HIGHLIGHTJS', $config->use_highlightjs);
        defined('FM_HIGHLIGHTJS_STYLE') || define('FM_HIGHLIGHTJS_STYLE', $config->highlightjs_style);
        defined('FM_DATETIME_FORMAT') || define('FM_DATETIME_FORMAT', $config->datetime_format);

        unset($p, $config->use_auth, $config->iconv_input_encoding, $config->use_highlightjs, $config->highlightjs_style);
    }

    private function _action_ajax_request ($fm) {
        //AJAX Request
        if (isset($_POST['ajax']) && !FM_READONLY) {

            //search : get list of files from the current folder
            if(isset($_POST['type']) && $_POST['type']=="search") {
                $dir = $_POST['path'];
                $response = $fm->scan($dir);
                echo json_encode($response);
            }

            //Send file to mail
            if (isset($_POST['type']) && $_POST['type']=="mail") {
                $isSend = $fm->send_mail($_POST['path'],$_POST['file'], $toMailId, 'File attached');
                echo $isSend;
            }

            //backup files
            if(isset($_POST['type']) && $_POST['type']=="backup") {
                $file = $_POST['file'];
                $path = $_POST['path'];
                $date = date("dMy-His");
                $newFile = $file.'-'.$date.'.bak';
                $fm->copy($path.'/'.$file, $path.'/'.$newFile,true) or die("Unable to backup");
                echo "Backup $newFile Created";
            }

            exit;
        }
    }

    private function _action_delete_file_folder ($fm) {
        // Delete file / folder
        if (isset($_GET['del']) && !FM_READONLY) {
            $del = $_GET['del'];
            $del = $fm->clean_path($del);
            $del = str_replace('/', '', $del);
            if ($del != '' && $del != '..' && $del != '.') {
                $path = FM_ROOT_PATH;
                if (FM_PATH != '') {
                    $path .= '/' . FM_PATH;
                }
                $is_dir = is_dir($path . '/' . $del);
                if ($fm->rdelete($path . '/' . $del)) {
                    $msg = $is_dir ? 'Folder <b>%s</b> deleted' : 'File <b>%s</b> deleted';
                    $fm->set_msg(sprintf($msg, $del));
                } else {
                    $msg = $is_dir ? 'Folder <b>%s</b> not deleted' : 'File <b>%s</b> not deleted';
                    $fm->set_msg(sprintf($msg, $del), 'error');
                }
            } else {
                $fm->set_msg('Wrong file or folder name', 'error');
            }
            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_create_folder ($fm) {
        // Create folder
        if (isset($_GET['new']) && isset($_GET['type']) && !FM_READONLY) {
            $new = $_GET['new'];
            $type = $_GET['type'];
            $new = $fm->clean_path($new);
            $new = str_replace('/', '', $new);
            if ($new != '' && $new != '..' && $new != '.') {
                $path = FM_ROOT_PATH;
                if (FM_PATH != '') {
                    $path .= '/' . FM_PATH;
                }
                if($_GET['type']=="file") {
                    $new = $this->_filter_file_extension($new);
                    if(!$fm->file_exists($path . '/' . $new)) {
                        $fm->create_file($path . '/' . $new);
                        $fm->set_msg(sprintf($fm->t('File <b>%s</b> created'), $new));
                    } else {
                        $fm->set_msg(sprintf($fm->t('File <b>%s</b> already exists'), $new), 'alert');
                    }
                } else {
                    if ($fm->mkdir($path . '/' . $new, false) === true) {
                        $fm->set_msg(sprintf($fm->t('Folder <b>%s</b> created'), $new));
                    } elseif ($fm->mkdir($path . '/' . $new, false) === $path . '/' . $new) {
                        $fm->set_msg(sprintf($fm->t('Folder <b>%s</b> already exists'), $new), 'alert');
                    } else {
                        $fm->set_msg(sprintf($fm->t('Folder <b>%s</b> not created'), $new), 'error');
                    }
                }
            } else {
                $fm->set_msg('Wrong folder name', 'error');
            }
            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_copy_folder_file ($fm) {
        // Copy folder / file
        if (isset($_GET['copy'], $_GET['finish']) && !FM_READONLY) {
            // from
            $copy = $_GET['copy'];
            $copy = $fm->clean_path($copy);
            // empty path
            if ($copy == '') {
                $fm->set_msg('Source path not defined', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }
            // abs path from
            $from = FM_ROOT_PATH . '/' . $copy;
            // abs path to
            $dest = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $dest .= '/' . FM_PATH;
            }
            $dest .= '/' . basename($from);
            // move?
            $move = isset($_GET['move']);
            // copy/move
            if ($from != $dest) {
                $msg_from = trim(FM_PATH . '/' . basename($from), '/');
                if ($move) {
                    $rename = $fm->rename($from, $dest);
                    if ($rename) {
                        $fm->set_msg(sprintf('Moved from <b>%s</b> to <b>%s</b>', $copy, $msg_from));
                    } elseif ($rename === null) {
                        $fm->set_msg('File or folder with this path already exists', 'alert');
                    } else {
                        $fm->set_msg(sprintf('Error while moving from <b>%s</b> to <b>%s</b>', $copy, $msg_from), 'error');
                    }
                } else {
                    if ($fm->rcopy($from, $dest)) {
                        $fm->set_msg(sprintf('Copyied from <b>%s</b> to <b>%s</b>', $copy, $msg_from));
                    } else {
                        $fm->set_msg(sprintf('Error while copying from <b>%s</b> to <b>%s</b>', $copy, $msg_from), 'error');
                    }
                }
            } else {
                $fm->set_msg('Paths must be not equal', 'alert');
            }
            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_mass_copy_files_folders ($fm) {
        // Mass copy files/ folders
        if (isset($_POST['file'], $_POST['copy_to'], $_POST['finish']) && !FM_READONLY) {
            // from
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }
            // to
            $copy_to_path = FM_ROOT_PATH;
            $copy_to = $fm->clean_path($_POST['copy_to']);
            if ($copy_to != '') {
                $copy_to_path .= '/' . $copy_to;
            }
            if ($path == $copy_to_path) {
                $fm->set_msg('Paths must be not equal', 'alert');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }
            if (!is_dir($copy_to_path)) {
                if (!$fm->mkdir($copy_to_path, true)) {
                    $fm->set_msg('Unable to create destination folder', 'error');
                    $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
                }
            }
            // move?
            $move = isset($_POST['move']);
            // copy/move
            $errors = 0;
            $files = $_POST['file'];
            if (is_array($files) && count($files)) {
                foreach ($files as $f) {
                    if ($f != '') {
                        // abs path from
                        $from = $path . '/' . $f;
                        // abs path to
                        $dest = $copy_to_path . '/' . $f;
                        // do
                        if ($move) {
                            $rename = $fm->rename($from, $dest);
                            if ($rename === false) {
                                $errors++;
                            }
                        } else {
                            if (!$fm->rcopy($from, $dest)) {
                                $errors++;
                            }
                        }
                    }
                }
                if ($errors == 0) {
                    $msg = $move ? 'Selected files and folders moved' : 'Selected files and folders copied';
                    $fm->set_msg($msg);
                } else {
                    $msg = $move ? 'Error while moving items' : 'Error while copying items';
                    $fm->set_msg($msg, 'error');
                }
            } else {
                $fm->set_msg('Nothing selected', 'alert');
            }
            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _filter_file_extension ($path) {
        $parts = pathinfo($path);
        $extension = $parts['extension'];
        $filters = $this->file_filter_extensions;
        if (null !== $filters) {
            if (in_array($extension, $filters)) {
                return $path."_";
            }
        }
        return $path;
    }

    private function _action_rename ($fm) {
        // Rename
        if (isset($_GET['ren'], $_GET['to']) && !FM_READONLY) {
            // old name
            $old = $_GET['ren'];
            $old = $fm->clean_path($old);
            $old = str_replace('/', '', $old);
            // new name
            $new = $_GET['to'];
            $new = $fm->clean_path($new);
            $new = str_replace('/', '', $new);
            $new = $this->_filter_file_extension($new);
            // path
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }
            // rename
            if ($old != '' && $new != '') {
                if ($fm->rename($path . '/' . $old, $path . '/' . $new)) {
                    $fm->set_msg(sprintf('Renamed from <b>%s</b> to <b>%s</b>', $old, $new));
                } else {
                    $fm->set_msg(sprintf('Error while renaming from <b>%s</b> to <b>%s</b>', $old, $new), 'error');
                }
            } else {
                $fm->set_msg('Names not set', 'error');
            }
            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_download ($fm) {
        // Download
        if (isset($_GET['dl'])) {
            $dl = $_GET['dl'];
            $dl = $fm->clean_path($dl);
            $dl = str_replace('/', '', $dl);
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }
            if ($dl != '' && $fm->is_file($path . '/' . $dl)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($path . '/' . $dl) . '"');
                header('Content-Transfer-Encoding: binary');
                header('Connection: Keep-Alive');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . $fm->filesize($path . '/' . $dl));
                $fm->readfile($path . '/' . $dl);
                exit;
            } else {
                $fm->set_msg('File not found', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }
        }
    }

    private function _action_upload ($fm) {
        // Upload
        if (isset($_POST['upl']) && !FM_READONLY) {
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }

            $errors = 0;
            $uploads = 0;
            $total = count($_FILES['upload']['name']);

            for ($i = 0; $i < $total; $i++) {
                $tmp_name = $_FILES['upload']['tmp_name'][$i];
                if (empty($_FILES['upload']['error'][$i]) && !empty($tmp_name) && $tmp_name != 'none') {
                    if ($fm->move_uploaded_file($tmp_name, $path . '/' . $_FILES['upload']['name'][$i])) {
                        $uploads++;
                    } else {
                        $errors++;
                    }
                }
            }

            if ($errors == 0 && $uploads > 0) {
                $fm->set_msg(sprintf('All files uploaded to <b>%s</b>', $path));
            } elseif ($errors == 0 && $uploads == 0) {
                $fm->set_msg('Nothing uploaded', 'alert');
            } else {
                $fm->set_msg(sprintf('Error while uploading files. Uploaded files: %s', $uploads), 'error');
            }

            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_mass_deleting ($fm) {
        // Mass deleting
        if (isset($_POST['group'], $_POST['delete']) && !FM_READONLY) {
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }

            $errors = 0;
            $files = $_POST['file'];
            if (is_array($files) && count($files)) {
                foreach ($files as $f) {
                    if ($f != '') {
                        $new_path = $path . '/' . $f;
                        if (!$fm->rdelete($new_path)) {
                            $errors++;
                        }
                    }
                }
                if ($errors == 0) {
                    $fm->set_msg('Selected files and folder deleted');
                } else {
                    $fm->set_msg('Error while deleting items', 'error');
                }
            } else {
                $fm->set_msg('Nothing selected', 'alert');
            }

            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_pack_files ($fm) {
        // Pack files
        if (isset($_POST['group'], $_POST['zip']) && !FM_READONLY) {
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }

            if (!class_exists('ZipArchive')) {
                $fm->set_msg('Operations with archives are not available', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $files = $_POST['file'];
            if (!empty($files)) {
                chdir($path);

                if (count($files) == 1) {
                    $one_file = reset($files);
                    $one_file = basename($one_file);
                    $zipname = $one_file . '_' . date('ymd_His') . '.zip';
                } else {
                    $zipname = 'archive_' . date('ymd_His') . '.zip';
                }

                $zipper = new FM_Zipper();
                $res = $zipper->create($zipname, $files);

                if ($res) {
                    $fm->set_msg(sprintf('Archive <b>%s</b> created', $zipname));
                } else {
                    $fm->set_msg('Archive not created', 'error');
                }
            } else {
                $fm->set_msg('Nothing selected', 'alert');
            }

            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_unpack ($fm) {
        // Unpack
        if (isset($_GET['unzip']) && !FM_READONLY) {
            $unzip = $_GET['unzip'];
            $unzip = $fm->clean_path($unzip);
            $unzip = str_replace('/', '', $unzip);

            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }

            if (!class_exists('ZipArchive')) {
                $fm->set_msg('Operations with archives are not available', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            if ($unzip != '' && is_file($path . '/' . $unzip)) {
                $zip_path = $path . '/' . $unzip;

                //to folder
                $tofolder = '';
                if (isset($_GET['tofolder'])) {
                    $tofolder = pathinfo($zip_path, PATHINFO_FILENAME);
                    if ($fm->mkdir($path . '/' . $tofolder, true)) {
                        $path .= '/' . $tofolder;
                    }
                }

                $zipper = new FM_Zipper();
                $res = $zipper->unzip($zip_path, $path);

                if ($res) {
                    $fm->set_msg('Archive unpacked');
                } else {
                    $fm->set_msg('Archive not unpacked', 'error');
                }

            } else {
                $fm->set_msg('File not found', 'error');
            }
            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    private function _action_change_perms ($fm) {
        // Change Perms (not for Windows)
        if (isset($_POST['chmod']) && !FM_READONLY && !FM_IS_WIN) {
            $path = FM_ROOT_PATH;
            if (FM_PATH != '') {
                $path .= '/' . FM_PATH;
            }

            $file = $_POST['chmod'];
            $file = $fm->clean_path($file);
            $file = str_replace('/', '', $file);
            if ($file == '' || (!is_file($path . '/' . $file) && !is_dir($path . '/' . $file))) {
                $fm->set_msg('File not found', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $mode = 0;
            if (!empty($_POST['ur'])) {
                $mode |= 0400;
            }
            if (!empty($_POST['uw'])) {
                $mode |= 0200;
            }
            if (!empty($_POST['ux'])) {
                $mode |= 0100;
            }
            if (!empty($_POST['gr'])) {
                $mode |= 0040;
            }
            if (!empty($_POST['gw'])) {
                $mode |= 0020;
            }
            if (!empty($_POST['gx'])) {
                $mode |= 0010;
            }
            if (!empty($_POST['or'])) {
                $mode |= 0004;
            }
            if (!empty($_POST['ow'])) {
                $mode |= 0002;
            }
            if (!empty($_POST['ox'])) {
                $mode |= 0001;
            }

            if (@chmod($path . '/' . $file, $mode)) {
                $fm->set_msg('Permissions changed');
            } else {
                $fm->set_msg('Permissions not changed', 'error');
            }

            $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
        }
    }

    // !!! IN PROGRESS -- CREATE LIST OF FILES and DIRECTORIES
    private function _get_files_indexed($fm, $path) {
        echo "Load indexed file class.";
        // [ ] does $path exist in index
        // $fm->get_parent_path(FM_PATH)
        $parent = $fm->get_parent_path(FM_PATH);
        $this->setAttribute("parent", $parent);

        $files = array(
            "one.txt",
            "two.jpg",
        );
        $this->setAttribute("files", array());
        $this->setAttribute("folders", array());

        if (!empty($files)) {
            natcasesort($files);
            $this->setAttribute("files", $files);
        }
        if (!empty($folders)) {
            natcasesort($folders);
            $this->setAttribute("folders", $folders);
        }

    }

    private function _get_files_scandir($fm, $path) {
        // check path
        if (!is_dir($path)) {
            $fm->redirect(FM_SELF_URL . $fm->base_query);
        }

        // get parent folder
        $parent = $fm->get_parent_path(FM_PATH);
        $this->setAttribute("parent", $parent);

        // fetch file system scandir
        $objects = is_readable($path) ? scandir($path) : array();
        $folders = array();
        $files = array();
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (!FM_SHOW_HIDDEN && substr($file, 0, 1) === '.') {
                    continue;
                }
                $new_path = $path . '/' . $file;
                if (is_file($new_path)) {
                    $files[] = $file;
                } elseif (is_dir($new_path) && $file != '.' && $file != '..') {
                    $folders[] = $file;
                }
            }
        }
        $this->setAttribute("files", array());
        $this->setAttribute("folders", array());

        if (!empty($files)) {
            natcasesort($files);
            $this->setAttribute("files", $files);
        }
        if (!empty($folders)) {
            natcasesort($folders);
            $this->setAttribute("folders", $folders);
        }
    }

    private function _action_check_path_and_parent ($fm) {
        // get current path
        $path = FM_ROOT_PATH;
        if (FM_PATH != '') {
            $path .= '/' . FM_PATH;
        }
        $this->setAttribute("path", $path);

        switch ($this->file_model) {            // indexed || file system
          case 'indexed':
            $this->_get_files_indexed($fm, $path);
            break;

          default:
            $this->_get_files_scandir($fm, $path);
            break;
        }
    }

    private function _action_upload_form ($fm) {
        // upload form
        if (isset($_GET['upload']) && !FM_READONLY) {
            $fm->show_header(); // HEADER
            $fm->show_nav_path(FM_PATH); // current path
            ?>
            <div class="path">
                <p><b><?php echo $fm->t('Uploading files') ?></b></p>
                <p class="break-word"><?php echo $fm->t('Destination folder:') ?> <?php echo $fm->convert_win(FM_ROOT_PATH . '/' . FM_PATH) ?></p>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="p" value="<?php echo $fm->enc(FM_PATH) ?>">
                    <input type="hidden" name="upl" value="1">
                    <input type="file" name="upload[]"><br>
                    <input type="file" name="upload[]"><br>
                    <input type="file" name="upload[]"><br>
                    <input type="file" name="upload[]"><br>
                    <input type="file" name="upload[]"><br>
                    <br>
                    <p>
                        <button type="submit" class="btn"><i class="fa fa-check-circle"></i> <?php echo $fm->t('Upload', $this->instance->config->lang) ?></button> &nbsp;
                        <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>"><i class="fa fa-times-circle"></i> <?php echo $fm->t('Cancel', $this->instance->config->lang) ?></a></b>
                    </p>
                </form>
            </div>
            <?php
            $fm->show_footer();
            exit;
        }
    }

    private function _action_copy_form_post ($fm) {
        // copy form POST
        if (isset($_POST['copy']) && !FM_READONLY) {
            $copy_files = $_POST['file'];
            if (!is_array($copy_files) || empty($copy_files)) {
                $fm->set_msg('Nothing selected', 'alert');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $fm->show_header(); // HEADER
            $fm->show_nav_path(FM_PATH); // current path
            ?>
            <div class="path">
                <p><b>Copying</b></p>
                <form action="" method="post">
                    <input type="hidden" name="p" value="<?php echo $fm->enc(FM_PATH) ?>">
                    <input type="hidden" name="finish" value="1">
                    <?php
                    foreach ($copy_files as $cf) {
                        echo '<input type="hidden" name="file[]" value="' . $fm->enc($cf) . '">' . PHP_EOL;
                    }
                    ?>
                    <p class="break-word"><?php echo $fm->t('Files:') ?> <b><?php echo implode('</b>, <b>', $copy_files) ?></b></p>
                    <p class="break-word"><?php echo $fm->t('Source folder:') ?> <?php echo $fm->convert_win(FM_ROOT_PATH . '/' . FM_PATH) ?><br>
                        <label for="inp_copy_to"><?php echo $fm->t('Destination folder:') ?></label>
                        <?php echo FM_ROOT_PATH ?>/<input type="text" name="copy_to" id="inp_copy_to" value="<?php echo $fm->enc(FM_PATH) ?>">
                    </p>
                    <p><label><input type="checkbox" name="move" value="1"> <?php echo $fm->t('Move', $this->instance->config->lang) ?></label></p>
                    <p>
                        <button type="submit" class="btn"><i class="fa fa-check-circle"></i> <?php echo $fm->t('Copy', $this->instance->config->lang) ?></button> &nbsp;
                        <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>"><i class="fa fa-times-circle"></i> <?php echo $fm->t('Cancel', $this->instance->config->lang) ?></a></b>
                    </p>
                </form>
            </div>
            <?php
            $fm->show_footer();
            exit;
        }
    }

    private function _action_copy_form ($fm) {
        $parent = $this->instance->parent;
        $folders = $this->instance->folders;

        // copy form
        if (isset($_GET['copy']) && !isset($_GET['finish']) && !FM_READONLY) {
            $copy = $_GET['copy'];
            $copy = $fm->clean_path($copy);
            if ($copy == '' || !$fm->file_exists(FM_ROOT_PATH . '/' . $copy)) {
                $fm->set_msg('File not found', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $fm->show_header(); // HEADER
            $fm->show_nav_path(FM_PATH); // current path
            ?>
            <div class="path">
                <p><b><?php echo $fm->t('Copying', $this->instance->config->lang) ?></b></p>
                <p class="break-word">
                    <?php echo $fm->t('Source path:', $this->instance->config->lang) ?> <?php echo $fm->convert_win(FM_ROOT_PATH . '/' . $copy) ?><br>
                    <?php echo $fm->t('Destination folder:', $this->instance->config->lang) ?> <?php echo $fm->convert_win(FM_ROOT_PATH . '/' . FM_PATH) ?>
                </p>
                <p>
                    <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;copy=<?php echo urlencode($copy) ?>&amp;finish=1"><i class="fa fa-check-circle"></i> <?php echo $fm->t('Copy', $this->instance->config->lang) ?></a></b> &nbsp;
                    <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;copy=<?php echo urlencode($copy) ?>&amp;finish=1&amp;move=1"><i class="fa fa-check-circle"></i> <?php echo $fm->t('Move', $this->instance->config->lang) ?></a></b> &nbsp;
                    <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>"><i class="fa fa-times-circle"></i> <?php echo $fm->t('Cancel', $this->instance->config->lang) ?></a></b>
                </p>
                <p><i><?php echo $fm->t('Select folder:') ?></i></p>
                <ul class="folders break-word">
                    <?php
                    if ($parent !== false) {
                        ?>
                        <li><a href="<?php echo $fm->base_query; echo urlencode($parent) ?>&amp;copy=<?php echo urlencode($copy) ?>"><i class="fa fa-chevron-circle-left"></i> ..</a></li>
                    <?php
                    }
                    foreach ($folders as $f) {
                        ?>
                        <li><a href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>&amp;copy=<?php echo urlencode($copy) ?>"><i class="fa fa-folder-o"></i> <?php echo $fm->convert_win($f) ?></a></li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
            $fm->show_footer();
            exit;
        }
    }

    private function _action_file_viewer($fm) {
        // file viewer
        $path = $this->instance->path;
        $config = $this->instance->config;

        if (isset($_GET['view'])) {
            $file = $_GET['view'];
            $file = $fm->clean_path($file);
            $file = str_replace('/', '', $file);
            if ($file == '' || !$fm->is_file($path . '/' . $file)) {
                $fm->set_msg('File not found', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $fm->show_header(); // HEADER
            $fm->show_nav_path(FM_PATH); // current path

            $file_url = FM_ROOT_URL . $fm->convert_win((FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $file);
            $file_path = $path . '/' . $file;

            $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            $mime_type = $fm->get_mime_type($file_path);
            $filesize = filesize($file_path);

            $is_zip = false;
            $is_image = false;
            $is_audio = false;
            $is_video = false;
            $is_text = false;

            $view_title = 'File';
            $filenames = false; // for zip
            $content = ''; // for text

            if ($ext == 'zip') {
                $is_zip = true;
                $view_title = 'Archive';
                $filenames = $fm->get_zif_info($file_path);
            } elseif (in_array($ext, $fm->get_image_exts())) {
                $is_image = true;
                $view_title = 'Image';
            } elseif (in_array($ext, $fm->get_audio_exts())) {
                $is_audio = true;
                $view_title = 'Audio';
            } elseif (in_array($ext, $fm->get_video_exts())) {
                $is_video = true;
                $view_title = 'Video';
            } elseif (in_array($ext, $fm->get_text_exts()) || substr($mime_type, 0, 4) == 'text' || in_array($mime_type, $fm->get_text_mimes())) {
                $is_text = true;
                $content = file_get_contents($file_path);
            }

            ?>
            <div class="path">
                <p class="break-word"><b><?php echo $view_title ?> "<?php echo $fm->convert_win($file) ?>"</b></p>
                <p class="break-word">
                    <?php echo $fm->t('Full path:', $this->instance->config->lang) ?> <?php echo $fm->convert_win($file_path) ?><br>
                    <?php echo $fm->t('File size:', $this->instance->config->lang) ?> <?php echo $fm->get_filesize($filesize) ?><?php if ($filesize >= 1000): ?> (<?php echo sprintf('%s bytes', $filesize) ?>)<?php endif; ?><br>
                    <?php echo $fm->t('MIME-type:', $this->instance->config->lang) ?> <?php echo $mime_type ?><br>
                    <?php
                    // ZIP info
                    if ($is_zip && $filenames !== false) {
                        $total_files = 0;
                        $total_comp = 0;
                        $total_uncomp = 0;
                        foreach ($filenames as $fn) {
                            if (!$fn['folder']) {
                                $total_files++;
                            }
                            $total_comp += $fn['compressed_size'];
                            $total_uncomp += $fn['filesize'];
                        }
                        ?>
                        Files in archive: <?php echo $total_files ?><br>
                        Total size: <?php echo $fm->get_filesize($total_uncomp) ?><br>
                        Size in archive: <?php echo $fm->get_filesize($total_comp) ?><br>
                        Compression: <?php echo round(($total_comp / $total_uncomp) * 100) ?>%<br>
                        <?php
                    }
                    // Image info
                    if ($is_image) {
                        $image_size = getimagesize($file_path);
                        echo 'Image sizes: ' . (isset($image_size[0]) ? $image_size[0] : '0') . ' x ' . (isset($image_size[1]) ? $image_size[1] : '0') . '<br>';
                    }
                    // Text info
                    if ($is_text) {
                        $is_utf8 = $fm->is_utf8($content);
                        if (function_exists('iconv')) {
                            if (!$is_utf8) {
                                $content = iconv(FM_ICONV_INPUT_ENC, 'UTF-8//IGNORE', $content);
                            }
                        }
                        echo 'Charset: ' . ($is_utf8 ? 'utf-8' : '8 bit') . '<br>';
                    }
                    ?>
                </p>
                <p>
                    <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;dl=<?php echo urlencode($file) ?>"><i class="fa fa-cloud-download"></i> <?php echo $fm->t('Download', $this->instance->config->lang) ?></a></b> &nbsp;
                    <b class="fa-external-link"><a href="<?php echo $file_url ?>" target="_blank"><i class="fa fa-external-link-square"></i> <?php echo $fm->t('Open', $this->instance->config->lang) ?></a></b> &nbsp;
                    <?php
                    // ZIP actions
                    if (!FM_READONLY && $is_zip && $filenames !== false) {
                        $zip_name = pathinfo($file_path, PATHINFO_FILENAME);
                        ?>
                        <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;unzip=<?php echo urlencode($file) ?>"><i class="fa fa-check-circle"></i> <?php echo $fm->t('UnZip', $this->instance->config->lang) ?></a></b> &nbsp;
                        <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;unzip=<?php echo urlencode($file) ?>&amp;tofolder=1" title="<?php echo $fm->t('UnZip to', $this->instance->config->lang) ?> <?php echo $fm->enc($zip_name) ?>"><i class="fa fa-check-circle"></i>
                            <?php echo $fm->t('UnZip to folder', $this->instance->config->lang) ?></a></b> &nbsp;
                        <?php
                    }
                    if($is_text && !FM_READONLY) {
                    ?>
                    <b><a href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>" class="edit-file"><i class="fa fa-pencil-square"></i> <?php echo $fm->t('Edit', $this->instance->config->lang) ?></a></b> &nbsp;
                    <b><a href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>&env=ace" class="edit-file"><i class="fa fa-pencil-square"></i> <?php echo $fm->t('Advanced Edit', $this->instance->config->lang) ?></a></b> &nbsp;
                    <?php }
                    if($config->send_mail && !FM_READONLY) {
                    ?>
                    <b><a href="javascript:mailto('<?php echo urlencode(trim(FM_ROOT_PATH.'/'.FM_PATH)) ?>','<?php echo urlencode($file) ?>')"><i class="fa fa-pencil-square"></i> <?php echo $fm->t('Mail', $this->instance->config->lang) ?></a></b> &nbsp;
                    <?php } ?>
                    <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>"><i class="fa fa-chevron-circle-left"></i> <?php echo $fm->t('Back', $this->instance->config->lang) ?></a></b>
                </p>
                <?php
                if ($is_zip) {
                    // ZIP content
                    if ($filenames !== false) {
                        echo '<code class="maxheight">';
                        foreach ($filenames as $fn) {
                            if ($fn['folder']) {
                                echo '<b>' . $fn['name'] . '</b><br>';
                            } else {
                                echo $fn['name'] . ' (' . $fm->get_filesize($fn['filesize']) . ')<br>';
                            }
                        }
                        echo '</code>';
                    } else {
                        echo '<p>Error while fetching archive info</p>';
                    }
                } elseif ($is_image) {
                    // Image content
                    if (in_array($ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico'))) {
                        echo '<p><img src="' . $file_url . '" alt="" class="preview-img"></p>';
                    }
                } elseif ($is_audio) {
                    // Audio content
                    echo '<p><audio src="' . $file_url . '" controls preload="metadata"></audio></p>';
                } elseif ($is_video) {
                    // Video content
                    echo '<div class="preview-video"><video src="' . $file_url . '" width="640" height="360" controls preload="metadata"></video></div>';
                } elseif ($is_text) {
                    if (FM_USE_HIGHLIGHTJS) {
                        // highlight
                        $hljs_classes = array(
                            'shtml' => 'xml',
                            'htaccess' => 'apache',
                            'phtml' => 'php',
                            'lock' => 'json',
                            'svg' => 'xml',
                        );
                        $hljs_class = isset($hljs_classes[$ext]) ? 'lang-' . $hljs_classes[$ext] : 'lang-' . $ext;
                        if (empty($ext) || in_array(strtolower($file), $fm->get_text_names()) || preg_match('#\.min\.(css|js)$#i', $file)) {
                            $hljs_class = 'nohighlight';
                        }
                        $content = '<pre class="with-hljs"><code class="' . $hljs_class . '">' . $fm->enc($content) . '</code></pre>';
                    } elseif (in_array($ext, array('php', 'php4', 'php5', 'phtml', 'phps'))) {
                        // php highlight
                        $content = highlight_string($content, true);
                    } else {
                        $content = '<pre>' . $fm->enc($content) . '</pre>';
                    }
                    echo $content;
                }
                ?>
            </div>
            <?php
            $fm->show_footer();
            exit;
        }
    }

    private function _action_file_editor($fm) {
        // file editor
        $path = $this->instance->path;

        if (isset($_GET['edit'])) {
            $file = $_GET['edit'];
            $file = $fm->clean_path($file);
            $file = str_replace('/', '', $file);
            if ($file == '' || !is_file($path . '/' . $file)) {
                $fm->set_msg($fm->t('File not found', $this->instance->config->lang), 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $fm->show_header(); // HEADER
            $fm->show_nav_path(FM_PATH); // current path

            $file_url = FM_ROOT_URL . $fm->convert_win((FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $file);
            $file_path = $path . '/' . $file;

            //normal editer
            $isNormalEditor = true;
            if(isset($_GET['env'])) {
                if($_GET['env'] == "ace") {
                    $isNormalEditor = false;
                }
            }

            //Save File
            if(isset($_POST['savedata'])) {
                $writedata = $_POST['savedata'];
                $fd=fopen($file_path,"w");
                @fwrite($fd, $writedata);
                fclose($fd);
                $fm->set_msg($fm->t('File Saved Successfully', $this->instance->config->lang), 'alert');
            }

            $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            $mime_type = $fm->get_mime_type($file_path);
            $filesize = filesize($file_path);
            $is_text = false;
            $content = ''; // for text

            if (in_array($ext, $fm->get_text_exts()) || substr($mime_type, 0, 4) == 'text' || in_array($mime_type, $fm->get_text_mimes())) {
                $is_text = true;
                $content = file_get_contents($file_path);
            }

            ?>
            <div class="path">
                <div class="edit-file-actions">
                    <a title="<?php echo $fm->t('Cancel', $this->instance->config->lang) ?>" href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH)) ?>&amp;view=<?php echo urlencode($file) ?>"><i class="fa fa-reply-all"></i> <?php echo $fm->t('Cancel', $this->instance->config->lang) ?></a>
                    <a title="<?php echo $fm->t('Backup', $this->instance->config->lang) ?>" href="javascript:backup('<?php echo urlencode($path) ?>','<?php echo urlencode($file) ?>')"><i class="fa fa-database"></i> <?php echo $fm->t('Backup', $this->instance->config->lang) ?></a>
                    <?php if($is_text) { ?>
                        <?php if($isNormalEditor) { ?>
                            <a title="<?php echo $fm->t('Advanced', $this->instance->config->lang) ?>" href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>&amp;env=ace"><i class="fa fa-paper-plane"></i> <?php echo $fm->t('Advanced Editor', $this->instance->config->lang) ?></a>
                            <button type="button" name="<?php echo $fm->t('Save', $this->instance->config->lang) ?>" data-url="<?php echo $file_url ?>" onclick="edit_save(this,'nrl')"><i class="fa fa-floppy-o"></i> <?php echo $fm->t('Save', $this->instance->config->lang) ?></button>
                        <?php } else { ?>
                            <a title="<?php echo $fm->t('Plain Editor', $this->instance->config->lang) ?>" href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>"><i class="fa fa-text-height"></i> <?php echo $fm->t('Plain Editor', $this->instance->config->lang) ?></a>
                            <button type="button" name="Save" data-url="<?php echo $file_url ?>" onclick="edit_save(this,'ace')"><i class="fa fa-floppy-o"></i> <?php echo $fm->t('Save', $this->instance->config->lang) ?></button>
                        <?php } ?>
                    <?php } ?>
                </div>
                <?php
                if ($is_text && $isNormalEditor) {
                    echo '<textarea id="normal-editor" rows="33" cols="120" style="width: 99.5%;">'. htmlspecialchars($content) .'</textarea>';
                } elseif ($is_text) {
                    echo '<div id="editor" contenteditable="true">'. htmlspecialchars($content) .'</div>';
                } else {
                    $fm->set_msg($fm->t('FILE EXTENSION HAS NOT SUPPORTED', $this->instance->config->lang), 'error');
                }
                ?>
            </div>
            <?php
            $fm->show_footer();
            exit;
        }
    }
    private function _action_chmod($fm) {
        // chmod (not for Windows)
        $path = $this->instance->path;

        if (isset($_GET['chmod']) && !FM_READONLY && !FM_IS_WIN) {
            $file = $_GET['chmod'];
            $file = $fm->clean_path($file);
            $file = str_replace('/', '', $file);
            if ($file == '' || (!is_file($path . '/' . $file) && !is_dir($path . '/' . $file))) {
                $fm->set_msg('File not found', 'error');
                $fm->redirect(FM_SELF_URL . $fm->base_query . urlencode(FM_PATH));
            }

            $fm->show_header(); // HEADER
            $fm->show_nav_path(FM_PATH); // current path

            $file_url = FM_ROOT_URL . (FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $file;
            $file_path = $path . '/' . $file;

            $mode = fileperms($path . '/' . $file);

            ?>
            <div class="path">
                <p><b><?php echo $fm->t('Change Permissions', $this->instance->config->lang) ?></b></p>
                <p>
                    <?php echo $fm->t('Full path:', $this->instance->config->lang) ?> <?php echo $file_path ?><br>
                </p>
                <form action="" method="post">
                    <input type="hidden" name="p" value="<?php echo $fm->enc(FM_PATH) ?>">
                    <input type="hidden" name="chmod" value="<?php echo $fm->enc($file) ?>">

                    <table class="compact-table">
                        <tr>
                            <td></td>
                            <td><b><?php echo $fm->t('Owner', $this->instance->config->lang) ?></b></td>
                            <td><b><?php echo $fm->t('Group', $this->instance->config->lang) ?></b></td>
                            <td><b><?php echo $fm->t('Other', $this->instance->config->lang) ?></b></td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><b><?php echo $fm->t('Read', $this->instance->config->lang) ?></b></td>
                            <td><label><input type="checkbox" name="ur" value="1"<?php echo ($mode & 00400) ? ' checked' : '' ?>></label></td>
                            <td><label><input type="checkbox" name="gr" value="1"<?php echo ($mode & 00040) ? ' checked' : '' ?>></label></td>
                            <td><label><input type="checkbox" name="or" value="1"<?php echo ($mode & 00004) ? ' checked' : '' ?>></label></td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><b><?php echo $fm->t('Write', $this->instance->config->lang) ?></b></td>
                            <td><label><input type="checkbox" name="uw" value="1"<?php echo ($mode & 00200) ? ' checked' : '' ?>></label></td>
                            <td><label><input type="checkbox" name="gw" value="1"<?php echo ($mode & 00020) ? ' checked' : '' ?>></label></td>
                            <td><label><input type="checkbox" name="ow" value="1"<?php echo ($mode & 00002) ? ' checked' : '' ?>></label></td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><b><?php echo $fm->t('Execute', $this->instance->config->lang) ?></b></td>
                            <td><label><input type="checkbox" name="ux" value="1"<?php echo ($mode & 00100) ? ' checked' : '' ?>></label></td>
                            <td><label><input type="checkbox" name="gx" value="1"<?php echo ($mode & 00010) ? ' checked' : '' ?>></label></td>
                            <td><label><input type="checkbox" name="ox" value="1"<?php echo ($mode & 00001) ? ' checked' : '' ?>></label></td>
                        </tr>
                    </table>

                    <p>
                        <button type="submit" class="btn"><i class="fa fa-check-circle"></i> <?php echo $fm->t('Change', $this->instance->config->lang) ?></button> &nbsp;
                        <b><a href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>"><i class="fa fa-times-circle"></i> <?php echo $fm->t('Cancel', $this->instance->config->lang) ?></a></b>
                    </p>

                </form>

            </div>
            <?php
            $fm->show_footer();
            exit;
        }
    }
}
