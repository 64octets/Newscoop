<?php
require_once($_SERVER['DOCUMENT_ROOT']."/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/common.php");
load_common_include_files($ADMIN_DIR);
todefnum('TOL_UserId');
todefnum('TOL_UserKey');
list($access, $User) = check_basic_access($_REQUEST);
if (!$access) {
	exit;
}
$menu_index = 1;
$max_menu_items = 5;
$counter_reset = false;
$counter_resets = 0;
$preConfigMenu = array();
$preConfigMenu[] = array('title'=>'Article Types', 
					  'link' => 'a_types/', 
					  'permission' => 'ManageArticleTypes'); 
$preConfigMenu[] = array('title' => 'Topics', 
					  'link' => 'topics/',
					  'permission' => 'ManageTopics'); 
$preConfigMenu[] = array('title' => 'Languages', 
					  'link' => 'languages/',
					  'permission' => 'ManageLanguages' ); 
$preConfigMenu[] = array('title' => 'Templates', 
					  'link' => 'templates/',
					  'permission' => 'ManageLanguages'); 
$preConfigMenu[] = array('title' => 'Countries', 
					  'link' => 'country/',
					  'permission' => 'ManageCountries'); 
$preConfigMenu[] = array('title' => 'Logs', 
					  'link' => 'logs/',
					  'permission' => 'ViewLogs'); 
$preConfigMenu[] = array('title' => 'Localizer', 
					  'link' => 'localizer/',
					  'permission' => 'ManageLocalizer'); 
$configMenu = array();
foreach ($preConfigMenu as $item) {
	if ($User->hasPermission($item['permission'])) {
		$configMenu[] = $item;
	}
}
$numConfigMenuItems = count($configMenu);
$numConfigMenuColumns = ($numConfigMenuItems/2) + ($numConfigMenuItems%2);

$preUserMenu = array();
$preUserMenu[] = array('title'=>'Users', 
					  'link' => 'users/', 
					  'permission' => 'ManageUsers'); 
$preUserMenu[] = array('title' => 'User Types', 
					  'link' => 'u_types/',
					  'permission' => 'ManageUserTypes'); 
$userMenu = array();
foreach ($preUserMenu as $item) {
	if ($User->hasPermission($item['permission'])) {
		$userMenu[] = $item;
	}
}

$preObsoleteMenu = array();
$preObsoleteMenu[] = array('title'=>'Glossary', 
					  'link' => 'glossary/', 
					  'permission' => 'ManageDictionary'); 
$preObsoleteMenu[] = array('title' => 'Infotype', 
					  'link' => 'infotype/',
					  'permission' => 'ManageClasses'); 
