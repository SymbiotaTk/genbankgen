GenBank submission tool plugin
--------------

A plugin to create genetic sequence submissions for the [GenBank](https://www.ncbi.nlm.nih.gov/genbank/) database, an annotated
collection of publicly available DNA sequences.

This set of tools provides a form interface, and basic file manager to facilitate the
generation, and editing, of submission files. An abstract code structure provides PHP
classes to integrate with the authentication and dataset interfaces for [Symbiota](http://symbiota.org/docs/), an
open source software framework. Authenticated user information, and specific specimen
meta-data, is extracted from the curated data in the portal environment, and compiled
with simplified form entries to generate a validated [GenBank](https://www.ncbi.nlm.nih.gov/genbank/) submission file (.sqn).
[Tbl2asn](https://www.ncbi.nlm.nih.gov/genbank/tbl2asn2/), developed by [GenBank](https://www.ncbi.nlm.nih.gov/genbank/), is the command-line program that produces the final
formated submission file on the server. The embedded PHP file manager provides a full
featured application for managing, editing, and downloading files produced by the plugin.

### References
* [GenBank](https://www.ncbi.nlm.nih.gov/genbank/)
* [Tbl2asn](https://www.ncbi.nlm.nih.gov/genbank/tbl2asn2/)
* [Symbiota](http://symbiota.org/docs/)
* [Mycology Collections Portal](http://mycoportal.org/portal/index.php)

### Installation (Symbiota)

1. Copy files to a location within the portal root directory.
   (ie. /portal/webservices/plugins/genbankgen)
2. Create directory for files and set write permissions for the web server.
   Example:
   ```bash
       cd /portal/webservices/plugins/genbankgen
       mkdir -p files/storage
       chmod -Rf 777 files
   ```
3. Update the plugin path in plugin.php
   Example:
   ```php
       $plugin_root = "/portal/webservices/plugins/genbankgen";
   ```
4. Embed application button
   Example:
   -- ADDED to END of /portal/collections/editor/includes/resourcetab.php
   ```php
   <div id="geneticdiv"  style="width:795px;">
           <fieldset>
                   <legend><b>GenBank Submission</b></legend>
   		<?php
   		    $lib_path = $SERVER_ROOT."/webservices/plugins/genbankgen/plugin.php";
   		    include_once $lib_path;
   		    if(class_exists('\GenBankGen\Plugin')) {
   			$defaults->SYMB_UID = $SYMB_UID;
   			$p = new \GenBankGen\Plugin($defaults);
   			echo $p->embed();
   		    }
   		?>
           </fieldset>
   </div>
   ```
