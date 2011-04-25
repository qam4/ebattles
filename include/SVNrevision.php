<?php

// SVN Revision Template file :: SVNrevisionTemplate.php

// Read by: SubWCRev.exe (TortoiseSVN)
// which writes: /include/SVNrevision.php
//

$svnRevision = "288";
$svnModified = "Modified";
$svnDate = "2011/04/24 21:52:08";
$svnRevRange = "288";
$svnMixed = "Not mixed";
$svnURL = "https://ebattles.googlecode.com/svn/branches/sc2Battles/include";

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