GenBank submission tool plugin
--------------

A plugin to create genetic sequence submissions for the [GenBank](https://www.ncbi.nlm.nih.gov/genbank/) database, an annotated 
collection of publicly available DNA sequences. 

This set of tools provides a form interface, and basic file manager to facilitate the
generation, and editing, of submission files. An abstract code structure provides PHP 
classes to integrate with the authentication and dataset interfaces for [Symbiota](http://symbiota.org/docs/), an 
open source software framework. Authenticated user information, and specific specimen 
meta-data, is extracted from the curated data in the portal environment, and compiled 
with simplified form entries to generate a valid [GenBank](https://www.ncbi.nlm.nih.gov/genbank/) submission file (.sqn). 
[Tbl2asn](https://www.ncbi.nlm.nih.gov/genbank/tbl2asn2/), developed by [GenBank](https://www.ncbi.nlm.nih.gov/genbank/), is the command-line program that produces the final 
formated submission file on the server. The embedded PHP file manager provides a full
featured application for managing, editing, and downloading files produced by the plugin.


[GenBank](https://www.ncbi.nlm.nih.gov/genbank/)
[Tbl2asn](https://www.ncbi.nlm.nih.gov/genbank/tbl2asn2/)
[Symbiota](http://symbiota.org/docs/)
[Mycology Collections Portal](http://mycoportal.org/portal/index.php)