$obsoleteMenu = array();
foreach ($preObsoleteMenu as $item) {
	if ($User->hasPermission($item['permission'])) {
		$obsoleteMenu[] = $item;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"
	"http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML>
<HEAD>
    <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<!--<META HTTP-EQUIV="Expires" CONTENT="now">-->
	<LINK rel="stylesheet" type="text/css" href="<?php echo $Campsite["website_url"] ?>/css/admin_stylesheet.css">	
	<TITLE><?php  putGS("Menu"); ?></TITLE>
</HEAD>

<BODY>
<div style="position: absolute; top: 74px; width: 100%; border-bottom: 1px solid black;"></div>

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#D0D0B0">
<tr>
	<td width="100px" style="padding: 15px; font-size: 14px; font-weight: bold; border-right: 1px solid black;" align="center">
		Campsite<br>
		v<?php p($Campsite['version']); ?>
		<!--<img src="/<?php echo $ADMIN; ?>/img/CampsiteLogo2.png">-->
	</td>
	<td width="100%">

		<TABLE CELLSPACING="0" CELLPADDING="0" bgcolor="#D0D0B0">
		<TR>
		<TD VALIGN="TOP">
			<TABLE border="0" CELLSPACING="0" CELLPADDING="0" BGCOLOR="#D0D0B0" width="100%">
			<TR>
				<!-- BEGIN Content Menu -->
				<td valign="top" style="padding-left: 10px;">
					<table cellpadding="1" cellspacing="0" border="0">
					<tr>
						<td colspan="2" valign="top">
							Content:
						</td>
					</tr>
					<tr>
						<TD ALIGN="left"><A HREF="pub/" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php  putGS("Publications"); ?>"></A></TD>
						<TD align="left" NOWRAP><A HREF="pub/" ONCLICK="" TARGET="fmain"><?php  putGS("Publications"); ?></A></TD>
					</tr>
					<tr>
						<TD ALIGN="left"><A HREF="imagearchive/" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php  putGS("Image archive"); ?>"></A></TD>
						<TD align="left" NOWRAP><A HREF="imagearchive/" ONCLICK="" TARGET="fmain"><?php  putGS("Image archive"); ?></A></TD>
					</tr>
					</table>
				</td>
				<!-- END Content Menu -->
				
				<?php if ($numConfigMenuItems > 0) { ?>
				<!-- BEGIN Configure Menu -->
				<td valign="top" style="padding-left: 30px;">
					<table cellpadding="0" cellspacing="0">
					<tr>
						<td colspan="2">
							Configure:
						</td>
					</tr>
					<tr>
						<td>
							<table border="0" cellpadding="1" cellspacing="0">
							<tr>
								<?php
								$count = 0;
								foreach ($configMenu as $menuItem) {
									if (($count % ($numConfigMenuColumns)) == 0) {
										echo "</tr><tr>";
									}
									?>
									<TD ALIGN="RIGHT"><A HREF="<?php p($menuItem['link']) ?>" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php putGS($menuItem['title']); ?>"></A></TD>
									<TD NOWRAP><A HREF="<?php p($menuItem['link']); ?>" TARGET="fmain"><?php  putGS($menuItem['title']); ?></A></TD>
								<?php
									$count++;
								} 
								?>
							</tr>
							</table>
						</td>
					</tr>
					</table>
				</td>	
				<!-- END Configure Menu -->
				<?php } ?>
				
				<?php if (count($userMenu) > 0) { ?>
				<!-- BEGIN User menu -->
				<td valign="top" style="padding-left: 30px;">
					<table cellpadding="1" cellspacing="0">
					<tr>
						<td colspan="2">
							Users:
						</td>
					</tr>
						<?php
						foreach ($userMenu as $menuItem) { ?>
							<tr>
								<TD ALIGN="left"><A HREF="<?php p($menuItem['link']) ?>" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php putGS($menuItem['title']); ?>"></A></TD>
								<TD align="left" NOWRAP><A HREF="<?php p($menuItem['link']); ?>" TARGET="fmain"><?php  putGS($menuItem['title']); ?></A></TD>
							</tr>
							<?php 
						}
						?>
					</table>
				</td>	
				<!-- END User menu -->
				<?php } ?>
				
				<?php if (count($obsoleteMenu) > 0) { ?>
				<!-- BEGIN Obsolete menu -->
				<td valign="top" style="padding-left: 30px;">
					<table cellpadding="1" cellspacing="0">
					<tr>
						<td colspan="2">
							Obsolete:
						</td>
					</tr>
						<?php
						foreach ($obsoleteMenu as $menuItem) {
							if ($User->hasPermission($menuItem['permission'])) { ?>
							<tr>
								<TD ALIGN="left"><A HREF="<?php p($menuItem['link']) ?>" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php putGS($menuItem['title']); ?>"></A></TD>
								<TD align="left" NOWRAP><A HREF="<?php p($menuItem['link']); ?>" TARGET="fmain"><?php  putGS($menuItem['title']); ?></A></TD>
							</tr>
							<?php 
							} 
						}
						?>
					</table>
				</td>	
				<!-- END Obsolete menu -->
				<?php } ?>
			</tr>
			</TABLE>
		</TD>	
		</TR>
		</TABLE>
	</td>
	
	<TD WIDTH="1%" VALIGN="TOP" style="height: 75px; border-left: 1px solid black; padding-left: 5px; padding-right: 5px;">
		<TABLE CELLSPACING="1" CELLPADDING="1" BGCOLOR="#D0D0B0" width="100%" height="100%">
		<TR>
			<TD ALIGN="left"><A HREF="home.php" ONCLICK="" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php  putGS("Home"); ?>"></A></TD>
			<TD NOWRAP><A HREF="home.php" ONCLICK="" TARGET="fmain"><?php  putGS("Home"); ?></A></TD>
		</tr>
		
		<tr>
			<TD ALIGN="left"><A HREF="" ONCLICK="window.open('/<?php echo $ADMIN; ?>/popup/', 'fpopup', 'menu=no,width=500,height=410'); return false;" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php  putGS("Quick Menu"); ?>"></A></TD>
			<TD NOWRAP><A HREF="" ONCLICK="window.open('/<?php echo $ADMIN; ?>/popup/', 'fpopup', 'menu=no,width=500,height=410'); return false;" TARGET="fmain"><?php  putGS("Quick Menu"); ?></A></TD>
		</TR>
		
		<TR>
			<TD ALIGN="RIGHT"><A HREF="logout.php" ONCLICK="" TARGET="fmain"><IMG SRC="/<?php echo $ADMIN; ?>/img/tol.gif" BORDER="0" ALT="<?php  putGS("Logout"); ?>"></A></TD>
			<TD NOWRAP COLSPAN="3" ALIGN="LEFT"><A HREF="logout.php" ONCLICK="" TARGET="fmain"><?php  putGS("Logout"); ?></A></TD>
		</TR>
		</TABLE>
	</TD>
</tr>
</table>
</BODY>
</HTML>