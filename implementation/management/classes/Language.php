<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/DatabaseObject.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/DbObjectArray.php');

class Language extends DatabaseObject {
	var $m_dbTableName = 'Languages';
	var $m_keyColumnNames = array('Id');
	var $m_keyIsAutoIncrement = true;
	var $m_columnNames = array('Id', 'Name', 'CodePage', 'OrigName', 'Code');
	
	/** 
	 * Constructor.
	 */
	function Language($p_languageId = null) {
		parent::DatabaseObject($this->m_columnNames);
		$this->setProperty('Id', $p_languageId, false);
		if (!is_null($p_languageId)) {
			$this->fetch();
		}
	} // constructor
	
	
	/**
	 * Return an array of all languages (array of Language objects).
	 * @return array
	 */
	function GetAllLanguages() {
		$queryStr = 'SELECT * FROM Languages ORDER BY Name';
		$languages = DbObjectArray::Create('Language', $queryStr);
		return $languages;
	} // fn GetAllLanguages

	
	/**
	 * The unique ID of the language in the database.
	 * @return int
	 */
	function getLanguageId() {
		return $this->getProperty('Id');
	} // fn getLanguageId
	
	
	/**
	 * Return the english name of this language.
	 * @return string
	 */
	function getName() {
		return $this->getProperty('Name');
	} // fn getName
	

	/**
	 * Return the name of the language as written in the language itself.
	 * @return string
	 */ 
	function getNativeName() {
		return $this->getProperty('OrigName');
	} // fn get
	
	
	/**
	 * Get the two-letter code for this language.
	 * @return string
	 */
	function getCode() {
		return $this->getProperty('Code');
	} // fn getCode
	
} // class Language

?>