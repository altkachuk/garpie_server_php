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
class MessageController extends Controller
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
    
    public function send($data, $timestamp)
    {
        $uniqueid = $data['uniqueid'];
        $user = Users::model()->findByUniqueid($uniqueid);
        
        if (!isset($data['session_id'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $session_id = $data['session_id'];
        
        if (!isset($data['value'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return;
        }
        $value = $data['value'];
        
        $message = new Messages();
        $message->user_id = $user['id'];
        $message->session_id = $session_id;
        $message->value = $value;
        $message->time = $timestamp;
        $message->save();
        
        $messages = array();
        $messages[] = $message->toObject();
        
        $result = array('status'=>'200', 'messages'=>$messages);
        echo json_encode($result);
    }
}
