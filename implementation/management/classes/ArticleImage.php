<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/classes/DatabaseObject.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/Article.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/Image.php');

class ArticleImage extends DatabaseObject {
	var $m_keyColumnNames = array('NrArticle','IdImage');
	var $m_dbTableName = 'ArticleImages';
	var $m_columnNames = array('NrArticle', 'IdImage', 'Number');
	var $m_image = null;
	
	function ArticleImage($p_articleId = null, $p_imageId = null, $p_templateId = null) { 
		if (!is_null($p_articleId) && !is_null($p_imageId)) {
			$this->m_data['NrArticle'] = $p_articleId;
			$this->m_data['IdImage'] = $p_imageId;
			$this->fetch();
		}
		elseif (!is_null($p_articleId) && !is_null($p_templateId)) {
			$this->m_data['NrArticle'] = $p_articleId;
			$this->m_data['Number'] = $p_templateId;
			$this->m_keyColumnNames = array('NrArticle', 'Number');
			$this->fetch();
		}
	} // constructor
	
	
	/**
	 * @return int
	 */
	function getImageId() {
		return $this->getProperty('IdImage');
	} // fn getImageId
	
	
	/**
	 * @return int
	 */
	function getArticleId() {
		return $this->getProperty('NrArticle');
	} // fn getArticleId

	
	/**
	 * @return int
	 */
	function getTemplateId() {
		return $this->getProperty('Number');
	} // fn getTemplateId
	
	
	/**
	 *
	 */
	function getImage() {
		return $this->m_image;
	}
	

	function GetUnusedTemplateId($p_articleId) {
		global $Campsite;
		// Get the highest template ID and add one.
		$queryStr = "SELECT MAX(Number)+1 FROM ArticleImages WHERE NrArticle=$p_articleId";
		$templateId = $Campsite['db']->GetOne($queryStr);
		if (!$templateId) {
			$templateId = 1;
		}
		return $templateId;
	} // fn GetUnusedTemplateId
	
	
	/**
	 * Return true if article already is using the given template ID, false otherwise.
	 *
	 * @param int p_articleId
	 * @param int p_templateId
	 *
	 * @return boolean
	 */
	function TemplateIdInUse($p_articleId, $p_templateId) {
		global $Campsite;
		$queryStr = "SELECT Number FROM ArticleImages"
					." WHERE NrArticle=$p_articleId AND Number=$p_templateId";
		$value = $Campsite['db']->GetOne($queryStr);
		if ($value !== false) {
			return true;
		}
		else {
			return false;
		}
	} // fn TemplateIdInUse
	
	
	/**
	 * Get all the images that belong to this article.
	 * @return array
	 */
	function GetImagesByArticleId($p_articleId) {
		global $Campsite;
		$tmpImage =& new Image();
		$columnNames = implode(',', $tmpImage->getColumnNames());
		
		$queryStr = 'SELECT '.$columnNames
					.', ArticleImages.Number, ArticleImages.NrArticle, ArticleImages.IdImage'
					.' FROM Images, ArticleImages'
					.' WHERE ArticleImages.NrArticle='.$p_articleId
					.' AND ArticleImages.IdImage=Images.Id'
					.' ORDER BY ArticleImages.Number';
		$rows =& $Campsite['db']->GetAll($queryStr);
		$returnArray = array();
		if (is_array($rows)) {
			foreach ($rows as $row) {
				$tmpArticleImage =& new ArticleImage();
				$tmpArticleImage->fetch($row);
				$tmpArticleImage->m_image =& new Image();
				$tmpArticleImage->m_image->fetch($row);
				$returnArray[] =& $tmpArticleImage;
			}
		}
		return $returnArray;
	} // fn GetImagesByArticleId
	
	
	/**
	 * Link the given image with the given article.  The template ID
	 * is the image's position in the template.
	 *
	 * @param int p_imageId
	 *
	 * @param int p_articleId
	 *
	 * @param int p_templateId
	 *		Optional.  If not specified, this will be the next highest number
	 *		of the existing values.
	 *
	 * @return void
	 */
	function AddImageToArticle($p_imageId, $p_articleId, $p_templateId = null) {
		global $Campsite;
		if (is_null($p_templateId)) {
			$p_templateId = ArticleImage::GetUnusedTemplateId($p_articleId);
		}
		$queryStr = 'INSERT IGNORE INTO ArticleImages(NrArticle, IdImage, Number)'
					.' VALUES('.$p_articleId.', '.$p_imageId.', '.$p_templateId.')';
		$Campsite['db']->Execute($queryStr);
	} // fn AddImageToArticle

	
	/**
	 * This call will only work for entries that already exist.
	 *
	 * @param int p_articleId
	 * @param int p_imageId
	 * @param int p_templateId
	 *
	 * @return void
	 */
	function SetTemplateId($p_articleId, $p_imageId, $p_templateId) {
		global $Campsite;
		$queryStr = "UPDATE ArticleImages SET Number=$p_templateId"
					." WHERE NrArticle=$p_articleId AND IdImage=$p_imageId";
		$Campsite['db']->Execute($queryStr);
	} // fn SetTemplateId
	
	
	/**
	 * Remove the linkage between the given image and the given article.
	 *
	 * @param int p_imageId
	 * @param int p_articleId
	 * @param int p_templateId
	 *
	 * @return void
	 */
	function RemoveImageFromArticle($p_imageId, $p_articleId, $p_templateId) {
		global $Campsite;
		$queryStr = 'DELETE FROM ArticleImages'
					.' WHERE NrArticle='.$p_articleId
					.' AND IdImage='.$p_imageId
					.' AND Number='.$p_templateId
					.' LIMIT 1';
		$Campsite['db']->Execute($queryStr);
	} // fn RemoveImageFromArticle

	
	/**
	 * Disassociate the image from all articles.
	 *
	 * @param int p_imageId
	 * @return void
	 */
	function OnImageDelete($p_imageId) {
		global $Campsite;
		$queryStr = 'DELETE FROM ArticleImages'
					." WHERE IdImage='".$p_imageId."'";
		$Campsite['db']->Execute($queryStr);
	} // fn OnImageDelete
	
	
	/**
	 * Remove image pointers for the given article.
	 * @param int p_articleId
	 * @return void
	 */
	function OnArticleDelete($p_articleId) {
		global $Campsite;
		$queryStr = 'DELETE FROM ArticleImages'
					." WHERE NrArticle='".$p_articleId."'";
		$Campsite['db']->Execute($queryStr);		
	} // fn OnArticleDelete
	

