<?php 
require_once($_SERVER['DOCUMENT_ROOT']. "/$ADMIN_DIR/pub/issues/sections/articles/article_common.php");
require_once($_SERVER['DOCUMENT_ROOT']. '/classes/DbObjectArray.php');
require_once($_SERVER['DOCUMENT_ROOT']. '/classes/ArticlePublish.php');

list($access, $User) = check_basic_access($_REQUEST);
if (!$access) {
	header("Location: /$ADMIN/logout.php");
	exit;
}

$Pub = Input::Get('Pub', 'int', 0);
$Issue = Input::Get('Issue', 'int', 0);
$Section = Input::Get('Section', 'int', 0);
$Language = Input::Get('Language', 'int', 0);
$sLanguage = Input::Get('sLanguage', 'int', 0, true);
$ArticleOffset = Input::Get('ArtOffs', 'int', 0, true);
$ArticlesPerPage = Input::Get('lpp', 'int', 10, true);

if (!Input::IsValid()) {
	CampsiteInterface::DisplayError(array('Invalid input: $1', Input::GetErrorString()), $_SERVER['REQUEST_URI']);
	exit;	
}

if ($ArticleOffset < 0) {
	$ArticleOffset = 0;
}

$publicationObj =& new Publication($Pub);
if (!$publicationObj->exists()) {
	CampsiteInterface::DisplayError('Publication does not exist.');
	exit;	
}

$issueObj =& new Issue($Pub, $Language, $Issue);
if (!$issueObj->exists()) {
	CampsiteInterface::DisplayError('Issue does not exist.');
	exit;	
}

$sectionObj =& new Section($Pub, $Issue, $Language, $Section);
if (!$sectionObj->exists()) {
	CampsiteInterface::DisplayError('Section does not exist.', $BackLink);
	exit;		
}

$languageObj =& new Language($Language);
$sLanguageObj =& new Language($sLanguage);
$allArticleLanguages =& Article::GetAllLanguages();

if ($sLanguage) {
	// Only show a specific language.
	$allArticles = Article::GetArticles($Pub, $Issue, $Section, $sLanguage, $Language,
		$ArticlesPerPage, $ArticleOffset);
	$totalArticles = count(Article::GetArticles($Pub, $Issue, $Section, $sLanguage));
	$numUniqueArticles = $totalArticles;
	$numUniqueArticlesDisplayed = count($allArticles);
} else {
	// Show articles in all languages.
	$allArticles =& Article::GetArticles($Pub, $Issue, $Section, null, $Language,
		$ArticlesPerPage, $ArticleOffset, true);
	$totalArticles = count(Article::GetArticles($Pub, $Issue, $Section, null));
	$numUniqueArticles = Article::GetNumUniqueArticles($Pub, $Issue, $Section);
	$numUniqueArticlesDisplayed = count(array_unique(DbObjectArray::GetColumn($allArticles, 'Number')));
}

$previousArticleId = 0;

?>
<HEAD>
	<TITLE><?php  putGS("Articles"); ?></TITLE>
	<LINK rel="stylesheet" type="text/css" href="<?php echo $Campsite['WEBSITE_URL']; ?>/css/admin_stylesheet.css">	
</HEAD>
<BODY>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="1" WIDTH="100%" class="page_title_container">
<TR>
	<TD class="page_title">
	    <?php  putGS("Articles"); ?>
	</TD>
	<TD ALIGN="RIGHT">
		<TABLE BORDER="0" CELLSPACING="1" CELLPADDING="0">
		<TR>
			<TD><A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Language=<?php  p($Language); ?>" class="breadcrumb"><?php  putGS("Sections");  ?></A></TD>
			<td class="breadcrumb_separator">&nbsp;</td>
			<TD><A HREF="/<?php echo $ADMIN; ?>/pub/issues/?Pub=<?php  p($Pub); ?>"  class="breadcrumb"><?php  putGS("Issues");  ?></A></TD>
			<td class="breadcrumb_separator">&nbsp;</td>
			<TD><A HREF="/<?php echo $ADMIN; ?>/pub/" class="breadcrumb"><B><?php  putGS("Publications");  ?></B></A></TD>
		</TR>
		</TABLE>
	</TD>
