<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/DatabaseObject.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/ArticleType.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/ArticleImage.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/ArticleTopic.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/ArticleIndex.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/Language.php');

class Article extends DatabaseObject {
	/**
	 * The column names used for the primary key.
	 * @var array
	 */
	var $m_keyColumnNames = array('IdPublication',
						   		  'NrIssue',
							   	  'NrSection',
							   	  'Number',
							   	  'IdLanguage');

	var $m_dbTableName = 'Articles';
	
	var $m_columnNames = array(
		// int - Publication ID 
		'IdPublication', 
		
		// int -Issue ID
		'NrIssue', 
		
		// int - Section ID
		'NrSection', 

		// int - Article ID
		'Number', 

		// int - Language ID,
		'IdLanguage',

		// string - Article Type
		'Type',
	
		// int - User ID of user who created the article
		'IdUser',
	
		// string - The title of the article.
		'Name',
	
		// string
		// Whether the article is on the front page or not.
	  	// This is represented as 'N' or 'Y'.
		'OnFrontPage',
	
		/**
		 * Whether or not the article is on the section or not.
		 * This is represented as 'N' or 'Y'.
		 * @var string
		 */
		'OnSection',
		'Published',
		'UploadDate',
		'Keywords',
		'Public',
		'IsIndexed',
		'LockUser',
		'LockTime',
		'ShortName',
		'ArticleOrder');
		
	var $m_languageName = null;
	
	/**
	 * Construct by passing in the primary key to access the article in 
	 * the database.
	 *
	 * @param int p_publicationId
	 *
	 * @param int p_issueId
	 *
	 * @param int p_sectionId
	 *
	 * @param int p_languageId
	 *
	 * @param int p_articleId
	 *		Not required when creating an article.
	 */
	function Article($p_publicationId = null, $p_issueId = null, $p_sectionId = null, 
					 $p_languageId = null, $p_articleId = null) 
	{
		parent::DatabaseObject($this->m_columnNames);
		$this->m_data['IdPublication'] = $p_publicationId;
		$this->m_data['NrIssue'] = $p_issueId;
		$this->m_data['NrSection'] = $p_sectionId;
		$this->m_data['IdLanguage'] = $p_languageId;
		$this->m_data['Number'] = $p_articleId;
		if (!is_null($p_publicationId) && !is_null($p_issueId)
			&& !is_null($p_sectionId) && !is_null($p_languageId) 
			&& !is_null($p_articleId)) {
			$this->fetch();
		}
	} // constructor
	
	
	/**
	 * A way for internal functions to call the superclass create function.
	 */
	function __create($p_values = null) { return parent::create($p_values); }
	
