<?php
   // always include the class2.php file - this is the main e107 file
   require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

   // this generates all the HTML up to the start of the main section
   e107_require_once(HEADERF);

   // Include plugin language file, check first for site's preferred language
   //include_lan(e_PLUGIN."myplugin/languages/myplugin_".e_LANGUAGE.".php")) {

   // write your PHP code to generate the required HTML here
   $text = "my HTML goes here";
   $text .= "more HTML for our plugin";

   // Ensure the pages HTML is rendered using the theme layout.
   $ns->tablerender('hello', $text);

   // this generates all the HTML (menus etc.) after the end of the main section
   e107_require_once(FOOTERF);
?>