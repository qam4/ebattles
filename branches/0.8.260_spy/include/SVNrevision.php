<?php

// SVN Revision Template file :: SVNrevisionTemplate.php

// Read by: SubWCRev.exe (TortoiseSVN)
// which writes: /include/SVNrevision.php
//

$svnRevision = "259";
$svnModified = "Modified";
$svnDate = "2010/10/06 23:27:51";
$svnRevRange = "261";
$svnMixed = "Not mixed";
$svnURL = "https://ebattles.googlecode.com/svn/branches/0.8.260_spy/include";

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