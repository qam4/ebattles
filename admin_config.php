<?php
   // Remember that we must include class2.php
   require_once("../../class2.php");

   // Check current user is an admin, redirect to main site if not
   if (!getperms("P")) {
      header("location:".e_HTTP."index.php");
      exit;
   }

   // Include page header stuff for admin pages
   require_once(e_ADMIN."auth.php");

   // Handle preferences form being submitted
   // n.b. to complex to list in this example

   // Our informative text
   $text = "Generate some HTML for our preference form";

   // The usual, tell e107 what to include on the page
   $ns->tablerender("MyPlugin Read Me", $text);

   require_once(e_ADMIN."footer.php");
?>