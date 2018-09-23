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
class PingController extends Controller
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
    
    public function ping($data, $timestamp)
    {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        $reqeustTimestamp = $timestamp - 2*24*60*60;
        if (isset($data['timestamp'])) {
            $tempTimestamp = date($data['timestamp']);
            if ($tempTimestamp > $reqeustTimestamp) {
                $reqeustTimestamp = $tempTimestamp;
            }
        }
        
        if (isset($data['commands'])) {
            $commands = $data['commands'];
            foreach ($commands as $command) {
                if ($command['command'] == 'sessionUserLocationUpdate') {
                    list($cc) = Yii::app()->createController('api/index');
                    $cc->actionSessionUserLocationUpdate($command);
                } else if ($command['command'] == 'sessionUserLocationStaticUpdate') {
                    list($cc) = Yii::app()->createController('api/index');
                    $cc->actionSessionUserLocationStaticUpdate($command);
                } else if ($command['command'] == 'sessionUserUpdate') {
                    list($cc) = Yii::app()->createController('api/index');
                    $cc->actionSessionUserUpdate($command);
                }
            }
        }
        
        $sessions = Sessions::model()->findAllByTime($user['id'], $reqeustTimestamp);
        $session_users = SessionUsers::model()->findAllByTime($user['id'], $reqeustTimestamp);
        $garpies = Garpies::model()->findAllByTime($user['id'], $reqeustTimestamp);
        $messages = Messages::model()->findAllByTime($user['id'], $reqeustTimestamp);
        
        $oldTimestamp = $reqeustTimestamp;
        
        foreach ($sessions as $session) {
            $temptime = $session['time'];
            if ($reqeustTimestamp < $temptime) {
                $reqeustTimestamp = $temptime + 0.0001;
            }
        }
        
        foreach ($session_users as $session_user) {
            $temptime = $session_user['time'];
            if ($reqeustTimestamp < $temptime) {
                $reqeustTimestamp = $temptime + 0.0001;
            }
        }
        
        foreach ($messages as $message) {
            $temptime = $message['time'];
            if ($reqeustTimestamp < $temptime) {
                $reqeustTimestamp = $temptime + 0.0001;
            }
        }
        
        $result = array(
            'status'=>'200',
            'timestamp' => "$reqeustTimestamp");
        if (count($sessions) > 0) {
            $result['sessions'] = $sessions;
        }
        if (count($session_users) > 0) {
            $result['session_users'] = $session_users;
            $result['garpies'] = $garpies;
            $result['users'] = Users::model()->findAllByTime($user['id'], $oldTimestamp);
        }
        if (count($messages) > 0) {
            $result['messages'] = $messages;
        }
        
        echo json_encode($result);
    }
    
    
    
    
    
    
}
