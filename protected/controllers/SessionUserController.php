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
class SessionUserController extends Controller
{
    public function filters()
	{
		return array(
			'accessControl',
            'postOnly + create',
            array(
                'application.filters.UserAccessPostFilter + create, update'
            )
		);
	}
    
    public function accessRules()
	{
		return array(
            array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('create','update',),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    public function actionCreate($session_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = array();
        
        $user = $this->getUser();
        $sesionUser = new SessionUsers();
        $sesionUser->user_id = $user->id;
        $sesionUser->session_id = $session_id;
        $sesionUser->is_master = 1;
        $sesionUser->state = 1;
        $sesionUser->save();
        $result[] = $sesionUser->toObject();
        
        foreach ($data as $uniqueid) {
            $user = Users::model()->findByUniqueid($uniqueid);
            if ($user == null) {
                $user = new Users();
                $user->uniqueid = $uniqueid;
                $user->save();
            }
            $sesionUser = new SessionUsers();
            $sesionUser->user_id = $user->id;
            $sesionUser->session_id = $session_id;
            $sesionUser->save();
            $result[] = $sesionUser->toObject();
        }
        
        echo json_encode($result);
    }
    
    public function actionUpdate() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sessionUser = SessionUsers::model()->findByPk($data['id']);
        foreach ($data as $key => $value) {
            if ($key != 'user') {
                $sessionUser[$key] = $value;
            }
        }
        $sessionUser->save();
        echo json_encode($sessionUser->toObject());
    }
    
    public function accept($data, $timestamp) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        if (!isset($data['garpie_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $garpie_id = $data['garpie_id'];
        
        if (!isset($data['latitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $latitude = $data['latitude'];
        
        if (!isset($data['longitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $longitude = $data['longitude'];
        
        if (!isset($data['altitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $altitude = $data['altitude'];
        
        SessionUsers::model()->accept($user['id'], $session_id, $garpie_id, $timestamp);
        $sessionUser = SessionUsers::model()->findBySession($user['id'], $session_id);

        
        SessionUserLocations::model()->createSessionUserLocation($sessionUser['id'], $latitude, $longitude, $altitude);
        
        $users = Users::model()->findAllBySession($session_id);
        $session_users = SessionUsers::model()->findAllBySession($session_id);
        $garpies = Garpies::model()->findAllBySession($session_id);
        $messages = Messages::model()->findAllBySession($session_id);
        
        Yii::import('application.controllers.SessionController');
        $session = Sessions::model()->findById($session_id);
        
        $notificationUsers = array();
        foreach ($session_users as $session_user) {
            if ($session_user['is_master'] == 1) {
                $notificationUsers[] = array('id'=>$session_user['user_id']);
            }
        }
        
        $message = array
        (
            'body' 	=> $user['name'] . ' accepted session: ' . $session['name'],
            'title' => 'Garpie',
            'sound' => 'default',
            'badge' => '1'
        );
        Yii::import('application.controllers.NotificationController');
        NotificationController::send($message, $notificationUsers);
        
        $result = array(
            'status'=>'200',
            'users' => $users,
            'session_users'=>$session_users,
            'garpies'=>$garpies,
            'messages'=>$messages);
        
        echo json_encode($result);
    }
    
    public function reject($data, $timestamp) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);

        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        $sessionUser = SessionUsers::model()->findByAttributes(array('user_id'=>$user['id'], 'session_id'=>$session_id));
        $sessionUser->enabled = 0;
        $sessionUser->time = $timestamp;
        $sessionUser->save();
        
        $session_users = array();
        $session_users[] = $sessionUser->toObject();
        
        $session = Sessions::model()->findById($session_id);
        
        $notificationUsers = array();
        $all_session_users = SessionUsers::model()->findAllBySession($session_id);
        foreach ($all_session_users as $session_user) {
            if ($session_user['is_master'] == 1) {
                $notificationUsers[] = array('id'=>$session_user['user_id']);
            }
        }
        
        $message = array
        (
            'body' 	=> $user['name'] . ' declined session: ' . $session['name'],
            'title' => 'Garpie',
            'sound' => 'default',
            'badge' => '1'
        );
        Yii::import('application.controllers.NotificationController');
        NotificationController::send($message, $notificationUsers);
        
        $result = array(
            'status'=>'200',
            'session_users'=>$session_users);
        
        echo json_encode($result);
    }
    
    public function update($data, $timestamp, $useResponse) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        $sessionUser = SessionUsers::model()->findByAttributes(array('user_id'=>$user['id'], 'session_id'=>$session_id));
        if (isset($data['garpie_id'])) {
            $garpie_id = $data['garpie_id'];
            $sessionUser->garpie_id = $garpie_id;
        }
        
        $session_users = array();
        $garpies = array();
        
        if (isset($data['state'])) {
            $state = $data['state'];
            if ($state == 3) {
                if ($sessionUser->is_master == 1) {
                    $statcUser = Users::model()->findByUniqueid(Users::$staticUniqueId);
                    if (!$statcUser) {
                        Users::model()->createStaticUser();
                        $statcUser = Users::model()->findByUniqueid(Users::$staticUniqueId);
                    }
                    
                    $sessionUser->state = $state;
                    
                    $staticSessionUser = SessionUsers::model()->findByAttributes(array('session_id'=>$sessionUser->session_id, 'user_id'=>$statcUser['id']));
                    if (!$staticSessionUser)
                        $staticSessionUser = new SessionUsers();
                    $staticSessionUser->session_id = $sessionUser->session_id;
                    $staticSessionUser->garpie_id = $sessionUser->garpie_id;
                    $staticSessionUser->user_id = $statcUser['id'];
                    $staticSessionUser->state = $sessionUser->state;
                    $staticSessionUser->enabled = $sessionUser->enabled;
                    $staticSessionUser->time = $timestamp;
                    $staticSessionUser->save();
                    
                    $staticSessionUser = SessionUsers::model()->findByAttributes(array('user_id'=> $statcUser['id'], 'session_id'=>$session_id));
                    
                    $staticSessionUserLocation = new SessionUserLocations();
                    $arSessionObject = $sessionUser->toObject();
                    $staticSessionUserLocation->latitude = $arSessionObject['latitude'];
                    $staticSessionUserLocation->longitude = $arSessionObject['longitude'];
                    $staticSessionUserLocation->altitude = $arSessionObject['altitude'];
                    $staticSessionUserLocation->session_user_id = $staticSessionUser->id;
                    $staticSessionUserLocation->save();
                    
                    
                    
                    $session_users[] = $staticSessionUser->toObject();
                }
            } else {
                if ($sessionUser->is_master == 1) {
                    $statcUser = Users::model()->findByUniqueid(Users::$staticUniqueId);
                    if ($statcUser) {
                        $staticSessionUser = SessionUsers::model()->findByAttributes(array('user_id'=> $statcUser['id'], 'session_id'=>$session_id));
                        if ($staticSessionUser && $staticSessionUser->enabled == 1) {
                            $staticSessionUser->enabled = 0;
                            $staticSessionUser->time = $timestamp;
                            $staticSessionUser->save();

                            $staticSessionUser = SessionUsers::model()->findByAttributes(array('user_id'=> $statcUser['id']));
                            $session_users[] = $staticSessionUser->toObject();
                        }
                    }
                }
                
                $sessionUser->state = $state;
            }
        }
        
        $sessionUser->time = $timestamp;
        $sessionUser->save();
        
        
        $session_users[] = $sessionUser->toObject();
        $garpies[] = Garpies::model()->findById($sessionUser->garpie_id);
        
        if (!$useResponse) {
            return;
        }
        $result = array(
            'status'=>'200',
            'session_users'=>$session_users,
            'garpies'=>$garpies);
        echo json_encode($result);
    }
    
    public function locationUpdate($data, $timestamp, $useResponse) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        if (!isset($data['latitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $latitude = $data['latitude'];
        
        if (!isset($data['longitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $longitude = $data['longitude'];
        
        if (!isset($data['altitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $altitude = $data['altitude'];
        
        $session_user = SessionUsers::model()->findBySession($user['id'], $session_id);
        
        if (!$session_user) {
            if (!$useResponse) {
                return;
            }
            $result = array('status'=>'200');
            echo json_encode($result);
            return;
        }
        
        SessionUserLocations::model()->createSessionUserLocation($session_user['id'], $latitude, $longitude, $altitude);
        SessionUsers::model()->udateTime($session_user['id'], $timestamp);
        
        $session_user['latitude'] = $latitude;
        $session_user['longitude'] = $longitude;
        $session_user['altitude'] = $altitude;
        
        $session_users = array();
        $session_users[] = $session_user;
        
        if (!$useResponse) {
            return;
        }
        $result = array('status'=>'200', 'session_users'=>$session_users);
        echo json_encode($result);
    }
    
    public function locationStaticUpdate($data, $timestamp, $useResponse) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        if (!isset($data['latitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $latitude = $data['latitude'];
        
        if (!isset($data['longitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $longitude = $data['longitude'];
        
        if (!isset($data['altitude'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $altitude = $data['altitude'];
        
        $staticUser = Users::model()->findByUniqueid(Users::$staticUniqueId);
        $session_user = SessionUsers::model()->findBySession($staticUser['id'], $session_id);
        
        if (!$session_user) {
            if (!$useResponse) {
                return;
            }
            $result = array('status'=>'200');
            echo json_encode($result);
            return;
        }
        
        SessionUserLocations::model()->createSessionUserLocation($session_user['id'], $latitude, $longitude, $altitude);
        SessionUsers::model()->udateTime($session_user['id'], $timestamp);
        
        $session_user['latitude'] = $latitude;
        $session_user['longitude'] = $longitude;
        $session_user['altitude'] = $altitude;
        
        $session_users = array();
        $session_users[] = $session_user;
        
        if (!$useResponse) {
            return;
        }
        $result = array('status'=>'200', 'session_users'=>$session_users);
        echo json_encode($result);
    }
    
    public function leave($data, $timestamp) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        $session_user = SessionUsers::model()->findBySession($user['id'], $session_id);
        
        $session_users = array();
        
        if (!$session_user || $session_user['enabled'] == 0) {
            $session_users[] = $session_user;
            $result = array('status'=>'200', 'session_users'=>$session_users);
            echo json_encode($result);
            return;
        }
        
        $query = 'UPDATE session_users '
                . 'SET time = :time, enabled = \'0\' '
                . 'WHERE id = :id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
        $command->bindParam(':id', $session_user['id'], PDO::PARAM_INT);
        $command->execute();
        
        if ($session_user['is_master'] == 1) {
            $query = 'UPDATE sessions '
                . 'SET time = :time, enabled = \'0\' '
                . 'WHERE id = :id';
            $command = Yii::app()->db->cache(0)->createCommand($query);
            $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
            $command->bindParam(':id', $session_user['session_id'], PDO::PARAM_INT);
            $command->execute();
            
            $query = 'UPDATE session_users '
                    . 'SET time = :time, enabled = \'0\' '
                    . 'WHERE session_id = :session_id AND enabled = 1';
            $command = Yii::app()->db->cache(0)->createCommand($query);
            $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
            $command->bindParam(':session_id', $session_user['session_id'], PDO::PARAM_INT);
            $command->execute();
        }
        
        $session_user['enabled'] = 0;
        $session_users[] = $session_user;
        
        $result = array('status'=>'200', 'session_users'=>$session_users);
        echo json_encode($result);
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
    
}