</TR>
</TABLE>

<TABLE BORDER="0" CELLSPACING="1" CELLPADDING="1" WIDTH="100%" class="current_location_table">
<TR>
	<TD ALIGN="RIGHT" WIDTH="1%" NOWRAP VALIGN="TOP" class="current_location_title">&nbsp;<?php  putGS("Publication"); ?>:</TD>
	<TD VALIGN="TOP" class="current_location_content"><?php p(htmlspecialchars($publicationObj->getName())); ?></TD>

	<TD ALIGN="RIGHT" WIDTH="1%" NOWRAP VALIGN="TOP" class="current_location_title">&nbsp;<?php  putGS("Issue"); ?>:</TD>
	<TD VALIGN="TOP" class="current_location_content"><?php p($issueObj->getIssueId()); ?>. <?php  p(htmlspecialchars($issueObj->getName())); ?> (<?php p(htmlspecialchars($languageObj->getName())); ?>)</TD>

	<TD ALIGN="RIGHT" WIDTH="1%" NOWRAP VALIGN="TOP" class="current_location_title">&nbsp;<?php  putGS("Section"); ?>:</TD>
	<TD VALIGN="TOP" class="current_location_content"><?php p($sectionObj->getSectionId()); ?>. <?php  p(htmlspecialchars($sectionObj->getName())); ?></TD>
</TR>
</TABLE>

<P>
<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
<TR>
<?php if ($User->hasPermission('AddArticle')) { ?>
	<TD>
		<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="1">
		<TR>
			<TD><A HREF="add.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Language=<?php  p($Language); ?>&Back=<?php p(urlencode($_SERVER['REQUEST_URI'])); ?>" ><IMG SRC="/<?php echo $ADMIN; ?>/img/icon/add_article.png" BORDER="0"></A></TD>
			<TD><A HREF="add.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Language=<?php  p($Language); ?>&Back=<?php  p(urlencode($_SERVER['REQUEST_URI'])); ?>" ><B><?php  putGS("Add new article"); ?></B></A></TD>
		</TR>
		</TABLE>
	</TD>
<?php  } ?>
	<TD ALIGN="RIGHT">
		<FORM METHOD="GET" ACTION="index.php" NAME="">
		<INPUT TYPE="HIDDEN" NAME="Pub" VALUE="<?php  p($Pub); ?>">
		<INPUT TYPE="HIDDEN" NAME="Issue" VALUE="<?php  p($Issue); ?>">
		<INPUT TYPE="HIDDEN" NAME="Section" VALUE="<?php  p($Section); ?>">
		<INPUT TYPE="HIDDEN" NAME="Language" VALUE="<?php  p($Language); ?>">
		<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="3" class="table_input">
		<TR>
			<TD><?php  putGS('Language'); ?>:</TD>
			<TD valign="middle">
				<SELECT NAME="sLanguage" class="input_select">
				<option></option>
				<?php 
				foreach ($allArticleLanguages as $languageItem) {
					echo '<OPTION value="'.$languageItem->getLanguageId().'"' ;
					if ($languageItem->getLanguageId() == $sLanguage) {
						echo " selected";
					}
					echo '>'.htmlspecialchars($languageItem->getName()).'</option>';
				} ?>
				</SELECT>
			</TD>
			<TD><INPUT TYPE="submit" NAME="Search" VALUE="<?php  putGS('Search'); ?>" class="button"></TD>
		</TR>
		</TABLE>
		</FORM>
	</TD>
</tr>
</TABLE>

