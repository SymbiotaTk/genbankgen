<?php
namespace GenBankGen;

class ControllerForm {
    public function __construct($req, $res) {
        $this->Request = $req;
        $this->Response = $res;

        $this->pkg_info = $this->Request->get_pkg_info(dirname(__FILE__));
        $this->css_namespace_label = "GenBank";

        $this->js = "";
        $this->js_src = array(
            "/view/cssjs/beauter.min.js",
            "/view/cssjs/class/HtmlFormContainer.class.js",
            "/view/cssjs/class/HtmlUl_withDelete.class.js",
            "/view/cssjs/class/HtmlFormEmbedded.class.js",
            "/view/cssjs/class/formAddAdditionalAuthors_schema.class.js",
            "/view/cssjs/class/formAddSequence_schema.class.js",
            "/view/cssjs/class/collectionAddTable.class.js",
            "/view/cssjs/class/handleLocalStorage.class.js",
            "/view/cssjs/class/handleAJAX.class.js",
            "/view/cssjs/class/handleTbl2asnSubmission.class.js",
        );
        $this->css = "";
        $this->css_links = array(
            "/view/cssjs/beauter.min.css",
            "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css",
        );

        return $this;
    }

    private function _wrap_css($path) {
        $pkg_url  = $this->pkg_info->url;
        $location = $pkg_url.$path;
        if (preg_match("|^(https?:)?\/\/|i", $path)) {
            $location = $path;
        }
        return "<link href=\"$location\" rel=\"stylesheet\">";
    }

    private function _wrap_js($path) {
        $pkg_url  = $this->pkg_info->url;
        $path = $pkg_url.$path;
        return "<script type=\"text/javascript\" src=\"$path\"></script>";
    }

    private function _set_css() {
        foreach ($this->css_links as $file) {
            $this->css .= $this->_wrap_css($file);
        }
    }

    private function _set_js() {
        foreach ($this->js_src as $file) {
            $this->js .= $this->_wrap_js($file);
        }
    }

    public function view() {
        $pkg_path = $this->pkg_info->path;
        $pkg_url  = $this->pkg_info->url;

        $this->_set_css();
        $this->_set_js();

        $button = "<style>".file_get_contents($pkg_path."/view/cssjs/main.css")."</style>";
        $button .= file_get_contents($pkg_path."/view/main.html");
        $button .= '<script language="Javascript">';
        $button .= file_get_contents($pkg_path."/view/cssjs/main.js");
        $button .= "</script>";

        return str_replace(array(
            "{%LINKED_CSS%}",
            "{%LINKED_JavaScript%}",
            "{%CSSNS%}",
            "{%CSSNSL%}",
            "{%button_path%}",
            "{%plugin_url%}",
            "{%rurl_create%}",
            "{%rurl_generated%}",
            "{%rurl_fm%}",
            "{%rurl_user%}",
            "{%rurl_object%}",
            "{%currentUser%}",
            "{%currentUserId%}",
            "{%currentOccid%}"
          ),
          array(
            $this->css,
            $this->js,
            ".".$this->css_namespace_label,
            $this->css_namespace_label,
            $this->pkg_info->path,
            $this->Request->get('base_url'),
            $this->Request->get('create_url'),
            $this->Request->get('fm_entries_url'),
            $this->Request->get('fm_base_url'),
            $this->Request->get('user_base_url'),
            $this->Request->get('object_base_url'),
            $this->Response->currentUser,
            $this->Response->currentUserId,
            $this->Response->currentOccid
          ), $button);
    }
}
