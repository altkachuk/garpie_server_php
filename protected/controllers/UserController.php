<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author andreyltkachuk
 */
class UserController extends Controller
{
    
    public function filters()
	{
		return array(
			'accessControl',
            'postOnly + update',
            array(
                'application.filters.UserAccessPostFilter + update'
            )
		);
	}
    
    public function accessRules()
	{
		return array(
            array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('update',),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    static $staticUniqueId = '+000000000000';
    
    public function actionUpdate() {
        $data = json_decode(file_get_contents('php://input'), true);
 
        $user = $this->getUser();
        foreach ($data as $key => $value) {
            $user[$key] = $value;
        }
        $user->save();
        echo json_encode($user->toObject());
    }
    
    private function getUser() {
        if (!function_exists('apache_request_headers')) {
            function apache_request_headers() {
                $arh = array();
                $rx_http = '/\AHTTP_/';
                foreach ($_SERVER as $key => $val) {
                    if (preg_match($rx_http, $key)) {
                        $arh_key = preg_replace($rx_http, '', $key);
                        $rx_matches = array();
                        $rx_matches = explode('_', $arh_key);
                        if (count($rx_matches) > 0 && strlen($arh_key) > 2) {
                            foreach ($rx_matches as $ak_key => $ak_val) {
                                $rx_matches[$ak_key] = ucfirst($ak_val);
                            }
                            $arh_key = implode('_', $rx_matches);
                        }
                        $arh[$arh_key] = $val;
                    }
                }

                return $arh;
            }
        }
        
        $headers = apache_request_headers();
        $matches = array();
        preg_match('/Token (.*)/', $headers['Authorization'], $matches);
        $token = $matches[1];
        $userToken = UserTokens::model()->findByAttributes(array('token'=>$token));
        $user = Users::model()->findByPk($userToken->user_id);
        return $user;
    }


    public function create($data) {
        if (!isset($data['uniqueid'])) {
            $result = array('status'=>'400', 'message'=>'empty uniqueid');
            echo json_encode($result);
            return;
        }
        $uniqueid = $data['uniqueid'];
        
        if (!isset($data['name'])) {
            $result = array('status'=>'400', 'message'=>'empty name');
            echo json_encode($result);
            return;
        }
        $name = $data['name']; 
        
        if (!isset($data['code'])) {
            $result = array('status'=>'400', 'message'=>'empty code');
            echo json_encode($result);
            return;
        }
        $code = $data['code'];
        
        
        $codeModel = Codes::model()->findByAttributes(array('uniqueid'=>$uniqueid, 'code'=>$code));
        if ($codeModel == null) {
            $result = array('status'=>'666', 'message'=>'Code wrong');
            echo json_encode($result);
            return;
        }
        
        $user = Users::model()->findByAttributes(array('uniqueid'=>$uniqueid));
        
        if (!$user) {
            $user = new Users();
        }
        
        $user->uniqueid = $uniqueid;
        $user->name = $name;
        $user->enabled = 1;
        
        if (isset($data['description'])) {
            $description = $data['description'];
            $user->description = $description;
        }
        
        $user->save();
        
        $users = array();
        $users[] = $user->toObject();
        
        $result = array('status'=>'200', 'users'=>$users);
        echo json_encode($result);
    }
    
    public function update($data) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByAttributes(array('uniqueid'=>$uniqueid));
        
        if (isset($data['name'])) {
            $name = $data['name'];
            $user->name = $name;
        }
        if (isset($data['description'])) {
            $description = $data['description'];
            $user->description = $description;
        }
        $user->save();
        
        $users = array();
        $users[] = $user->toObject();
        
        $result = array('status'=>'200', 'users'=>$users);
        echo json_encode($result);
    }
    
    public function get($uniqueid) {
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!$user) {
            $result = array('status'=>'401');
            echo json_encode($result);
            return;
        }
        
        $users = array();
        $users[] = $user;
        
        $result = array('status'=>'200', 'users'=>$users);
        echo json_encode($result);
    }
    
    public function tokenUpdate($data) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['token'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $token = $data['token'];
        
        $userToken = UserTokens::model()->findByAttributes(array('user_id'=>$user['id']));
        if (!$userToken) {
            $userToken = new UserTokens();
        }
        
        $userToken->user_id = $user['id'];
        $userToken->token = $token;
        $userToken->save();

        $result = array('status'=>'200');
        echo json_encode($result);
    }
    
    public function test($data) {
        if (!isset($data['uniqueid'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $uniqueid = $data['uniqueid'];
        
        $user = Users::model()->findByUniqueid($uniqueid);
        if (!$user) {
            $result = array('status'=>'401');
            echo json_encode($result);
            return;
        }
        
        if (!isset($data['users_uid'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $users_uid = $data['users_uid'];
        
        $query = 'SELECT id, uniqueid, name, description, photo, enabled FROM users WHERE enabled = :enabled AND ( ';
        $i = 0;
        foreach ($users_uid as $uid) {
            if ($i > 0)
                $query .= ' OR ';
            $query .= 'uniqueid = :uniqueid' . $i;
            $i++;
        }
        $query .= ')';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':enabled', 1, PDO::PARAM_INT);
        $i = 0;
        foreach ($users_uid as $uid) {
            $command->bindValue(':uniqueid'.$i, $uid, PDO::PARAM_STR);
            $i++;
        }
        $users = $command->queryAll();
        
        $result = array('status'=>'200', 'users'=>$users);
        echo json_encode($result);
    }
    
}
