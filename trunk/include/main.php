<?php
/**
 * Main.php
 *
 */
include("./include/session.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>eBattles - Online Gaming Tournaments &amp; Ladders</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name="keywords" content="Online, Gaming, Clans, Leagues, Battles, Wars, Ladders, Competition, Multiplayer, Tournaments">
    <meta name="description" content="Host for online gaming tournaments and ladders.">    

    <style type="text/css" media="screen">
        @import url("common/lists.css");
        @import url("css/tools.css");
        @import url("css/typo.css");
        @import url("css/forms.css");
        /* swap layout stylesheet: 
        	layout-navtop-localleft.css
		layout-navtop-subright.css
		layout-navtop-3col.css
		layout-navtop-1col.css
		layout-navleft-1col.css
		layout-navleft-2col.css
	*/
        @import url("css/layout-navleft-1col.css");
        @import url("css/layout.css");
        @import url("css/tab.css");
    </style>

    <!-- calendar stylesheet -->
    <link rel="stylesheet" type="text/css" media="all" href="calendar-blue.css" title="win2k-cold-1" />

    <script language="javascript" type="text/javascript" src="./tiny_mce/tiny_mce.js"></script>
    <script language="javascript" type="text/javascript">
    tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	skin : "o2k7",
	skin_variant : "black",
	plugins : "table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,searchreplace,print,contextmenu",
	theme_advanced_buttons1 : "save,print,preview,separator,bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright, justifyfull",
	theme_advanced_buttons2: "cut,copy,paste,separator,undo,redo,bullist,numlist,separator,outdent,indent",
	theme_advanced_buttons2_add : "separator,forecolor,backcolor",
	theme_advanced_buttons3 : "link,unlink,image,charmap,emotions,insertdate,inserttime",
	theme_advanced_toolbar_location : "bottom",
	theme_advanced_toolbar_align : "left",
	plugin_insertdate_dateFormat : "%Y-%m-%d",
	plugin_insertdate_timeFormat : "%H:%M:%S",
	extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
   });
    </script>
    <link rel="SHORTCUT ICON" type="image/png" href="../images/site_icon_16.png">
</head>

<body id="page-home">
    
    <div id="page">
    
    <?php include("./include/header.php");?>

        <div id="content" class="clearfix">
        
            <?php include("./menu.php");?>
