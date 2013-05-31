<?php

// SVN Revision Template file :: SVNrevisionTemplate.php

// Read by: SubWCRev.exe (TortoiseSVN)
// which writes: /include/SVNrevision.php
//

$svnRevision = "370";
$svnModified = "Modified";
$svnDate = "2013/05/29 13:51:29";
$svnRevRange = "369:370";
$svnMixed = "Mixed revision WC";
$svnURL = "https://ebattles.googlecode.com/svn/branches/0.9.7_nesjett/include";

$thisRevRange = explode(':', $svnRevRange);
$startRange = $thisRevRange[0];
$endRange = $startRange;
$svnRevRange = $startRange;
if(isset($thisRevRange[1]))
{
  $endRange = $thisRevRange[1];
    $svnRevRange = $startRange . ":" . $endRange;
}

$svnRevision = $endRange + 1; // Next revision number

$now = date("F j, Y, g:i a");
echo " \n";
echo " <!-- Source Version........: $svnRevision --> \n";
echo " <!-- Modification Status...: $svnModified --> \n";
echo " <!-- Version Commit Date...: $now --> \n";
echo " <!-- Revision Range........: $svnRevRange --> \n";
echo " <!-- Source Mixture........: $svnMixed --> \n";
echo " <!-- SVN *URL*.............: $svnURL --> \n";
echo " \n";

// EndOfFile

?>