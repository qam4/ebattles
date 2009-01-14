<?php

// SVN Revision Template file :: SVNrevisionTemplate.php

// Read by: SubWCRev.exe (TortoiseSVN)
// which writes: /include/SVNrevision.php
//

$svnRevision = "10";
$svnModified = "Not modified";
$svnDate = "2009/01/14 01:47:42";
$svnRevRange = "10";
$svnMixed = "Not mixed";
$svnURL = "https://ebattles.googlecode.com/svn/trunk/include";

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