	/**
	 * Copy all the pointers for the given article.
	 * @param int p_srcArticleId
	 * @param int p_destArticleId
	 * @return void
	 */
	function OnArticleCopy($p_srcArticleId, $p_destArticleId) {
		global $Campsite;
		$queryStr = 'SELECT * FROM ArticleImages WHERE NrArticle='.$p_srcArticleId;
		$rows = $Campsite['db']->GetAll($queryStr);
		foreach ($rows as $row) {
			$queryStr = 'INSERT IGNORE INTO ArticleImages(NrArticle, IdImage, Number)'
						." VALUES($p_destArticleId, ".$row['IdImage'].",".$row['Number'].")";
			$Campsite['db']->Execute($queryStr);
		}
	} // fn OnArticleCopy
	
	
	/**
	 * Return an array of Article objects, all the articles
	 * which use this image.
	 *
	 * @return array
	 */
	function GetArticlesThatUseImage($p_imageId) {
		global $Campsite;
		$article =& new Article();
		$columnNames = $article->getColumnNames();
		$columnQuery = array();
		foreach ($columnNames as $columnName) {
			$columnQuery[] = 'Articles.'.$columnName;
		}
		$columnQuery = implode(',', $columnQuery);
		$queryStr = 'SELECT '.$columnQuery.' FROM Articles, ArticleImages '
					.' WHERE ArticleImages.IdImage='.$p_imageId
					.' AND ArticleImages.NrArticle=Articles.Number'
					.' ORDER BY Articles.Number, Articles.IdLanguage';
		$rows =& $Campsite['db']->GetAll($queryStr);
		$articles = array();
		if (is_array($rows)) {
			foreach ($rows as $row) {
				$tmpArticle =& new Article();
				$tmpArticle->fetch($row);
				$articles[] =& $tmpArticle;
			}
		}
		return $articles;
	} // fn GetArticlesThatUseImage
	
} // class ArticleImages

?>