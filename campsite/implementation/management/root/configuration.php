<?php

global $Campsite, $ADMIN_DIR, $ADMIN, $DO_NOT_CONNECT_TO_DB;

$configuration_files = array("install_conf.php", 
							 "database_conf.php", 
							 "apache_conf.php", 
							 "parser_conf.php", 
							 "smtp_conf.php");

foreach ($configuration_files as $index=>$conf_file) {
	require($_SERVER['DOCUMENT_ROOT'] . "/$conf_file");
}
unset($configuration_files);

$Campsite['HTML_COMMON_DIR'] = $Campsite['WWW_COMMON_DIR'] . "/html";
$Campsite['CGI_COMMON_DIR'] = $Campsite['WWW_COMMON_DIR'] . "/cgi-bin";

//$db_name = $Campsite['DATABASE_NAME'];
$Campsite['HTML_DIR'] = $Campsite['WWW_DIR'].'/'.$Campsite['DATABASE_NAME'].'/html';
$Campsite['CGI_DIR'] = $Campsite['WWW_DIR'].'/'.$Campsite['DATABASE_NAME'].'/cgi-bin';

$ADMIN_DIR = "admin-files";
$ADMIN = "admin";

if (!$DO_NOT_CONNECT_TO_DB)
	require($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');

$scheme = (substr($_SERVER['REQUEST_URI'], 0, 5) == 'https')?'https://':'http://';
$Campsite['HOSTNAME'] = $_SERVER['SERVER_NAME'];
$Campsite['WEBSITE_URL'] = $scheme.$Campsite['HOSTNAME'];
unset($scheme);

$Campsite['VERSION'] = '2.2.0';

$Campsite['IMAGE_DIRECTORY'] = $Campsite['HTML_DIR'].'/images/';
$Campsite['IMAGE_BASE_URL'] = $Campsite['WEBSITE_URL'].'/images/';
$Campsite['IMAGE_PREFIX'] = 'cms-image-';
$Campsite['IMAGEMAGICK_INSTALLED'] = true;
$Campsite['THUMBNAIL_COMMAND'] = 'convert -sample 64x64';
$Campsite['THUMBNAIL_DIRECTORY'] = $Campsite['IMAGE_DIRECTORY'].'/thumbnails/';
$Campsite['THUMBNAIL_BASE_URL'] = $Campsite['WEBSITE_URL'].'/images/thumbnails/';
$Campsite['THUMBNAIL_PREFIX'] = 'cms-thumb-';
$Campsite['TMP_DIRECTORY'] = '/tmp/';

?>