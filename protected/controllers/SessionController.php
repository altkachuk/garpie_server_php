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
class SessionController extends Controller
{
    public function filters()
	{
		return array(
			'accessControl',
		);
	}
    
    public function accessRules()
	{
		return array(
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    public function create($data, $timestamp) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        $session = new Sessions();
        if (!isset($data['name'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session->name = $data['name'];
        
        if (isset($data['description'])) {
            $description = $data['description'];
            $session->description = $description;
        }
        
        if (!isset($data['garpie_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $garpie_id = $data['garpie_id'];
        
        if (!isset($data['users_uid'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $users_uid = $data['users_uid'];
        
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
        
        $session->time = $timestamp;
        $session->save();
        
        $sessions = array();
        $sessions[] = $session->toObject();
        
        $session_users = array();
        $garpies = array();
        
        SessionUsers::model()->createSessionUser($user['id'], $session->id, $timestamp, 1, 1, $garpie_id);
        $sessionUser = SessionUsers::model()->findBySession($user['id'], $session->id);
        $sessionUser['latitude'] = $latitude;
        $sessionUser['longitude'] = $longitude;
        $sessionUser['altitude'] = $altitude;
        $session_users[] = $sessionUser;
        
        $garpies[] = Garpies::model()->findById($garpie_id);
        
        SessionUserLocations::model()->createSessionUserLocation($sessionUser['id'], $latitude, $longitude, $altitude);
        
        foreach ($users_uid as $uid) {
            $usr = Users::model()->findByUniqueid($uid);
            if (!$usr) {
                $usr = new Users();
                $usr->uniqueid = $uid;
                $usr->enabled = 0;
                $usr->save();
            }
            
            SessionUsers::model()->createSessionUser($usr['id'], $session->id, $timestamp);
            $sessionUser = SessionUsers::model()->findBySession($usr['id'], $session->id);
            
            $session_users[] = $sessionUser;
        }
        
        $users = Users::model()->findAllBySession($session->id);
        
        $message = array
        (
            'body' 	=> $user['name'] . ' invited you to session: ' . $session->name,
            'title' => 'Garpie',
            'sound' => 'default',
            'badge' => '1'
        );
        
        Yii::import('application.controllers.NotificationController');
        NotificationController::send($message, $users);
        
        $result = array(
            'status'=>'200', 
            'sessions'=>$sessions, 
            'garpies'=>$garpies,
            'users'=>$users,
            'session_users'=>$session_users);
        
        echo json_encode($result);
    }
    
    public function update($data, $timestamp) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        $session = Sessions::model()->findByPk($session_id);
        if (isset($data['name'])) {
            $name = $data['name'];
            $session->name = $name;
        }
        if (isset($data['description'])) {
            $description = $data['description'];
            $session->description = $description;
        }
        $session->time = $timestamp;
        $session->save();
        
        $sessions = array();
        $sessions[] = $session->toObject();
        
        $result = array('status'=>'200', 'sessions'=>$sessions);
        echo json_encode($result);
    }
    
    public function addUsers($data, $timestamp) {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        if (!isset($data['users_uid'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $users_uid = $data['users_uid'];
        
        $session = Sessions::model()->findByPk($session_id);
        
        $session_users = array();
        
        foreach ($users_uid as $uid) {
            $user = Users::model()->findByUniqueid($uid);
            if (!$user) {
                $user = new Users();
                $user->uniqueid = $uid;
                $user->enabled = 0;
                $user->save();
            }
            
            $sessionUser = SessionUsers::model()->findBySession($user['id'], $session->id);
            if (!$sessionUser) {
                SessionUsers::model()->createSessionUser($user['id'], $session->id, $timestamp);
                $sessionUser = SessionUsers::model()->findBySession($user['id'], $session->id);
                $session_users[] = $sessionUser;
            } else if ($sessionUser['enabled'] == 0) {
                SessionUsers::model()->readd($sessionUser['id'], $timestamp);
                $sessionUser = SessionUsers::model()->findBySession($user['id'], $session->id);
                $session_users[] = $sessionUser;
            }
        }
        
        
        
        $tmpusers = Users::model()->findAllBySession($session->id);
        
        $users = array();
        foreach ($tmpusers as $user) {
            foreach ($session_users as $sessionUser) {
                if ($user['uniqueid'] == $sessionUser['uniqueid']) {
                    $users[] = $user;
                }
            }
        }
        
        if (count($users) > 0) {
            $message = array
            (
                'body' 	=> 'You are invited to session: ' . $session->name,
                'title' => 'Garpie',
                'sound' => 'default',
                'badge' => '1'
            );

            Yii::import('application.controllers.NotificationController');
            NotificationController::send($message, $users);
        }
        
        $result = array(
            'status'=>'200',
            'users'=>$users,
            'session_users'=>$session_users);
        
        echo json_encode($result);
    }
}
