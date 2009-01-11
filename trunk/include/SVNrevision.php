<?php

// SVN Revision Template file :: SVNrevisionTemplate.php

// Read by: SubWCRev.exe (TortoiseSVN)
// which writes: /include/SVNrevision.php
//

$svnRevision = "58";
$svnModified = "Modified";
$svnDate = "2008/12/10 16:39:20";
$svnRevRange = "57:58";
$svnMixed = "Mixed revision WC";
$svnURL = "file:///\fmarc-lab2/src/svnroot/eBattles/trunk/include";

$thisRevRange = explode(':', $svnRevRange);
$startRange = $thisRevRange[0];
$endRange = '';
if(isset($thisRevRange[1]))
{
  $endRange = $thisRevRange[1];
}

$endRange = $endRange + 1;
$svnRevRange = $startRange . ":" . $endRange;

$svnRevision = $svnRevision + 1;

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