	/**
	 * Create an article in the database.  Use the SET functions to
	 * change individual values.
	 *
	 * @param string p_articleType
	 * @param string p_name
	 *
	 * @return void
	 */
	function create($p_articleType, $p_name = null) {
		global $Campsite;
		// Create the article ID.
		$queryStr = 'UPDATE AutoId SET ArticleId=LAST_INSERT_ID(ArticleId + 1)';
		$Campsite['db']->Execute($queryStr);
		if ($Campsite['db']->Affected_Rows() <= 0) {
			// If we were not able to get an ID, dont try to create the article.
			return;
		}
		$this->m_data['Number'] = $Campsite['db']->Insert_ID();
	
		// Create the record
		$values = array();
		if (!is_null($p_name)) {
			$values['Name'] = $p_name;
		}
		$values['ShortName'] = $this->m_data['Number'];
		$values['Type'] = $p_articleType;
		$values['Public'] = 'Y';
		$success = parent::create($values);
		if (!$success) {
			return;
		}
		$this->setProperty('UploadDate', 'NOW()', true, true);
		$this->fetch();
	
		// Insert an entry into the article type table.
		$articleData =& new ArticleType($this->m_data['Type'], 
			$this->m_data['Number'], 
			$this->m_data['IdLanguage']);
		$articleData->create();
	} // fn create

	
	/**
	 * Create a copy of this article.
	 *
	 * @param int p_destPublication
	 *		The destination publication ID.
	 *
	 * @param int p_destIssue
	 *		The destination issue ID.
	 *
	 * @param int p_destSection
	 * 		The destination section ID.
	 *
	 * @param int p_userId
	 *		The user creating the copy.
	 *
	 * @return Article
	 *		The copied Article object.
	 */
	function copy($p_destPublication, $p_destIssue, $p_destSection, $p_userId) {
		global $Campsite;
		// Create the duplicate article object.
		$articleCopy =& new Article($p_destPublication, $p_destIssue, 
			$p_destSection, $this->m_data['IdLanguage']);
		$articleCopy->create($this->m_data['Type']);
		
		// Change some attributes
		$articleCopy->m_data['IdUser'] = $p_userId;
		$articleCopy->m_data['Published'] = 'N';
		$articleCopy->m_data['IsIndexed'] = 'N';		
		$articleCopy->m_data['LockUser'] = 0;
		$articleCopy->m_data['LockTime'] = 0;
		// Create a unique name for the new copy
		$origNewName = $this->m_data['Name'] . " (duplicate";
		$newName = $origNewName .")";
		$count = 1;
		while (true) {
			$queryStr = 'SELECT * FROM Articles '
						.' WHERE IdPublication = '.$p_destPublication
						.' AND NrIssue = ' . $p_destIssue
						.' AND NrSection = ' . $p_destSection
						.' AND IdLanguage = ' . $this->m_data['IdLanguage']
						." AND Name = '$newName'";
			$row = $Campsite['db']->GetRow($queryStr);
			if (count($row) > 0) {
				$newName = $origNewName.' '.++$count.')';
			}
			else {
				break;
			}
		}
		$articleCopy->m_data['Name'] = $newName;
		
		// Copy some attributes
		$articleCopy->m_data['OnFrontPage'] = $this->m_data['OnFrontPage'];
		$articleCopy->m_data['OnSection'] = $this->m_data['OnFrontPage'];
		$articleCopy->m_data['Keywords'] = $this->m_data['Keywords'];
		$articleCopy->m_data['Public'] = $this->m_data['Public'];
		$articleCopy->m_data['ArticleOrder'] = $this->m_data['Number'];
		
		$articleCopy->commit();

		$articleData =& $this->getArticleTypeObject();
		$articleData->copyToExistingRecord($articleCopy->getArticleId());
		
		// Copy image pointers
		ArticleImage::OnArticleCopy($this->m_data['Number'], $articleCopy->getArticleId());

		// Copy topic pointers
		ArticleTopic::OnArticleCopy($this->m_data['Number'], $articleCopy->getArticleId());
		
		return $articleCopy;
	} // fn copy
	