<P>
<?php 
if ($numUniqueArticlesDisplayed > 0) {
	$counter = 0;
	$color = 0;
?>
<TABLE BORDER="0" CELLSPACING="1" CELLPADDING="3" WIDTH="100%" class="table_list">
<TR class="table_list_header">
	<TD ALIGN="LEFT" VALIGN="TOP"  ><?php  putGS("Name<BR><SMALL>(click to edit)</SMALL>"); ?></TD>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Type"); ?></TD>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Language"); ?></TD>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Status"); ?></TD>
	<?php if ($User->hasPermission('Publish')) { ?>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Order"); ?></TD>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Automatic publishing"); ?></TD>
	<?php } ?>	
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Preview"); ?></TD>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Translate"); ?></TD>
	<?php  if ($User->hasPermission('AddArticle')) { ?>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Duplicate"); ?></TD>
	<?php  } ?>
	<?php  if ($User->hasPermission('DeleteArticle')) { ?>
	<TD ALIGN="center" VALIGN="TOP" WIDTH="1%" ><?php  putGS("Delete"); ?></TD>
	<?php  } ?>	
</TR>
<?php 
$uniqueArticleCounter = 0;
foreach ($allArticles as $articleObj) {
	if ($articleObj->getArticleId() != $previousArticleId) {
		$uniqueArticleCounter++;
	}
	if ($uniqueArticleCounter > $ArticlesPerPage) {
		break;
	}
	?>	
	<TR <?php  if ($color) { $color=0; ?>class="list_row_even"<?php  } else { $color=1; ?>class="list_row_odd"<?php  } ?>>
		<TD <?php if ($articleObj->getArticleId() == $previousArticleId) { ?>style="padding-left: 20px;"<?php } ?>>
		<?php
		// Can the user edit the article?
		$userCanEdit = $articleObj->userCanModify($User);
		if ($userCanEdit) { ?>
		<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/edit.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php p($articleObj->getLanguageId()); ?>"><?php } ?><?php  p(htmlspecialchars($articleObj->getTitle())); ?>&nbsp;<?php if ($userCanEdit) { ?></A><?php } ?>
		</TD>
		<TD ALIGN="RIGHT">
			<?php p(htmlspecialchars($articleObj->getType()));  ?>
		</TD>

		<TD>
			<?php
			p(htmlspecialchars($articleObj->getLanguageName())); 
			?>
		</TD>
		<TD ALIGN="CENTER">
			<?php 
			$statusLink = "<A HREF=\"/$ADMIN/pub/issues/sections/articles/status.php?Pub=". $Pub
				.'&Issue='.$Issue.'&Section='.$Section.'&Article='.$articleObj->getArticleId()
				.'&Language='.$Language.'&sLanguage='.$articleObj->getLanguageId()
				.'&Back='.urlencode($_SERVER['REQUEST_URI']).'">';
			if ($articleObj->getPublished() == "Y") { 
				$statusWord = "Published";
			}
			elseif ($articleObj->getPublished() == "N") { 
				$statusWord = "New";
			}
			elseif ($articleObj->getPublished() == "S") { 
				$statusWord = "Submitted";
			}
			$enableStatusLink = false;
			if ($User->hasPermission('Publish')) {
				$enableStatusLink = true;
			}
			elseif ($User->hasPermission('ChangeArticle') 
					&& ($articleObj->getPublished() != 'Y')) {
				$enableStatusLink = true;
			}
			elseif ( ($User->getId() == $articleObj->getUserId())
					&& ($articleObj->getPublished() == "N")) {
				$enableStatusLink = true;
			}
			if ($enableStatusLink) {
				echo $statusLink;
			}
			putGS($statusWord);
			if ($enableStatusLink) {
				echo "</a>";
			}
			?>
		</TD>
		
		<?php
		// The MOVE links  
		if ($User->hasPermission('Publish')) { 
			if (($articleObj->getArticleId() == $previousArticleId) || ($numUniqueArticles <= 1))  {
				?>
				<TD ALIGN="CENTER" valign="middle" NOWRAP></TD>
				<?php
			}
			else {
				?>
				<TD ALIGN="right" valign="middle" NOWRAP>
					<table cellpadding="0" cellspacing="0">
					<tr>
						<td width="22px">
							<?php if (($ArticleOffset > 0) || ($uniqueArticleCounter != 1)) { ?>
								<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/do_move.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php p($articleObj->getLanguageId()); ?>&ArticleLanguage=<?php p($articleObj->getLanguageId()); ?>&move=up_rel&pos=1&ArtOffs=<?php p($ArticleOffset); ?>&Back=<?php p(urlencode($_SERVER['REQUEST_URI'])); ?>"><img src="/<?php echo $ADMIN; ?>/img/icon/up.png" width="16" height="16" border="0"></A>
							<?php } ?>
						</td>
						<td width="22px">
							<?php if (($uniqueArticleCounter+$ArticleOffset) < $numUniqueArticles) { ?>
								<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/do_move.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php p($articleObj->getLanguageId()); ?>&ArticleLanguage=<?php p($articleObj->getLanguageId()); ?>&move=down_rel&pos=1&ArtOffs=<?php p($ArticleOffset); ?>&Back=<?php p(urlencode($_SERVER['REQUEST_URI'])); ?>"><img src="/<?php echo $ADMIN; ?>/img/icon/down.png" width="16" height="16" border="0" style="padding-left: 3px; padding-right: 3px;"></A>
							<?php } ?>
						</td>
						<form method="GET" action="do_move.php">
						<input type="hidden" name="Pub" value="<?php p($Pub); ?>">
						<input type="hidden" name="Issue" value="<?php p($Issue); ?>">
						<input type="hidden" name="Section" value="<?php p($Section); ?>">
						<input type="hidden" name="Language" value="<?php p($Language); ?>">
						<input type="hidden" name="sLanguage" value="<?php p($sLanguage); ?>">
						<input type="hidden" name="ArticleLanguage" value="<?php p($articleObj->getLanguageId()); ?>">
						<input type="hidden" name="Article" value="<?php p($articleObj->getArticleId()); ?>">
						<input type="hidden" name="ArtOffs" value="<?php p($ArticleOffset); ?>">
						<input type="hidden" name="Back" value="<?php p($_SERVER['REQUEST_URI']); ?>">
						<input type="hidden" name="move" value="abs">
						<td>
							<select name="pos" onChange="this.form.submit();" class="input_select">
							<?php
							for ($j = 1; $j <= $numUniqueArticles; $j++) {
								if (($ArticleOffset + $uniqueArticleCounter) == $j) {
									echo "<option value=\"$j\" selected>$j</option>\n";
								} else {
									echo "<option value=\"$j\">$j</option>\n";
								}
							}
							?>
							</select>
						</td>
						</form>
					</tr>
					</table>
				</TD>
				<?php  
				}
		} // if user->hasPermission('publish') 
		?>
		
		<?php if ($User->hasPermission('Publish')) { ?>
		<TD ALIGN="CENTER">
			<?php if ($articleObj->getPublished() != 'N') { 
				$events =& ArticlePublish::GetArticleEvents($articleObj->getArticleId(),
					$articleObj->getLanguageId());?>
			<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/autopublish.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language);?>&sLanguage=<?php p($articleObj->getLanguageId()); ?>"><img src="/<?php p($ADMIN); ?>/img/icon/<?php p((count($events) > 0) ? 'automatic_publishing_active.png':'automatic_publishing.png'); ?>" alt="<?php  putGS("Automatic publishing"); ?>" border="0" width="22" height="22"></A>
			<?php 
			} else { ?>
				&nbsp;<?PHP
			}
			?>
		</TD>
		<?php } ?>
		<TD ALIGN="CENTER">
			<A HREF="" ONCLICK="window.open('/<?php echo $ADMIN; ?>/pub/issues/sections/articles/preview.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php  p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php  p($articleObj->getLanguageId()); ?>', 'fpreview', 'resizable=yes, menubar=no, toolbar=yes, width=800, height=600'); return false"><img src="/<?php p($ADMIN); ?>/img/icon/preview.png" alt="<?php  putGS("Preview"); ?>" border="0" width="22" height="22"></A>
		</TD>
		<TD ALIGN="CENTER">
			<?php  if ($articleObj->getArticleId() != $previousArticleId) { ?>
			<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/translate.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php p($articleObj->getLanguageId()); ?>&Back=<?php p(urlencode($_SERVER['REQUEST_URI'])); ?>"><img src="/<?php p($ADMIN); ?>/img/icon/translate.png" alt="<?php  putGS("Translate"); ?>" border="0" width="22" height="22"></A>
			<?php  } else { ?>
				&nbsp;
			<?php  } ?>
		</TD>
		
		<?php  if ($User->hasPermission('AddArticle')) { ?>
		<TD ALIGN="CENTER">
			<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/duplicate.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php  p($articleObj->getLanguageId()); ?>&Back=<?php p(urlencode($_SERVER['REQUEST_URI'])); ?>"><img src="/<?php p($ADMIN); ?>/img/icon/duplicate.png" alt="<?php  putGS("Duplicate"); ?>" border="0" width="22" height="22"></A>
		</TD>
		<?php  } ?>

		<?php  if ($User->hasPermission('DeleteArticle')) { ?>
		<TD ALIGN="CENTER">
			<A HREF="/<?php echo $ADMIN; ?>/pub/issues/sections/articles/do_del.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Article=<?php p($articleObj->getArticleId()); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php p($articleObj->getLanguageId()); ?>" onclick="return confirm('<?php htmlspecialchars(putGS('Are you sure you want to delete the article $1 ($2)?', '&quot;'.htmlspecialchars($articleObj->getTitle(), ENT_QUOTES).'&quot', htmlspecialchars($articleObj->getLanguageName(), ENT_QUOTES))); ?>');"><IMG SRC="/<?php echo $ADMIN; ?>/img/icon/delete.png" BORDER="0" ALT="<?php  putGS('Delete article $1', $articleObj->getTitle()); ?>" width="16" height="16"></A>
		</TD>
		<?php  }
		if ($articleObj->getArticleId() != $previousArticleId)
			$previousArticleId = $articleObj->getArticleId();
		?>	
	</TR>
	<?php 
} // foreach
?>	
<TR>
	<TD NOWRAP>
		<?php 
    	if ($ArticleOffset > 0) { ?>
			<B><A HREF="index.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php  p($sLanguage); ?>&ArtOffs=<?php  p(max(0, ($ArticleOffset - $ArticlesPerPage))); ?>">&lt;&lt; <?php  putGS('Previous'); ?></A></B>
		<?php  }

    	if ( ($ArticleOffset + $ArticlesPerPage) < $numUniqueArticles) { 
    		if ($ArticleOffset > 0) {
    			?>|<?php
    		}
    		?>
			 <B><A HREF="index.php?Pub=<?php  p($Pub); ?>&Issue=<?php  p($Issue); ?>&Section=<?php  p($Section); ?>&Language=<?php  p($Language); ?>&sLanguage=<?php  p($sLanguage); ?>&ArtOffs=<?php  p(min( ($numUniqueArticles-1), ($ArticleOffset + $ArticlesPerPage))); ?>"><?php  putGS('Next'); ?> &gt;&gt</A></B>
		<?php  } ?>
	</TD>
	<td colspan="3">
		<?php putGS("$1 articles found", $numUniqueArticles); ?>
	</td>
</TR>
</TABLE>
<?php  } else { ?><BLOCKQUOTE>
	<LI><?php  putGS('No articles.'); ?></LI>
	</BLOCKQUOTE>
<?php  } ?>
<?php CampsiteInterface::CopyrightNotice(); ?>