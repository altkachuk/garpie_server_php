<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CodeController
 *
 * @author andreyltkachuk
 */
class CodeController extends Controller
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
            array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('send',),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    public function actionSend($uniqueid) {
        $code = rand(1000, 9999);
        
        $model = Codes::model()->findByAttributes(array('uniqueid'=>$uniqueid));
        if ($model == NULL) {
            $model = new Codes();
        }
        $model->uniqueid = $uniqueid;
        $model->code = $code;
        $model->save();
        
        require_once(dirname(__FILE__) . '/../extensions/twilio/Twilio/autoload.php');
        
        // test
        /*$sid = 'ACf7629d46f067a21a72946dc9d183d507';
        $token = '8273610d66395b53772f425bb0f2c615';*/
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
        );
        
        echo json_encode(array('code'=>$code));
    }
    
    public function send($data) {
        if (!isset($data['uniqueid'])) {
            $result = array('status'=>'400', 'message'=>'empty uniqueid');
            echo json_encode($result);
            return;
        }
        $uniqueid = $data['uniqueid'];
        $code = rand(1000, 9999);
        
        $model = Codes::model()->findByAttributes(array('uniqueid'=>$uniqueid));
        if ($model == NULL) {
            $model = new Codes();
        }
        $model->uniqueid = $uniqueid;
        $model->code = $code;
        $model->save();
        
        
        
        require_once(dirname(__FILE__) . '/../extensions/twilio/Twilio/autoload.php');
        
        // test
        /*$sid = 'ACf7629d46f067a21a72946dc9d183d507';
        $token = '8273610d66395b53772f425bb0f2c615';*/
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
        );
        
        $result = array('status'=>'200', 'code'=>$code, 'sid'=>$message->sid);
        echo json_encode($result);
    }
}