	/**
	 * Create a copy of the article, but make it a translation
	 * of the current one.
	 *
	 * @param int p_languageId
	 * @param int p_userId
	 * @param string p_name
	 * @return Article
	 */
	function createTranslation($p_languageId, $p_userId, $p_name) {
		global $Campsite;
		// Construct the duplicate article object.
		$articleCopy =& new Article();
		$articleCopy->m_data['IdPublication'] = $this->m_data['IdPublication']; 
		$articleCopy->m_data['NrIssue'] = $this->m_data['NrIssue']; 
		$articleCopy->m_data['NrSection'] = $this->m_data['NrSection']; 
		$articleCopy->m_data['IdLanguage'] = $p_languageId; 
		$articleCopy->m_data['Number'] = $this->m_data['Number']; 
		$values = array();
		// Copy some attributes
		$values['ShortName'] = $this->m_data['ShortName'];
		$values['Name'] = $p_name;
		$values['Type'] = $this->m_data['Type'];
		$values['IsIndexed'] = $this->m_data['IsIndexed'];		
		$values['OnFrontPage'] = $this->m_data['OnFrontPage'];
		$values['OnSection'] = $this->m_data['OnFrontPage'];
		$values['Public'] = $this->m_data['Public'];
		$values['ArticleOrder'] = $this->m_data['ArticleOrder'];
		// Change some attributes
		$values['LockUser'] = 0;
		$values['LockTime'] = 0;
		$values['IdUser'] = $p_userId;
		$values['Published'] = 'N';

		// Create the record
		$success = $articleCopy->__create($values);
		if (!$success) {
			return;
		}

		$articleCopy->setProperty('UploadDate', 'NOW()', true, true);

		// Insert an entry into the article type table.
		$articleCopyData =& new ArticleType($articleCopy->m_data['Type'], 
			$articleCopy->m_data['Number'], $articleCopy->m_data['IdLanguage']);
		$articleCopyData->create();
		
		$origArticleData =& $this->getArticleTypeObject();
		$origArticleData->copyToExistingRecord($articleCopy->getArticleId(), $p_languageId);
		
		return $articleCopy;
	} // fn createTranslation
	
	
	/**
	 * Delete article from database.  This will 
	 * only delete one specific translation of the article.
	 */
	function delete() {
		// Delete row from article type table.
		$articleData =& new ArticleType($this->m_data['Type'], 
			$this->m_data['Number'], 
			$this->m_data['IdLanguage']);
		$articleData->delete();
		
		// is this the last translation?
		if (count($this->getLanguages()) <= 1) {
			// Delete image pointers
			ArticleImage::OnArticleDelete($this->m_data['Number']);
			
			// Delete topics pointers
			ArticleTopic::OnArticleDelete($this->m_data['Number']);
			
			// Delete indexes
			ArticleIndex::OnArticleDelete($this->getPublicationId(), $this->getIssueId(),
				$this->getSectionId(), $this->getLanguageId(), $this->getArticleId());
		}
					
		// Delete row from Articles table.
		parent::delete();
	} // fn delete
	
	
	/**
	 * Lock the article with the given User ID.
	 *
	 * @param int p_userId
	 *
	 */
	function lock($p_userId) {
		global $Campsite;
		$queryStr = 'UPDATE Articles '
					.' SET LockUser='.$p_userId
					.', LockTime=NOW() '
					.' WHERE '. $this->getKeyWhereClause();
		$Campsite['db']->Execute($queryStr);
	} // fn lock

	
	/**
	 * Unlock the article so anyone can edit it.
	 * @return void
	 */
	function unlock() {
		global $Campsite;
		$queryStr = 'UPDATE Articles '
					.' SET LockUser=0'
					.' WHERE '. $this->getKeyWhereClause();
		$Campsite['db']->Execute($queryStr);		
	} // fn unlock
	
	
	/**
	 * Return an array of Langauge objects, one for each
	 * type of language the article is written in.
	 *
	 * @return array
	 */
	function getLanguages() {
		global $Campsite;
	 	$queryStr = 'SELECT IdLanguage FROM Articles '
	 				.' WHERE IdPublication='.$this->m_data['IdPublication']
	 				.' AND NrIssue='.$this->m_data['NrIssue']
	 				.' AND NrSection='.$this->m_data['NrSection']
	 				.' AND Number='.$this->m_data['Number'];
	 	$languageIds = $Campsite['db']->GetCol($queryStr);
	 	$languages = array();
	 	if (is_array($languageIds)) {
			foreach ($languageIds as $languageId) {
				$languages[] =& new Language($languageId);
			}
	 	}
		return $languages;
	} // fn getLanguages
	
	
	/**
	 * Return an array of Article objects, one for each
	 * type of language the article is written in.
	 *
	 * @return array
	 */
	function getTranslations() {
		global $Campsite;
	 	$queryStr = 'SELECT '. implode(',', $this->m_columnNames).' FROM Articles '
	 				.' WHERE IdPublication='.$this->m_data['IdPublication']
	 				.' AND NrIssue='.$this->m_data['NrIssue']
	 				.' AND NrSection='.$this->m_data['NrSection']
	 				.' AND Number='.$this->m_data['Number'];
	 	$rows = $Campsite['db']->GetAll($queryStr);
	 	$articles = array();
	 	if (is_array($rows)) {
			foreach ($rows as $row) {
				$tmpArticle =& new Article($row['IdPublication'], $row['NrIssue'], 
					$row['NrSection'], $row['IdLanguage']);
				$tmpArticle->fetch($row);
				$articles[] =& $tmpArticle;
			}
	 	}
		return $articles;
	} // fn getTranslations
	
	
	/**
	 * A simple way to get the name of the language the article is 
	 * written in.  The value is cached in case there are multiple
	 * calls to this function.
	 *
	 * @return string
	 */
	function getLanguageName() {
		if (is_null($this->m_languageName)) {
			$language =& new Language($this->m_data['IdLanguage']);
			$this->m_languageName = $language->getName();
		}
		return $this->m_languageName;
	} // fn getLanguageName
	
	
	/**
	 * Get the section that this article is in.
	 * @return object
	 */
	function getSection() {
		global $Campsite;
	    $queryStr = 'SELECT * FROM Sections '
	    			.' WHERE IdPublication='.$this->getPublicationId()
	    			.' AND NrIssue='.$this->getIssueId()
	    			.' AND IdLanguage='.$this->getLanguageId();
		$query = $Campsite['db']->Execute($queryStr);
		if ($query->RecordCount() <= 0) {
			$queryStr = 'SELECT * FROM Sections '
						.' WHERE IdPublication='.$this->getPublicationId()
						.' AND NrIssue='.$this->getIssueId()
						.' LIMIT 1';
			$query = $Campsite['db']->Execute($queryStr);	
		}
		$row = $query->FetchRow();
		$section =& new Section($this->getPublicationId(), $this->getIssueId(),
			$this->getLanguageId());
		$section->fetch($row);
	    return $section;
	} // fn getSection
	
	
	/**
	 * Change the article's position in the order sequence
	 * relative to its current position.
	 *
	 * @param string p_direction
	 * 		Can be "up" or "down".
	 *
	 * @param int p_spacesToMove
	 *		The number of spaces to move the article.
	 *
	 * @return boolean
	 */
	function moveRelative($p_direction, $p_spacesToMove = 1)	{
		global $Campsite;
		
		// Get the article that is in the final position where this
		// article will be moved to.
		$compareOperator = ($p_direction == 'up') ? '<' : '>';
		$order = ($p_direction == 'up') ? 'desc' : 'asc';
		$queryStr = 'SELECT DISTINCT(Number), ArticleOrder FROM Articles '
					.' WHERE IdPublication='.$this->m_data['IdPublication']
					.' AND NrIssue='.$this->m_data['NrIssue']
					.' AND NrSection='.$this->m_data['NrSection']
					.' AND ArticleOrder '.$compareOperator.' '.$this->m_data['ArticleOrder']
					//.' AND IdLanguage='.$this->m_data['IdLanguage']
					.' ORDER BY ArticleOrder ' . $order
		     		.' LIMIT '.($p_spacesToMove-1).', 1';
		$destRow = $Campsite['db']->GetRow($queryStr);
		if (!$destRow) {
			return false;
		}
		// Shift all articles one space between the source and destination article.
		$operator = ($p_direction == 'up') ? '+' : '-';
		$minArticleOrder = min($destRow['ArticleOrder'], $this->m_data['ArticleOrder']);
		$maxArticleOrder = max($destRow['ArticleOrder'], $this->m_data['ArticleOrder']);
		$queryStr = 'UPDATE Articles SET ArticleOrder = ArticleOrder '.$operator.' 1 '
					.' WHERE IdPublication = '. $this->m_data['IdPublication']
					.' AND NrIssue = ' . $this->m_data['NrIssue']
					.' AND NrSection = ' . $this->m_data['NrSection']
		     		.' AND ArticleOrder >= '.$minArticleOrder
		     		.' AND ArticleOrder <= '.$maxArticleOrder;
		$Campsite['db']->Execute($queryStr);
		
		// Change position of this article to the destination position.
		$queryStr = 'UPDATE Articles SET ArticleOrder = ' . $destRow['ArticleOrder']
					.' WHERE IdPublication = '. $this->m_data['IdPublication']
					.' AND NrIssue = ' . $this->m_data['NrIssue']
					.' AND NrSection = ' . $this->m_data['NrSection']
		     		.' AND Number = ' . $this->m_data['Number'];
		$Campsite['db']->Execute($queryStr);

		return true;
	} // fn moveRelative
	
	
	/**
	 * 
	 * @param int p_position
	 * @return boolean
	 */
	function moveAbsolute($p_moveToPosition = 1) {
		global $Campsite;
		// Get the article that is in the location we are moving
		// this one to.
		$queryStr = 'SELECT DISTINCT(Number), ArticleOrder FROM Articles '
					.' WHERE IdPublication='.$this->m_data['IdPublication']
					.' AND NrIssue='.$this->m_data['NrIssue']
					.' AND NrSection='.$this->m_data['NrSection']
		     		.' ORDER BY ArticleOrder ASC LIMIT '.($p_moveToPosition - 1).', 1';
		$destRow = $Campsite['db']->GetRow($queryStr);
		if (!$destRow) {
			return false;
		}
		if ($destRow['ArticleOrder'] == $this->m_data['ArticleOrder']) {
			return true;
		}
		if ($destRow['ArticleOrder'] > $this->m_data['ArticleOrder']) {
			$operator = '-';
		} else {
			$operator = '+';
		}
		// Reorder all the other articles in this section
		$minArticleOrder = min($destRow['ArticleOrder'], $this->m_data['ArticleOrder']);
		$maxArticleOrder = max($destRow['ArticleOrder'], $this->m_data['ArticleOrder']);
		$queryStr = 'UPDATE Articles '
					.' SET ArticleOrder = ArticleOrder '.$operator.' 1 '
					.' WHERE IdPublication='.$this->m_data['IdPublication']
					.' AND NrIssue='.$this->m_data['NrIssue']
					.' AND NrSection='.$this->m_data['NrSection']
		     		.' AND ArticleOrder >= '.$minArticleOrder
		     		.' AND ArticleOrder <= '.$maxArticleOrder;
		$Campsite['db']->Execute($queryStr);
		
		// Reposition this article.
		$queryStr = 'UPDATE Articles '
					.' SET ArticleOrder='.$destRow['ArticleOrder']
					.' WHERE IdPublication='.$this->m_data['IdPublication']
					.' AND NrIssue='.$this->m_data['NrIssue']
					.' AND NrSection='.$this->m_data['NrSection']
		     		.' AND Number='.$this->m_data['Number'];
		$Campsite['db']->Execute($queryStr);
		return true;
	} // fn moveAbsolute
	

