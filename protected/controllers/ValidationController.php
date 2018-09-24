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
class ValidationController extends Controller
{
    public function filters() {
		return array(
			'accessControl',
            'postOnly + requestCode',
            'postOnly + token',
            /*array(
                'application.filters.UserAccessPostFilter - validate, garpieGetAll, userCreate, userGet'
            )*/
		);
	}
    
    public function accessRules() {
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array(
                    'requestCode',
                    'token',
                    ),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    public function actionRequestCode($uniqueid) {
        $code = rand(1000, 9999);
        
        $model = Codes::model()->findByAttributes(array('uniqueid'=>$uniqueid));
        if ($model == NULL) {
            $model = new Codes();
        }
        $model->uniqueid = $uniqueid;
        $model->code = $code;
        $model->save();
        
        
        
        /*require_once(dirname(__FILE__) . '/../extensions/twilio/Twilio/autoload.php');
        
        // test
        //$sid = 'ACf7629d46f067a21a72946dc9d183d507';
        //$token = '8273610d66395b53772f425bb0f2c615';
        // live
        $sid = 'AC92be15f2fff79ac6311408889de8160c';
        $token = '35d269fa0e64fd4ba45516b4b78c2f74';
        
        $client = new Twilio\Rest\Client($sid, $token);
        
        $text = 'Your code is: ' . $code;
        
        // Use the client to do fun stuff like send text messages!
        $message = $client->messages->create(
            // the number you'd like to send the message to
            $uniqueid,
            array(
                // A Twilio phone number you purchased at twilio.com/console
                'from' => '+14086598308 ',
                // the body of the text message you'd like to send
                'body' => $text
            )
        );*/
        
        $result = array('code'=>$code/*, 'sid'=>$message->sid*/);
        echo json_encode($result);
    }
    
    public function actionToken($uniqueid, $code) {        
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
        $user->enabled = 1;
        $user->save();
        
        $token = bin2hex(rand(1000000000, 9999999999));
        $userToken = new UserTokens();
        $userToken->user_id = $user['id'];
        $userToken->token = $token;
        $userToken->save();
        
        $result = array('token'=>$token);
        echo json_encode($result);
    }
}
