<?PHP

class Topic extends DatabaseObject {
	var $m_keyColumnNames = array('Id', 'LanguageId');

	var $m_dbTableName = 'Topics';
	
	var $m_columnNames = array('Id', 'LanguageId', 'Name', 'ParentId');
	
	var $m_hasSubtopics = null;
	
	/**
	 *
	 * @param int p_id
	 * @param int p_languageId
	 */
	function Topic($p_id = null, $p_languageId = null) { 
		parent::DatabaseObject($this->m_columnNames);
		if (!is_null($p_id) && is_numeric($p_id)) {
			$this->m_data['Id'] = $p_id;
			$this->m_data['LanguageId'] = $p_languageId;
			$this->fetch();
		}
	} // constructor
	

	/**
	 * @return string
	 */
	function getName() {
		return $this->getProperty('Name');
	} // fn getName
	
	
	/**
	 * @return int
	 */
	function getTopicId() {
		return $this->getProperty('Id');
	} // fn getTopicId
	
	
	/**
	 * @return int
	 */
	function getParentId() {
		return $this->getProperty('ParentId');
	}
	
	
	/**
	 * Return an array of Topics starting from the root down
	 * to and including the current topic.
	 *
	 * @return array
	 */
	function getPath() {
		global $Campsite;
		$row = true;
		$currentId = $this->m_data['Id'];
		$stack = array();
		while (($row !== false) && (count($row) > 0)) {
			$queryStr = 'SELECT * FROM Topics WHERE Id = '.$currentId;
			$row = $Campsite['db']->GetRow($queryStr);
			if (($row !== false) && (count($row) > 0)) {
				$topic =& new Topic();
				$topic->fetch($row);
				array_unshift($stack, $topic);
				$currentId = $topic->getParentId();
			}			
		}
		return $stack;
	} // fn getParents
	
	
	/**
	 * Get the subtopics (as an array of Topics) for this topic.
	 *
	 * @param array p_sqlOptions
	 * @return array
	 */
	function getSubtopics($p_sqlOptions = null) {
		global $Campsite;
		$queryStr = 'SELECT * FROM Topics '
					.' WHERE ParentId = '.$this->m_data['Id']
					.' ORDER BY Name ';
		$queryStr = DatabaseObject::ProcessOptions($queryStr, $p_sqlOptions);
		$subtopics = DbObjectArray::Create('Topic', $queryStr);
		return $subtopics;
	} // fn getSubtopics
	
	
	/**
	 * Return true if this topic has subtopics.
	 *
	 * @return boolean
	 */
	function hasSubtopics() {
		global $Campsite;
		// Returned the cached value if available.
		if (!is_null($this->m_hasSubtopics)) {
			return $this->m_hasSubtopics;
		}
		$queryStr = 'SELECT COUNT(*) FROM Topics WHERE ParentId = '.$this->m_data['Id'];
		$numRows = $Campsite['db']->GetOne($queryStr);
		return ($numRows > 0);
	} // fn hasSubtopics

	
	/** 
	 * @param string
	 * @return array
	 */
	function GetByName($p_name)  {
		$p_name = addslashes($p_name);
		$queryStr = "SELECT * FROM Topics WHERE Name LIKE '%$p_name%'";
		$matchTopics =& DbObjectArray::Create('Topic', $queryStr);
		return $matchTopics;
	} // fn GetByName
	
} // class Topics

?>