	/**
	 * Get the name of the dynamic article type table.
	 *
	 * @return string
	 */
	function getArticleTypeTableName() {
		return 'X'.$this->m_data['Type'];
	} // fn getArticleTypeTableName
	
	
	/**
	 * @return int
	 */
	function getPublicationId() {
		return $this->getProperty('IdPublication');
	} // fn getPublicationId
	
	
	/**
	 * @return int
	 */
	function getIssueId() {
		return $this->getProperty('NrIssue');
	} // fn getIssueId
	
	
	/**
	 * @return int
	 */
	function getSectionId() {
		return $this->getProperty('NrSection');
	} // fn getSectionId
	
	
	/**
	 * @return int
	 */
	function getLanguageId() {
		return $this->getProperty('IdLanguage');
	} // fn getLanguageId
	
	
	/**
	 * @return int
	 */ 
	function getArticleId() {
		return $this->getProperty('Number');
	} // fn getArticleId
	
	
	/**
	 * @return string
	 */
	function getTitle() {
		return $this->getProperty('Name');
	} // fn getTitle
	
	
	/**
	 * @return string
	 */
	function getName() {
		return $this->getProperty('Name');
	} // fn getName
	
	
	/**
	 * Set the title of the article.
	 *
	 * @param string title
	 *
	 * @return void
	 */
	function setTitle($p_title) {
		return parent::setProperty('Name', $p_title);
	} // fn setTitle

	
	/**
	 * Get the article type.
	 * @return string
	 */
	function getType() {
		return $this->getProperty('Type');
	} // fn getType
	

