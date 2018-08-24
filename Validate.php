<?php
class Account_Model_User_Validate extends Application_Model_Abstract 
{
    const IMAGE_MAXSIZE     = 2097152; //2 MB

    protected $_name                = "users";
	protected $_useUrlHelper        = true;
    protected $_useTranslatorHelper = true;

    protected $userModel = null;
	
	public function validRegister($data = array()) {
        $errors = array();
        foreach ($data as $key => $value) {
            $value = trim($value);
            switch ($key) {
                case 'first_name':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid first name.");
                        }
                    } 
                    break;

                case 'last_name':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid last name.");
                        }
                    } 
                    break;
                    
                case 'email' :
                    $validator = new Zend_Validate_EmailAddress();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid email.");
                            break;
                        }
                    } else {
                        $existEmail = $this->getAdapter()->fetchCol(
                            $this->getAdapter()->select()->from('users', array('email'=>'LOWER(email)'))
                        );

                        if(in_array(strtolower($value), $existEmail)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Email already exists.");
                        }
                    }
                    break;
                case 'u_password' : 
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid password.");   
                        }
                    } else {
                        if(!preg_match('$\S*(?=\S{8,})\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Password must be at least 8 letter.");
                        } elseif(!preg_match('$\S*(?=\S*[a-zA-Z])(?=\S*[\W])(?=\S*[\d])\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Please enter strong password.");
                        }
                    }
                    break; 
                default:
                    break;
            }
        }
        return $errors;
    }

    public function loginValidate($data = array()) {
        $errors = array();

        foreach ($data as $key => $value) {
            switch ($key) {
                
                case 'username':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Username is required.");   
                        }
                    } 
                    break; 
                case 'password':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Password is required.");
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return $errors;
    }

    public function validChangePassword($data = array()) {
        $errors = array();

        foreach ($data as $key => $value) {
            $value = trim($value);
            switch ($key) {
                
                case 'new_password':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid password.");;   
                        }
                    } else {
                        if(!preg_match('$\S*(?=\S{8,})\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Password must be at least 8 latter.");
                        } elseif(!preg_match('$\S*(?=\S*[a-zA-Z])(?=\S*[\W])(?=\S*[\d])\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Please enter strong password.");
                        }
                    }
                    break; 
                case 'confirm_password':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Confirm password is not matched.");  
                        }
                    } else {
                        if($data['new_password'] != $data['confirm_password']){
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Confirm password is not matched.");
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return $errors;
    }

    public function validUserChangePassword($data = array()) {
        $errors = array();

        foreach ($data as $key => $value) {
            $value = trim($value);
            switch ($key) {
                case 'curr_password':
                    $validator = new Zend_Validate_Db_RecordExists(
                        array(
                            'table' => 'users',
                            'field' => 'u_password'
                        )
                    );
                    if(!$validator->isValid(md5($value))){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid current password.");   
                        }
                    } 
                    break;
                case 'new_password':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid password.");   
                        }
                    } else {
                        if(!preg_match('$\S*(?=\S{8,})\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Password must be at least 8 latter.");
                        } elseif(!preg_match('$\S*(?=\S*[a-zA-Z])(?=\S*[\W])(?=\S*[\d])\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Please enter strong password.");
                        }
                    }
                    break; 
                case 'confirm_password':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Confirm password is not matched.");  
                        }
                    } else {
                        if($data['new_password'] != $data['confirm_password']){
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Confirm password is not matched.");
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return $errors;
    }

    public function validForgotPassword($data = array()) {
    	$errors = array();
        foreach ($data as $key => $value) {
            $value = trim($value);
            switch ($key) {
                
                case 'u_email' :
                    $validator = new Zend_Validate_EmailAddress();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid email");  
                            break;
                        }
                    } else {
                        $existEmail = $this->getAdapter()->fetchCol(
                            $this->getAdapter()->select()->from('users', array('email'=>'LOWER(email)'))
                            		->where('u_role <> ?', Account_Model_User::ROLE_ADMIN )
                        );

                        if(!in_array(strtolower($value), $existEmail)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Email does not exists.");
                        }
                    }
                    break;

                default:
                    break;
            }
        }  
        return $errors;
    }

    public function validProfile($data = array(),$userData = array()) {
        $data = array_merge($data,$userData);

        $errors = array();
        foreach ($data as $key => $value) {
            $value = trim($value);
            switch ($key) {
                case 'first_name':
                    if($value) {
                        $validator = new Zend_Validate_Alpha();
                        if(!$validator->isValid($value)){
                            foreach ($validator->getMessages() as $message) {
                                $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid first name!");  
                            }
                        }
                    } else {
                        $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid first name!"); 
                    } 
                    break;  

                case 'last_name' :
                    if($value) {
                        $validator = new Zend_Validate_Alpha();
                        if(!$validator->isValid($value)){
                            foreach ($validator->getMessages() as $message) {
                                $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid last name!");   
                            }
                        }
                    } else {
                        $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid last name!"); 
                    } 
                    break;

                case 'contact' :
                
                    if($value){
                        $validator = new Zend_Validate_Regex(array('pattern' => '/^((\\+[1-9]{1,4}[ \\-]*)|(\\([0-9]{2,3}\\)[ \\-]*)|([0-9]{2,4})[ \\-]*)*?[0-9]{3,4}?[ \\-]*[0-9]{3,4}?$/'));
                        if(!$validator->isValid($value)){
                            foreach ($validator->getMessages() as $message) {
                                $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid contact no!");   
                            }
                        } 
                    }
                    break; 

                case 'dob' : 
                    if($value){
                        $validator = new Zend_Validate_Date(array('format' => 'dd-mm-yyyy'));
                
                        if(strtotime($value) >= strtotime(date('d-m-Y'))){
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid date of birth!");
                            break;        
                        }
                        if(!$validator->isValid($value)){
                            foreach ($validator->getMessages() as $message) {
                                $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid date of birth!");   
                            }
                        } 
                    }
                    break;

                default:
                    break;
            }
        }  

        return $errors;
    }

    public function validProfilePic($data = array()) {
        $errors = null;
        foreach ($data as $key => $value) {
            switch ($key) {
                 case 'file': 
                    if(count($data['file'])){
                        $acceptable = array(
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/png'
                        );
                        
                        if(!in_array($value['type'], $acceptable) && !empty($value["type"])) {
                            $errors = $this->getTranslatorHelper()->Translator()->__('Invalid file type. Only JPG, GIF and PNG types are accepted.');
                            break;
                        }

                        if(($value['size'] >= self::IMAGE_MAXSIZE) || ($value["size"] == 0)) {
                            $errors = $this->getTranslatorHelper()->Translator()->__('File too large. File must be less than 2 MB.');
                            break;
                        }

                    }
                    break;

                default:
                    break;
            }
        }  
        return $errors;
    }

    public function validUserName($username = null, $user_id = null) {
        $errors = array();
       
        $username = trim($username);

        $validator = new Zend_Validate_NotEmpty();
        if(!$validator->isValid($username)) {
            foreach ($validator->getMessages() as $message) {
                $errors['username'][] = $this->getTranslatorHelper()->Translator()->__("Enter valid username.");;   
                break;
            }
        } else {
            $existUsername = $this->getAdapter()->fetchCol(
                $this->getAdapter()->select()
                                    ->from('users', array('username'=>'LOWER(username)'))
                                    ->where('id <> ?',$user_id)

            );

            if(in_array(strtolower($username), $existUsername)) {
                $errors['username'][] = $this->getTranslatorHelper()->Translator()->__("Username already used.Try another username.");
            }
        }
               
        return $errors;
    }

    public function validAdminUserName($username = null, $user_id = null) {
        $errors = array();
       
        $username = trim($username);

        $validator = new Zend_Validate_NotEmpty();
        if(!$validator->isValid($username)) {
            foreach ($validator->getMessages() as $message) {
                $errors['username'][] = $this->getTranslatorHelper()->Translator()->__("Enter valid username.");   
                break;
            }
        }     
        return $errors;
    }

    public function validChangeEmail($data = array(),$user_id = '') {   
        $errors = array();

        foreach ($data as $key => $value) {
            switch ($key) {
                
                case 'email':
                    $validator = new Zend_Validate_EmailAddress();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid email");
                            break;   
                        }
                    } else {
                        $owenEmail = $this->getAdapter()->fetchCol(
                            $this->getAdapter()->select()
                                                ->from('users', array('email'=>'LOWER(email)'))
                                                ->where('id = ?',$user_id)
                        );
                        
                        if(!in_array(strtolower($value), $owenEmail)) {
                            $existEmail = $this->getAdapter()->fetchCol(
                                $this->getAdapter()->select()
                                                    ->from('users', array('email'=>'LOWER(email)'))
                                                    ->where('id <> ?',$user_id)
                            );

                            if(in_array(strtolower($value), $existEmail)) {
                                $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Email already exists.");
                            }
                        } else {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Email already used by you.Please try with another email.");
                        }
                    }
                    break; 
                case 'password':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter valid password.");   
                        }
                    } else {
                        if(!preg_match('$\S*(?=\S{8,})\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Password must be at least 8 latter.");
                        } elseif(!preg_match('$\S*(?=\S*[a-zA-Z])(?=\S*[\W])(?=\S*[\d])\S*$', $value)) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Please enter strong password.");
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        
        return $errors;
    }

    public function validPaymentDetail($data=array()) {
        $errors = array();

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'paypal_id':
                    $validator = new Zend_Validate_EmailAddress();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid paypal ID.");
                        }
                    }
                    break;   
                case 'address' :
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid address.");
                        }
                    }
                    break;
                case 'city' :
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid city.");
                        }
                    }
                    break;
                case 'zipcode' :
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid zipcode.");
                        }
                    }
                    break;
                case 'country' :
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)) {
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter vaid country.");
                        }
                    }
                    break;
            }
        }

        return $errors;
    }

    public function validReviewComment($data = array()) {
        $errors = array();
        foreach ($data as $key => $value) {
            $value = trim($value);
            switch ($key) {
                case 'r_comment':
                    $validator = new Zend_Validate_NotEmpty();
                    if(!$validator->isValid($value)){
                        foreach ($validator->getMessages() as $message) {
                            $errors[$key][] = $this->getTranslatorHelper()->Translator()->__("Enter Valid comment.");
                        }
                    } 
                    break;
 
                default:
                    break;
            }
        }
        return $errors;
    }

    public function _getUserModel() {
        if(is_null($this->userModel)) {
            $this->userModel = new Account_Model_User();
        }
        return $this->userModel;
    }
}