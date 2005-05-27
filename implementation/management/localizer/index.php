<?php

include_once($_SERVER['DOCUMENT_ROOT']."/configuration.php");
include_once($_SERVER['DOCUMENT_ROOT']."/$ADMIN_DIR/CampsiteInterface.php");
include_once($_SERVER['DOCUMENT_ROOT']."/classes/Input.php");
include_once('Localizer.php');

$localizer =& Localizer::getInstance();
Localizer::LoadLanguageFiles('/localizer', 'locals');
Localizer::LoadLanguageFiles('/', 'globals');

global $g_translationStrings;

$action = Input::Get('action', 'string', 'translate', true);
//echo "Action: $action<br>";

// Show the converstion screen if this is the first time the
// user is using the new localizer.
//echo Localizer::GetMode()."<br>";
if (Localizer::GetMode() == 'php') {
	$action = 'convert';
}

if (isset($_REQUEST['find_translation_strings'])) {
	$action = 'find_translation_strings';
}

//echo "<pre>";
//print_r($g_translationStrings);
//print_r($_REQUEST);
//echo "</pre>";
?>
<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="1" WIDTH="100%" class="page_title_container">
<TR>
    <TD class="page_title">
        <?php  putGS("Localizer"); ?>
    </TD>
</TR>       
</TABLE>