	/**
	 * Return the user ID of the user who created this article.
	 * @return int
	 */
	function getUserId() {
		return $this->getProperty('IdUser');
	} // fn getUserId
	
	
	/**
	 * @param int value
	 */
	function setUserId($value) {
		return parent::setProperty('IdUser', $value);
	}
	
	
	/**
	 * @return int
	 */
	function getOrder() {
		return $this->getProperty('ArticleOrder');
	} // fn getOrder
	
	
	/**
	 * Return true if the article is on the front page.
	 * @return boolean
	 */
	function onFrontPage() {
		return ($this->getProperty('OnFrontPage') == 'Y');
	} // fn onFrontPage
	
	
	/**
	 * @param boolean value
	 */
	function setOnFrontPage($p_value) {
		return parent::setProperty('OnFrontPage', $p_value?'Y':'N');
	} // fn setOnFrontPage
	
	
	/**
	 * @return boolean
	 */
	function onSection() {
		return ($this->getProperty('OnSection') == 'Y');
	} // fn onSection
	
	
	/**
	 * @param boolean value
	 */
	function setOnSection($p_value) {
		return parent::setProperty('OnSection', $p_value?'Y':'N');
	} // fn setOnSection
	
	
	/**
	 * @return string
	 * 		Can be 'Y', 'S', or 'N'.
	 */
	function getPublished() {
		return $this->getProperty('Published');
	} // fn isPublished
	
	
	/**
	 * Set the published state of the article.  
	 * Can be 'Y' = 'Yes', 'S' = 'Submitted', or 'N' = 'No'.
	 * @param string value
	 */
	function setPublished($p_value) {
		return parent::setProperty('Published', $p_value);
	} // fn setIsPublished
	
	
	/**
	 * Return the date the article was created in the form YYYY-MM-DD.
	 * @return string
	 */
	function getUploadDate() {
		return $this->getProperty('UploadDate');
	} // fn getUploadDate
	
	
	/**
	 * @return string
	 */
	function getKeywords() {
		return $this->getProperty('Keywords');
	} // fn getKeywords
	
	
	/**
	 * @param string $value
	 */
	function setKeywords($p_value) {
		return parent::setProperty('Keywords', $p_value);
	} // fn setKeywords
	
	
	/**
	 * @return boolean
	 */
	function isPublic() {
		return ($this->getProperty('Public') == 'Y');
	} // fn isPublic
	
	
	/**
	 *
	 * @param boolean value
	 */
	function setIsPublic($p_value) {
		return parent::setProperty('Public', $p_value?'Y':'N');
	} // fn setIsPublic
	
	
	/**
	 * @return boolean
	 */
	function isIndexed() {
		return ($this->getProperty('IsIndexed') == 'Y');
	} // fn isIndexed
	
	
	/**
	 * @param boolean value
	 */
	function setIsIndexed($p_value) {
		return parent::setProperty('IsIndexed', $p_value?'Y':'N');
	} // fn setIsIndexed
	
	
	/**
	 * Return the user ID of the user who has locked the article.
	 * @return int
	 */
	function getLockedByUser() {
		return $this->getProperty('LockUser');
	} // fn getLockedByUser
	
	
	/**
	 * @param int value
	 */
	function setLockedByUser($p_value) {
		return parent::setProperty('LockUser', $p_value);
	} // fn setLockedByUser
	
	
	/**
	 * Get the time the article was locked.
	 *
	 * @return string
	 *		In the form of YYYY-MM-DD HH:MM:SS
	 */
	function getLockTime() {
		return $this->getProperty('LockTime');
	} // fn getLockTime

	
	/**
	 * @return string
	 */
	function getShortName() {
		return $this->getProperty('ShortName');
	} // fn getShortName
	
	
	/**
	 * @param string value
	 */
	function setShortName($p_value) {
		return parent::setProperty('ShortName', $p_value);
	} // fn setShortName
	
	
	/**
	 * Return the ArticleType object for this article.
	 *
	 * @return ArticleType
	 */
	function getArticleTypeObject() {
		return new ArticleType($this->getProperty('Type'), 
			$this->getProperty('Number'), 
			$this->getProperty('IdLanguage'));
	} // fn getArticleTypeObject
	
	
//	/**
//	 * Create and return an array representation of an article for use in a template.
//	 * @return array
//	 */
//	function getTemplateVar() {
//		$templateVar = array();
//		$templateVar['publication_id'] = $this->IdPublication;
//		$templateVar['issue_id'] = $this->NrIssue;
//		$templateVar['section_id'] = $this->NrSection;
//		$templateVar['article_id'] = $this->Number;
//		$templateVar['language_id'] = $this->IdLanguage;
//		$templateVar['article_type'] = $this->Type;
//		$templateVar['user_id'] = $this->IdUser;
//		$templateVar['title'] = $this->Name;
//		$templateVar['on_front_page'] = $this->OnFrontPage;
//		$templateVar['on_section'] = $this->OnSection;
//		$templateVar['published'] = $this->Published;
//		$templateVar['upload_date'] = $this->UploadDate;
//		$templateVar['keywords'] = $this->Keywords;
//		$templateVar['is_public'] = $this->Public;
//		$templateVar['is_indexed'] = $this->IsIndexed;
//		$templateVar['locked_by_user'] = $this->LockUser;
//		$templateVar['lock_time'] = $this->LockTime;
//		$templateVar['short_name'] = $this->ShortName;
//		return $templateVar;
//	} // fn getTemplateVar


