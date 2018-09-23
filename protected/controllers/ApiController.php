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
class ApiController extends Controller
{
    public function filters() {
		return array(
			'accessControl',
            'postOnly + create',
            'postOnly + complete',
            'postOnly + accept',
            'postOnly + reject',
            'postOnly + updateGarpie',
            array(
                'application.filters.UserAccessPostFilter - validate, garpieGetAll, userCreate, userGet'
            )
		);
	}
    
    public function accessRules() {
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array(
                    'validate',
                    'garpieGetAll',
                    'userCreate', 'userUpdate', 'userGet', 'userTest',
                    'userTokenUpdate',
                    'sessionCreate', 'sessionUpdate', 'sessionAddUsers',
                    'sessionUserAccept', 'sessionUserReject', 'sessionUserUpdate', 'sessionUserLocationUpdate', 'sessionUserLocationStaticUpdate', 'sessionUserLeave',
                    'messageSend',
                    'ping', 'removeOldSessions'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    public function actionValidate() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        list($cc) = Yii::app()->createController('code/index');
        echo $cc->send($data);
    }
    
    public function actionGarpieGetAll() {
        list($cc) = Yii::app()->createController('garpie/index');
        echo $cc->getAll();
    }
    
    public function actionUserCreate() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        list($cc) = Yii::app()->createController('user/index');
        echo $cc->create($data);
    }
    
    public function actionUserUpdate() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        list($cc) = Yii::app()->createController('user/index');
        echo $cc->update($data);
    }
    
    public function actionUserGet($uniqueid) {
        list($cc) = Yii::app()->createController('user/index');
        echo $cc->get($uniqueid);
    }
    
    public function actionUserTest() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        list($cc) = Yii::app()->createController('user/index');
        echo $cc->test($data);
    }
    
    public function actionUserTokenUpdate() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        list($cc) = Yii::app()->createController('user/index');
        echo $cc->tokenUpdate($data);
    }
    
    public function actionSessionCreate() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('session/index');
        echo $cc->create($data, $timestamp);
    }
    
    public function actionSessionUpdate() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('session/index');
        echo $cc->update($data, $timestamp);
    }
    
    public function actionSessionAddUsers() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('session/index');
        echo $cc->addUsers($data, $timestamp);
    }
    
    public function actionSessionUserAccept() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('sessionUser/index');
        echo $cc->accept($data, $timestamp);
    }
    
    public function actionSessionUserReject() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('sessionUser/index');
        echo $cc->reject($data, $timestamp);
    }
    
    public function actionSessionUserUpdate($data=null, $timestamp=null) {
        $useResponse = false;
        if ($timestamp == null) {
            $timestamp = $this->getTimestamp();
        }
        if ($data == null) {
            $data = json_decode(file_get_contents('php://input'), true);
            $useResponse = true;
        }
        
        list($cc) = Yii::app()->createController('sessionUser/index');
        echo $cc->update($data, $timestamp, $useResponse);
    }
    
    public function actionSessionUserLocationUpdate($data=null, $timestamp=null) {
        $useResponse = false;
        if ($timestamp == null) {
            $timestamp = $this->getTimestamp();
        }
        if ($data == null) {
            $data = json_decode(file_get_contents('php://input'), true);
            $useResponse = true;
        }
        
        list($cc) = Yii::app()->createController('sessionUser/index');
        echo $cc->locationUpdate($data, $timestamp, $useResponse);
    }
    
    public function actionSessionUserLocationStaticUpdate($data=null, $timestamp=null) {
        $useResponse = false;
        if ($timestamp == null) {
            $timestamp = $this->getTimestamp();
        }
        if ($data == null) {
            $data = json_decode(file_get_contents('php://input'), true);
            $useResponse = true;
        }
        
        list($cc) = Yii::app()->createController('sessionUser/index');
        echo $cc->locationStaticUpdate($data, $timestamp, $useResponse);
    }
    
    public function actionSessionUserLeave() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('sessionUser/index');
        echo $cc->leave($data, $timestamp);
    }
    
    public function actionMessageSend() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('message/index');
        echo $cc->send($data, $timestamp);
    }

    public function actionPing() {
        $data = json_decode(file_get_contents('php://input'), true);
        $timestamp = $this->getTimestamp();
        
        list($cc) = Yii::app()->createController('ping/index');
        echo $cc->ping($data, $timestamp);
    }
    
    // remove sessions after 24hrs
    public function actionRemoveOldSessions() {
        $timestamp = $this->getTimestamp();
        $oldTimestammp = $timestamp - (60 * 60 * 24 * 2);
        echo $oldTimestammp;
        
        $criteria = new CDbCriteria();
        $criteria->compare('enabled', '1');
        $criteria->compare('time', "<$oldTimestammp");
        $sessions = Sessions::model()->findAll($criteria);
        
        foreach ($sessions as $session) {
            foreach ($session->sessionUsers as $sessionUser) {
                if ($sessionUser->enabled == 1) {
                    $sessionUser->enabled = 0;
                    $sessionUser->time = $timestamp;
                    $sessionUser->save();
                }
            }
            
            $session->enabled = 0;
            $session->time = $timestamp;
            $session->save();
        }
    }

    private function getTimestamp() {
        $timestamp = microtime(true);
        return $timestamp;
    }
    
}