<?php
switch ($action) {
case 'convert': 
	include('convert.php');
    break;
    
case 'convert_confirm':
	$languages = $localizer->getLanguages('php');
	// recursive convert GS-files to XML-files on filesystem
    $startdir = LOCALIZER_BASE_DIR.LOCALIZER_ADMIN_DIR;
    $pattern  = '/^(locals|globals)\.[a-z]{2,2}\.php$/';
    $sep = "|";
    $list = Localizer::SearchFilesRecursive($startdir, $pattern, $sep);
    $list = explode($sep, $list);
	?>
    <center>
    <?php putGS("Converting..."); ?><br>
    <div style="width: 700px; height: 400px; overflow: auto; border: 1px solid black;">
    <table>
    <?php
    foreach($list as $pathname) {
        if ($pathname) {
            $pathname = str_replace($startdir, '', $pathname);
            $base = explode('.', basename($pathname));
            $file = array('base' => $base[0],
                          'dir'  => dirname($pathname));
            foreach($languages as $lang) {
                if($lang['Code'] == $base[1]) {
                    $languageCode = $lang['Id'];
                    $sourceFile =& new LocalizerLanguage($file['base'], $file['dir'], $languageCode);
                    $sourceFile->loadGsFile();

                    $sourceFile->saveAsXml();
                    
                    // Verify that the saved file is the same as the original.
                    $copyLanguage =& new LocalizerLanguage($file['base'], $file['dir'], $languageCode);
                    $copyLanguage->loadXmlFile();
					
                    echo "<tr><td>$pathname: ";
                    if (!$copyLanguage->equal($sourceFile)) {
                    	echo "<font color='red'>";
                    	putGS("fail"); 
                    	echo ".</font>";                    	
                    }
                    else {
                    	echo "<font color='green'>";
                    	putGS("success");
                    	echo ".</font>";
                    	@unlink($sourceFile->getSourceFile());
                    }
                    Localizer::FixPositions($file['base'], $file['dir']);
                    echo "</td></tr>";
                }
            }
        }
    }
    ?>
    </table>
    </div>
    <br>    
    <?php
	break;

case 'translate':
    require_once("translate.php");    
    translationForm($_REQUEST);
	break;

case 'save_translation':
    $base = Input::Get('base');
    $directory = Input::Get('dir');
	if ($directory == '/globals') {
		$directory = '/';
		$base = 'globals';
	}
    $targetLanguageId = Input::Get('localizer_target_language');
    $data = Input::Get('data', 'array');
    Localizer::ModifyStrings($base, $directory, $targetLanguageId, $data);
    // Localizer strings are changed -> reload files
    Localizer::LoadLanguageFiles('/', 'globals');
    Localizer::LoadLanguageFiles('/localizer', 'locals'); 
    require_once("translate.php");    
    translationForm($_REQUEST);
	break;

case 'add_string':
	$base = Input::Get('base');
	$directory = Input::Get('dir');
	if ($directory == '/globals') {
		$directory = '/';
		$base = 'globals';
	}
	$pos = Input::Get('pos');
	if ($pos == 'begin') {
		$pos = 0;
	}
	elseif ($pos == 'end') {
		$pos = null;
	}

    $msg = Localizer::CompareKeys($directory, $_REQUEST['newKey']);
    if (count($msg) > 0) {
        foreach ($msg as $val => $err) {
            while ($key = array_search($val, $_REQUEST['newKey'])) {
                unset($_REQUEST['newKey'][$key]);
            }
        }
    }
	// skip if all was unset above
    if (count($_REQUEST['newKey'])) {  
        Localizer::AddStringAtPosition($base, $directory, $pos, $_REQUEST['newKey']);
    }

    require_once("translate.php");
    translationForm($_REQUEST);
	break;

case 'remove_string':
	$base = Input::Get('base');
	$directory = Input::Get('dir');
	if ($directory == '/globals') {
		$directory = '/';
		$base = 'globals';
	}
    $deleteMe = Input::Get('string', 'string');
    Localizer::RemoveString($base, $directory, $deleteMe);
    require_once("translate.php");    
    translationForm($_REQUEST);
	break;

case 'move_string':
	$base = Input::Get('base');
	$directory = Input::Get('dir');
	if ($directory == '/globals') {
		$directory = '/';
		$base = 'globals';
	}
	$pos1 = Input::Get('pos1', 'int');
	$pos2 = Input::Get('pos2', 'int');
    Localizer::MoveString($base, $directory, $pos1, $pos2);
    require_once("translate.php");    
    translationForm($_REQUEST);
	break;

case 'add_missing_translation_strings':
	$base = Input::Get('base');
	$directory = Input::Get('dir');
	if ($directory == '/globals') {
		$directory = '/';
		$base = 'globals';
	}
	//echo "Base: $base, Directory: $directory<br>";
	$missingStrings = Localizer::FindMissingStrings($directory);
    if (count($missingStrings) > 0) {
        Localizer::AddStringAtPosition($base, $directory, 0, $missingStrings);
    }
    require_once("translate.php");    
    translationForm($_REQUEST);
	break;
	
case 'delete_unused_translation_strings':
	$base = Input::Get('base');
	$directory = Input::Get('dir');
	if ($directory == '/globals') {
		$directory = '/';
		$base = 'globals';
	}
	//echo "Base: $base, Directory: $directory<br>";
	$unusedStrings = Localizer::FindUnusedStrings($directory);
    if (count($unusedStrings) > 0) {
       	Localizer::RemoveString($base, $directory, $unusedStrings);
    }
    require_once("translate.php");    
    translationForm($_REQUEST);
	break;
	
case 'newLangFilePref':
    $output .= Display::newLangFilePref($_REQUEST['dir'], $_REQUEST['denied']);
	break;

case 'newLangFileForm':
    $output .= Display::newLangFileForm($_REQUEST['amount'], $_REQUEST['base'], $_REQUEST['dir']);
	break;

case 'storeNewLangFile':
    $file = array('dir'         => $_REQUEST['dir'],
                  'base'        => $_REQUEST['base'],
                  'Id'          => LOCALIZER_DEFAULT_LANG
            );

    Localizer::AddStringAtPosition($_REQUEST['base'], $_REQUEST['dir'], 'new', $_REQUEST['newKey']);
	break;

case 'manageLanguages':
    $output .= Display::manageLangForm();
	break;

} // switch

//echo $error['msg'].$output;
?>
</body>
</html>