	/***************************************************************************/
	/* Static Functions                                                        */
	/***************************************************************************/
	
	/**
	 * Return the number of unique (language-independant) articles according
	 * to the given parameters.
	 */
	function GetNumUniqueArticles($p_publicationId = null, $p_issueId = null, 
								  $p_sectionId = null) {
		global $Campsite;
		$queryStr = 'SELECT COUNT(DISTINCT(Number)) FROM Articles';
		$whereClause = array();
		if (!is_null($p_publicationId)) {
			$whereClause[] = "IdPublication=$p_publicationId";			
		}
		if (!is_null($p_issueId)) {
			$whereClause[] = "NrIssue=$p_issueId";
		}
		if (!is_null($p_sectionId)) {
			$whereClause[] = "NrSection=$p_sectionId";
		}
		if (count($whereClause) > 0) {
			$queryStr .= ' WHERE ' . implode(' AND ', $whereClause);
		}
		$result = $Campsite['db']->GetOne($queryStr);
		return $result;
	} // fn GetNumUniqueArticles
	
	
	/**
	 * Return an array of (array(Articles), int) where
	 * the array of articles are those written by the given user, within the given range,
	 * and the int is the total number of articles written by the user.
	 *
	 * @return array
	 */
	function GetArticlesByUser($p_userId, $p_start = 0, $p_upperLimit = 20) {
		global $Campsite;
		$queryStr = 'SELECT * FROM Articles '
					." WHERE IdUser=$p_userId"
					.' ORDER BY Number DESC, IdLanguage '
					." LIMIT $p_start, $p_upperLimit";
		$query = $Campsite['db']->Execute($queryStr);
		$articles = array();
		while ($row = $query->FetchRow()) {
			$tmpArticle =& new Article($row['IdPublication'], $row['NrIssue'],
				$row['NrSection'], $row['IdLanguage']);
			$tmpArticle->fetch($row);
			$articles[] = $tmpArticle;
		}
		$queryStr = 'SELECT COUNT(*) FROM Articles '
					." WHERE IdUser=$p_userId"
					.' ORDER BY Number DESC, IdLanguage ';
		$totalArticles = $Campsite['db']->GetOne($queryStr);
		
		return array($articles, $totalArticles);
	} // fn GetArticlesByUser
	

