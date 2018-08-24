<?php
	class Account_Model_User extends Application_Model_Abstract {
        const ROLE_ADMIN        = 1;
        const ROLE_INSTRUCTOR   = 2;
        const ROLE_STUDENT      = 3;
       
        const ROLE_ADMIN_TEXT       = "Admin";
        const ROLE_INSTRUCTOR_TEXT  = "Instructor";
        const ROLE_STUDENT_TEXT     = "Student";

        const STATUS_ACTIVE            = 1;
        const STATUS_WAITING_FOR_USER  = 2;
        const STATUS_SUSPENDED         = 3;
        const STATUS_DELETE_BY_USER    = 4;

        const STATUS_ACTIVE_TEXT            = 'Active';
        const STATUS_WAITING_FOR_USER_TEXT  = 'Not confirmed';
        const STATUS_SUSPENDED_TEXT         = 'Suspended';
        const STATUS_DELETE_BY_USER_TEXT    = 'Deleted By User';

        const GENDER_MALE   = 1; 
        const GENDER_FEMALE = 2;

        const GENDER_MALE_TEXT   = "Male"; 
        const GENDER_FEMALE_TEXT = "Female";

        const PREFIX_MR  = 1;
        const PREFIX_MRS = 2;

        const PREFIX_MR_TEXT  = "Mr";
        const PREFIX_MRS_TEXT = "Mrs";
        
        const IMAGEPATH = APPLICATION_PATH. "/../data/media/user/";

        const TABLE_NAME        = "users";

        protected $_useUrlHelper        = true;
        protected $_useTranslatorHelper = true;

		protected $_name 		= "users";
		protected $_primary 	= "id";
		protected $_rowClass 	= "Account_Model_User_Row";
    	protected $_rowsetClass = "Account_Model_User_Rowset";

        
        private $UserModel      = null;
        private $validateModel  = null;
        private $profileModel   = null;
        private $wallModel      = null;
        private $reviewModel    = null;
        
        public function getStatusOptions() {
            return array(
                self::STATUS_ACTIVE             => self::STATUS_ACTIVE_TEXT,
                self::STATUS_WAITING_FOR_USER   => self::STATUS_WAITING_FOR_USER_TEXT,
                self::STATUS_SUSPENDED          => self::STATUS_SUSPENDED_TEXT,
                self::STATUS_DELETE_BY_USER     => self::STATUS_DELETE_BY_USER_TEXT,
            );
        }

        public function getRoleOptions() {
            return array(
                self::ROLE_ADMIN => self::ROLE_ADMIN_TEXT,
                self::ROLE_INSTRUCTOR  => self::ROLE_INSTRUCTOR_TEXT,
                self::ROLE_STUDENT  => self::ROLE_STUDENT_TEXT,
            );
        }

        public function getPrefixOptions() {
            return array(
                self::PREFIX_MR  => self::PREFIX_MR_TEXT,
                self::PREFIX_MRS => self::PREFIX_MRS_TEXT
            );
        }

        public function getGenderOptions() {
            return array(
                self::GENDER_MALE       => self::GENDER_MALE_TEXT,
                self::GENDER_FEMALE     => self::GENDER_FEMALE_TEXT
            );
        }

        public function login($data = array()) {
            $username   = (isset($data['username'])) ? $data['username'] : '';
            $password   = (isset($data['password'])) ? $data['password'] : '';

            $row = $this->fetchRow(
                $this->select()->from($this->_name)->where("email = ? OR username = ?", $username, $username)->where("u_role = ?", self::ROLE_ADMIN)
            );

            if(!$row) {
                return false;
            }

            $row->u_password = $password;
            return $row->login();
        }

        //Frontend Login for user
        public function userLogin($data = array()) {
            $username   = (isset($data['username'])) ? $data['username'] : '';
            $password   = (isset($data['password'])) ? $data['password'] : '';
            $https      = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

            $select =  $this->select()
                    ->from(array('u' => 'users'),
                        array('id', 'email','u_password', 'username', 'first_name', 'last_name', 'status', 'last_login'))
                    ->where("email = ? OR username = ?", $username, $username)
                    ->where("status = ?", self::STATUS_ACTIVE)
                    ->where("u_role = ?", self::ROLE_INSTRUCTOR)
                    ->orWhere("u_role = ?", self::ROLE_STUDENT)
                    ->setIntegrityCheck(false);

            $row = $this->fetchRow($select);

            if(!$row) {
                return false;
            } 
            $row->u_password = md5($password);
            return $row->userLogin(); 
        }

        public function getUserAccountDetail($data = array()) {
            return $this->fetchRow(
                $this->select()->from(array('u' => 'users'),array('email','status'))
                        ->where("email = ? ", $data['username'])
                        ->where("u_password = ? ", md5($data['password']))
                        ->setIntegrityCheck(false)
            );
        }

        public function userConfirmLogin($data = array()) {
            $error = $this->_getvalidateModel()->loginValidate($data);
            if($error) {
                return false;
            }

            $username   = (isset($data['username'])) ? $data['username'] : '';
            $password   = (isset($data['password'])) ? $data['password'] : '';
            $https      = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

            $select =  $this->select()
                    ->from(array('u' => 'users'),
                        array('id', 'email','u_password', 'username', 'first_name', 'last_name', 'status', 'last_login'))
                    ->where("email = ? OR username = ?", $username, $username)
                    ->where("status = ?", self::STATUS_ACTIVE)
                    ->where("u_role = ?", self::ROLE_INSTRUCTOR)
                    ->orWhere("u_role = ?", self::ROLE_STUDENT)
                    ->setIntegrityCheck(false);

            $row = $this->fetchRow($select);

            if(!$row) {
                return false;
            } 
            $row->u_password = $password;
            return $row->userLogin(); 
        }

        public function adminLogin($data = array()) {
    		$username 	= (isset($data['username'])) ? $data['username'] : '';
    		$password 	= (isset($data['password'])) ? $data['password'] : '';

    		$row = $this->fetchRow(
    			$this->select()->from($this->_name)->where("email = ? OR username = ?", $username, $username)->where("u_role = ?", self::ROLE_ADMIN)->where("status = ?", self::STATUS_ACTIVE)
    		);

    		if(!$row) {
    			return false;
    		}

    		$row->u_password = md5($password);
    		return $row->adminLogin();
    	}

        public function getUserByEmail($email = null) {
            $https  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $select = $this->select()->setintegritycheck(false)
                        ->from(array('u'=>'users'),
                                array('id','email','username','u_password','last_login','status', 'first_name', 'last_name','created_date','updated_date','u_role','facebook_user_id','google_user_id')
                        )
                        ->joinLeft(array('us' => 'user_profile'),
                            'u.id = us.user_id',
                            array('gender', 'prefix', 'contact', 'facebook', 'dob', 'linkedin', 'googleplus', 'twitter', 'youtube', 'otherurl'))
                        ->joinLeft(array('uw' => 'user_wall'),
                            'u.id = uw.user_id',
                            array('profile_pic','headline','designation','biography','language','cover_pic'))
                        ->where("u.email = ? OR u.username = ?", $email, $email);

            $result = $this->fetchRow($select);

            if(!is_null($result)){
                $result->attachAvgReview();
                $result->attachReviews();
            }

            return $result;            
        }

        public function getUserDeatil($email = null) {
            $https      = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $select =  $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'users'),
                            array('id','email','username','last_login','u_password','status', 'first_name', 'last_name','created_date','updated_date','u_role','facebook_user_id','google_user_id'))
                    ->joinLeft(array('us' => 'user_profile'),
                            'u.id = us.user_id',
                            array("gender", 'prefix', 'contact'))
                    ->joinLeft(array('uw' => 'user_wall'),
                        'u.id = uw.user_id',
                        array('profile_pic' ,'headline','designation','cover_pic'))
                    ->where("u.email LIKE ?", $email);

            $result = $this->fetchRow($select);

            if(!is_null($result)){
                $result->attachAvgReview();
                $result->attachReviews();
            }

            return $result;            
        }

        public function getUserDeatilById($id = null){
            $https      = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $select =  $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'users'),
                            array('id','email','username','u_password','last_login','status', 'first_name', 'last_name','created_date','updated_date','u_role','facebook_user_id','google_user_id'))
                    ->joinLeft(array('us' => 'user_profile'),
                            'u.id = us.user_id',
                            array("gender", 'prefix', 'contact','dob', 'facebook','linkedin', 'googleplus ', 'twitter ', 'youtube', 'otherurl'))
                    ->joinLeft(array('uw' => 'user_wall'),
                        'u.id = uw.user_id',
                        array('profile_pic','headline','designation','biography','cover_pic'))
                    ->where("u.id = ? ", $id);
            
            $result = $this->fetchRow($select);
            
            if(!is_null($result)){
                $result->attachAvgReview();
                $result->attachReviews();
            }

            return $result; 
        }

        public function getUserDeatilByFacebookId($id = null){
            $https      = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $select =  $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'users'),
                            array('id','email','username','u_password','last_login','status', 'first_name', 'last_name','created_date','updated_date','u_role','facebook_user_id','google_user_id'))
                    ->joinLeft(array('us' => 'user_profile'),
                            'u.id = us.user_id',
                            array("gender", 'prefix', 'contact','dob', 'facebook','linkedin', 'googleplus ', 'twitter ', 'youtube', 'otherurl'))
                    ->joinLeft(array('uw' => 'user_wall'),
                        'u.id = uw.user_id',
                        array('profile_pic','headline','designation','biography','cover_pic'))
                    ->where("u.facebook_user_id = ? ", $id)
                    ;
            
            $result = $this->fetchRow($select);

            if(!is_null($result)){
                $result->attachAvgReview();
                $result->attachReviews();
            }

            return $result; 
        }

        public function getUserByUsername($username = array()) {
            return $this->fetchRow(
                $this->select()->from($this->_name,array('id','email'))->where('username = ?', $username)
            );            
        }

        public function getUsers($role_id = self::ROLE_INSTRUCTOR) {
            $select = $this->select()->from($this->_name)->where('u_role = ?', $role_id)->order('id DESC');
            return $this->fetchAll($select);
        }

        public function getTotalUsers($role_id = self::ROLE_INSTRUCTOR) {
            $select = $this->select()->from($this->_name,array('COUNT(id)'))->where('u_role = ?', $role_id);
            return $this->fetchRow($select);
        }

        public function registerUser($data = array()) {
            $date       = Zend_Date::now();
            $timeStamp  = gmdate("Y-m-d H:i:s", $date->getTimestamp());

            $data['u_password']     = md5($data['u_password']);
            $data['username']       = '';
            $data['status']         = self::STATUS_WAITING_FOR_USER;
            $data['u_role']         = self::ROLE_INSTRUCTOR;
            $data['created_date']   = $timeStamp;
            $data['updated_date']   = $timeStamp;

            return $this->insert($data);
        }

        public function registerUserFacebook($fbData = array()){
            $date       = Zend_Date::now();
            $timeStamp  = gmdate("Y-m-d H:i:s", $date->getTimestamp()); 
            
            $this->getAdapter()->beginTransaction();
            try{
                $userInsertData = array(
                    'email'             => ($fbData['email']) ? $fbData['email'] : null,  
                    'u_password'        => null,  
                    'status'            => Account_Model_User::STATUS_ACTIVE, 
                    'u_role'            => Account_Model_User::ROLE_INSTRUCTOR, 
                    'created_date'      => $timeStamp,  
                    'last_login'        => $timeStamp,  
                    'first_name'        => $fbData['first_name'],  
                    'last_name'         => $fbData['last_name'], 
                    'facebook_user_id'  => $fbData['id'],
                );

                $userId = $this->insert($userInsertData);
                
                $userWallData = array(
                    'user_id'       => $userId,
                    'profile_pic'   => 'https://graph.facebook.com/'.$fbData['id'].'/picture?type=large',
                    'language'      => Account_Model_User_Wall::LANGUAGE_ENGLISH,
                    'updated_date'  => $timeStamp,
                    'cover_pic'     => $fbData['cover']['source'],
                );

                $this->_getUserWallModel()->insert($userWallData);

                $userProfileData = array(
                    'user_id'       => $userId,
                    'gender'        => ($fbData['gender'] == 'male') ? self::GENDER_MALE : self::GENDER_FEMALE,
                    'prefix'        => ($fbData['gender'] == 'male') ? self::GENDER_MALE : self::GENDER_FEMALE,
                    'facebook'      => ($fbData['link']) ? ltrim($fbData['link'],"https://www.facebook.com/_scoped_user_id/") : ''
                );

                $this->_getUserProfileModel()->insert($userProfileData);

                $users = $this->getUserDeatil($fbData['email']);
                if($users){
                    $this->getUserSession()->token = session_id();
                    $this->getUserSession()->user = serialize($users);
                }
                
                $response = array('type' => 'success');
                $this->getAdapter()->commit();
            } catch(Exception $e){
                $this->getAdapter()->rollBack();
                $response = array('type' => 'failure', 'error' => $e->getMessage());
                Zend_Registry::get('logger')->log(sprintf('%s in %s on line %d', $e->getMessage(), __FILE__, __LINE__), 3);
            }

            return $response;
        }

        public function registerUserGoogle($gpData = array()){
            $date       = Zend_Date::now();
            $timeStamp  = gmdate("Y-m-d H:i:s", $date->getTimestamp()); 
            
            $this->getAdapter()->beginTransaction();
            try{
                $userInsertData = array(
                    'email'             => ($gpData['email']) ? $gpData['email'] : null,  
                    'u_password'        => null,  
                    'status'            => Account_Model_User::STATUS_ACTIVE, 
                    'u_role'            => Account_Model_User::ROLE_INSTRUCTOR, 
                    'created_date'      => $timeStamp,  
                    'last_login'        => $timeStamp,  
                    'first_name'        => $gpData['given_name'],  
                    'last_name'         => $gpData['family_name'], 
                    'google_user_id'    => $gpData['id'],
                );

                $userId = $this->insert($userInsertData);
                
                $userWallData = array(
                    'user_id'       => $userId,
                    'profile_pic'   => $gpData['picture'],
                    'language'      => Account_Model_User_Wall::LANGUAGE_ENGLISH,
                    'updated_date'  => $timeStamp,
                    //'cover_pic'     => $fbData['cover']['source'],
                );

                $this->_getUserWallModel()->insert($userWallData);

                $userProfileData = array(
                    'user_id'       => $userId,
                    'gender'        => (isset($gpData['gender']) && ($gpData['gender'] == 'male')) ? self::GENDER_MALE : self::GENDER_FEMALE,
                    'prefix'        => (isset($gpData['gender']) && ($gpData['gender'] == 'male')) ? self::GENDER_MALE : self::GENDER_FEMALE,
                    'facebook'      => (isset($gpData['link']) && $gpData['link']) ? ltrim($gpData['link'],"") : ''
                );

                $this->_getUserProfileModel()->insert($userProfileData);

                $users = $this->getUserDeatil($gpData['email']);
                if($users){
                    $this->getUserSession()->token = session_id();
                    $this->getUserSession()->user = serialize($users);
                }
                
                $response = array('type' => 'success');
                $this->getAdapter()->commit();
            } catch(Exception $e){
                $this->getAdapter()->rollBack();
                $response = array('type' => 'failure', 'error' => $e->getMessage());
                Zend_Registry::get('logger')->log(sprintf('%s in %s on line %d', $e->getMessage(), __FILE__, __LINE__), 3);
            }

            return $response;
        }

        public function isConfirmedEmail($email = null) {
            $flag = $this->fetchRow(
                        $this->select()->from($this->_name)
                                    ->where('email = ?', $email)
                                    ->where('status = ?', self::STATUS_ACTIVE)
                    );      

            if(count($flag)){
                return true;
            } else {
                return false;
            }
        }
    
        public function verifyEmail($code = "") {
            $params = array();
            parse_str(base64_decode($code), $params);

            if(!isset($params['user_id']) || empty($params['user_id'])) {
                return false;
            }

            if(!isset($params['email']) || empty($params['email'])) {
                return false;
            }

            $this->update(array('status' => self::STATUS_ACTIVE), "id = {$params['user_id']}");
            $profileData = array(
                'user_id'       => $params['user_id'],
                'prefix'        => Account_Model_User_Profile::PREFIX_MR,
                'gender'        => Account_Model_User_Profile::GENDER_MALE,
            );

            $wallData = array(
                'user_id'       => $params['user_id'],
                'profile_pic'   => Account_Model_User_Wall::DEFAULT_PROFILE_PIC,
                'language'      => Account_Model_User_Wall::LANGUAGE_ENGLISH,
            );
            $this->_getUserProfileModel()->insert($profileData);
            $this->_getUserWallModel()->insert($wallData);

            $password = $this->fetchRow(
                $this->select()->from($this->_name)->where('id = ?', $params['user_id'] )
            );

            $data = array(
                'username' => $params['email'],
                'password' => $password->u_password
            );

            return $this->userConfirmLogin($data);  
        }

        public function filter($filter = array(), $sort = 'created_date', $order = 'DESC') {
            
            $select = $this->select()->from($this->_name);
            foreach ($filter as $key => $value) {
                $value = trim($value);
                switch ($key) {
                    case 'first_name' :
                        if($value){
                            $select->where("LOWER(first_name) LIKE LOWER('%{$value}%')");
                        }
                        break;

                    case 'last_name' :
                        if($value){
                            $select->where("LOWER(last_name) LIKE LOWER('%{$value}%')");
                        }
                        break;

                    case 'username':
                        if($value){
                            $select->where("LOWER(username) LIKE LOWER('%{$value}%')");
                        }
                        break;
                    case 'email':
                        if($value){
                            $select->where("email LIKE '%{$value}%'");
                        }
                        break;
                    case 'u_role':
                        if($value){
                            $select->where("u_role = {$value}"); 
                        }                      
                        break;
                    case 'created_date_from':

                        if($value){
                            $select->where("created_date >= '{$filter["created_date_from"]}'");
                        }
                        break;
                    case 'created_date_to':
                        if($value){
                            $select->where("created_date <= '{$filter["created_date_to"]}'");
                        }
                        break;
                   
                    case 'status':
                        if($value){
                            $select->where("status ={$value}");
                        }
                        break;
                    default:
                        break;
                }
            }

            $select->order($sort.' '.$order);
    
            return $this->fetchAll($select);
        }

        public function changeEmail($data = array()) {
            $userbase = unserialize($this->getUserSession()->user);
            $id = $userbase['id'];
            
            $date       = Zend_Date::now();
            $timeStamp  = gmdate("Y-m-d H:i:s", $date->getTimestamp());

            $adapter = new Zend_Auth_Adapter_DbTable(
                $this->getAdapter(),
                'users',
                'email',
                'u_password'
            );

            $adapter->setIdentity($userbase['email']);
            $adapter->setCredential(md5($data['password']));

            $result = $adapter->authenticate();
            if($result->isValid()) {
                unset($data['password']);
                $data['updated_date'] = $timeStamp;
                $this->update($data,'id = '.$id);

                $users = $this->getUserDeatil($data['email']);

                $this->getUserSession()->token = session_id();
                $this->getUserSession()->user = serialize($users);

                $response = array(
                    'type' => 'success',
                    'msg' => $this->getTranslatorHelper()->Translator()->__("Email Changed Successfully."),
                );
            } else {
                $response = array(
                    'type' => 'failure',
                    'html' => array('password' =>array($this->getTranslatorHelper()->Translator()->__("Enter Valid Password."))),
                );
            }
            return $response;
        }

        public function getAdminLastLogin() {
            return $this->getAdapter()->fetchRow(
                $this->getAdapter()->select()
                                    ->from('users', array('last_login'))
                                    ->where('u_role = ?',self::ROLE_ADMIN)
                                    ->where('id = 1')
            );
        }

        public function getAdminData() {
            return $this->fetchRow(
                $this->select()->from($this->_name)->where("u_role = ?", self::ROLE_ADMIN)->where("status = ?", self::STATUS_ACTIVE)
            );
        }

        public function logout() {
            /*session_destroy();
            unset($this->getUserSession()->user);*/
            Zend_Session::namespaceUnset('ad-user');
            return true;
        }



        public function logoutAdmin() {
            /*session_destroy();
            unset($this->getSession()->adminUser);*/
            Zend_Session::namespaceUnset('ad-master');
            return true;
        }

        private function getResultViaPaginator($select) {
            return new Zend_Paginator(
                new Zend_Paginator_Adapter_DbSelect($select)
            );
        }

        public function _getvalidateModel(){
            if(is_null($this->validateModel)) {
                $this->validateModel = new Account_Model_User_Validate();
            }
            return $this->validateModel;
        }

        protected function _getUserProfileModel() {
            if(is_null($this->profileModel)) {
                $this->profileModel = new Account_Model_User_Profile();
            }
            return $this->profileModel;
        }

        protected function _getUserWallModel() {
            if(is_null($this->wallModel)) {
                $this->wallModel = new Account_Model_User_Wall();
            }
            return $this->wallModel;
        }

        public static function getLoggedInUserSession(){
            return unserialize(self::getUserSession()->user);
        }

        public function _getReviewModel(){
            if(is_null($this->reviewModel)) {
                $this->reviewModel = new Catalog_Model_Product_Review();
            }
            return $this->reviewModel;
        }

        public function getSession() {
            // Admin Side User Session namespace
            return new Zend_Session_Namespace("ad-master");
        }

        public static function getUserSession() {
            // Front side User Session namespace
            return new Zend_Session_Namespace('ad-user');
        }
	}
?>