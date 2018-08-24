<?php
class Controller_Abstract extends Zend_Controller_Action
{
    private $userModel      = null;
    private $cartModel      = null;
    
    public function preDispatch() {
        parent::preDispatch();

        if(!$this->_getSession()->token) {
     		if($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()){
               
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                $response = array(
                    'type' =>'loginin',
                );

                echo json_encode($response);
                die;
            } else {
                $this->_redirect("");
            }
            //$this->_forward('index', 'user_login', 'account');
        } else {
            $userbase = unserialize($this->_getSession()->user);
            $this->view->loggedin    = $userbase;
            $user = $this->view->loggedin;
            
            $this->view->userData = $this->_getUserModel()->getUserDeatilById($userbase['id']);
            $this->view->cartItem = $this->_getCartModel()->getCartByUserId($userbase['id'])->attachProduct();

            //$this->view->log_user = $this->_getprofileModel()->getProfileByUserId($user['id']);
        }
    }

    protected function _getUserModel() {
        if(is_null($this->userModel)) {
            $this->userModel = new Account_Model_User();
        }
        return $this->userModel;
    }

    protected function _getCartModel() {
        if(is_null($this->cartModel)) {
            $this->cartModel = new Account_Model_User_Cart();
        }
        return $this->cartModel;
    }

   

    protected function _getSession() {
        return new Zend_Session_Namespace("ad-user");
    }
}