	/**
	 * Get a list of submitted articles.
	 * Return an array of two elements: (array(Articles), int).
	 * The first element is an array of submitted articles.
	 * The second element is the total number of submitted articles.
	 *
	 * @param int p_lowerLimit
	 * @param int p_upperLimit
	 * @return array
	 */
	function GetSubmittedArticles($p_start = 0, $p_upperLimit = 20) {
		global $Campsite;
		$queryStr = 'SELECT * FROM Articles'
	    			." WHERE Published = 'S' "
	    			.' ORDER BY Number DESC, IdLanguage '
	    			." LIMIT $p_start, $p_upperLimit";
		$query = $Campsite['db']->Execute($queryStr);
		$articles = array();
		while ($row = $query->FetchRow()) {
			$tmpArticle =& new Article($row['IdPublication'], $row['NrIssue'],
				$row['NrSection'], $row['IdLanguage']);
			$tmpArticle->fetch($row);
			$articles[] = $tmpArticle;
		}
		$queryStr = 'SELECT COUNT(*) FROM Articles'
	    			." WHERE Published = 'S' "
	    			.' ORDER BY Number DESC, IdLanguage ';
	    $totalArticles = $Campsite['db']->GetOne($queryStr);
	    
		return array($articles, $totalArticles);
	} // fn GetSubmittedArticles
	
	
	/**
	 * Get the list of all languages that articles have been written in.
	 * @return array
	 */
	function GetAllLanguages() {
		global $Campsite;
		$tmpLanguage =& new Language();
		$languageColumns = $tmpLanguage->getColumnNames(true);
		$languageColumns = implode(",", $languageColumns);
	 	$queryStr = 'SELECT DISTINCT(IdLanguage), '.$languageColumns
	 				.' FROM Articles, Languages '
	 				.' WHERE Articles.IdLanguage = Languages.Id';
	 	$rows = $Campsite['db']->GetAll($queryStr);
	 	$languages = array();
	 	if (is_array($rows)) {
			foreach ($rows as $row) {
				$tmpLanguage =& new Language();
				$tmpLanguage->fetch($row);
				$languages[] =& $tmpLanguage;
			}
	 	}
		return $languages;		
	} // fn GetAllLanguages
	
	
	/**
	 * Get a list of articles.  You can be as specific or as general as you
	 * like with the parameters: e.g. specifying only p_publication will get
	 * you all the articles in a particular publication.  Specifying all 
	 * parameters will get you all the articles in a particular section with
	 * the given language.
	 *
	 * @param int p_publicationId
	 * @param int p_issueId
	 * @param int p_sectionId
	 * @param int p_languageId
	 *
	 * @param int p_preferredLanguage
	 *		If specified, list this language before others.
	 *
	 * @param array p_sqlLimit
	 *		Set the terms for the LIMIT clause.
	 *
	 * @return array
	 *		Return an array of Article objects.
	 */
	function GetArticles($p_publicationId = null, $p_issueId = null, 
						 $p_sectionId = null, $p_languageId = null, 
						 $p_preferredLanguage = null, $p_numRows = null,
						 $p_startAt = '', $p_numRowsIsUniqueRows = false) {
		global $Campsite;
		
		$whereClause = array();
		if (!is_null($p_publicationId)) {
			$whereClause[] = "IdPublication=$p_publicationId";			
		}
		if (!is_null($p_issueId)) {
			$whereClause[] = "NrIssue=$p_issueId";
		}
		if (!is_null($p_sectionId)) {
			$whereClause[] = "NrSection=$p_sectionId";
		}
		if (!is_null($p_languageId)) {
			$whereClause[] = "IdLanguage=$p_languageId";
		}

		if ($p_numRowsIsUniqueRows) {
			$queryStr1 = 'SELECT DISTINCT(Number) FROM Articles ';
			if (count($whereClause) > 0) {
				$queryStr1 .= ' WHERE '. implode(' AND ', $whereClause);
			}
			if ($p_startAt !== '') {
				$p_startAt .= ',';
			}
			$queryStr1 .= ' ORDER BY ArticleOrder ASC, Number DESC ';
			if (!is_null($p_numRows)) {
				$queryStr1 .= ' LIMIT '.$p_startAt.$p_numRows;
			}
			$uniqueArticleNumbers = $Campsite['db']->GetCol($queryStr1);
		}
		
		$queryStr2 = 'SELECT *';
		// This causes the preferred language to be listed first.
		if (!is_null($p_preferredLanguage)) {
			$queryStr2 .= ", abs($p_preferredLanguage - IdLanguage) as LanguageOrder ";
		}
		$queryStr2 .= ' FROM Articles';
		
		// If selecting unique rows, specify those rows in the 
		// WHERE clause.
		$uniqueRowsClause = '';
		if ($p_numRowsIsUniqueRows) {
			$tmpClause = array();
			foreach ($uniqueArticleNumbers as $uniqueNumber) {
				$tmpClause[] = "Number = $uniqueNumber";
			}
			if (count($tmpClause) > 0) {
				$uniqueRowsClause = '(' .implode(' OR ', $tmpClause).')';
			}
		}
		
		// Add the WHERE clause.
		if ((count($whereClause) > 0) || ($uniqueRowsClause != '')) {
			$queryStr2 .= ' WHERE ';
			if (count($whereClause) > 0) {
				$queryStr2 .= '(' . implode(' AND ', $whereClause) .')';
			}
			if ($uniqueRowsClause != '') {
				if (count($whereClause) > 0) {
					$queryStr2 .= ' AND ';
				}
				$queryStr2 .= $uniqueRowsClause;
			}
		}
		
		// ORDER BY clause
		$orderBy = ' ORDER BY ArticleOrder ASC, Number DESC ';
		if (!is_null($p_preferredLanguage)) {
			$orderBy .= ', LanguageOrder ASC, IdLanguage ASC';
		}
		$queryStr2 .= $orderBy;
		
		// If not using the unique rows option,
		// use the limit clause to set the number of rows returned.
		if (!$p_numRowsIsUniqueRows) {
			if ($p_startAt !== '') {
				$p_startAt .= ',';
			}
			if (!is_null($p_numRows)) {
				$queryStr2 .= ' LIMIT '.$p_startAt.$p_numRows;
			}
		}
				
		$query = $Campsite['db']->Execute($queryStr2);
		$articles = array();
		while ($row = $query->FetchRow()) {
			$tmpArticle =& new Article($row['IdPublication'], $row['NrIssue'],
				$row['NrSection'], $row['IdLanguage']);
			$tmpArticle->fetch($row);
			$articles[] = $tmpArticle;
		}
		return $articles;
	} // fn GetArticles
	
} // class Article

?>