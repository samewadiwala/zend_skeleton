<?php

class Account_Model_User_Profile extends Application_Model_Abstract 
{
	const GENDER_MALE   = 1; 
    const GENDER_FEMALE = 2;

    const GENDER_MALE_TEXT   = "Male"; 
    const GENDER_FEMALE_TEXT = "Female";

    const PREFIX_MR  = 1;
    const PREFIX_MRS = 2;

    const PREFIX_MR_TEXT  = "Mr";
    const PREFIX_MRS_TEXT = "Mrs";

	protected $_name 		= "user_profile";
	protected $_primary 	= "id";
	protected $_rowClass 	= "Account_Model_User_Profile_Row";
	protected $_rowsetClass = "Account_Model_User_Profile_Rowset";

	protected $_useUrlHelper        = true;
    protected $_useTranslatorHelper = true;

	public function getProfileByUserId($id = null)
	{
		//echo $id;die;
		$select = $this->getAdapter()->select()->from($this->_name)->where('user_id = ?', $id);
        return $this->getAdapter()->fetchRow($select);
	}

	public function updateData($data=array(),$user_id)
	{
		$where = $this->getAdapter()->quoteInto('user_id = ?', $user_id);
 
		return $this->update($data, $where);
	}

}