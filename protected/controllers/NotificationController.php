<?php

class NotificationController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/content';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    static public function send($message, $users) {        
        // API access key from Google API's Console
        $API_ACCESS_KEY = 'AAAAPnYIvr8:APA91bHlGPLk_Hjtaf2AAid8llfUKtzYSXwWMtB40LJnzz9Fei3dGSbTd3GlTX7XcFxw_57uQlmFi8FCvbZjK-Fn_w4jh_1vHfsP5zd2PzPwfA7YRaCPGqt6cuOg48xYI9mgyJeyNkG4Lg86ru2QWvXgGxgdAhYvVg';
        
        $fields = array('notification'	=> $message);
        
        $tokens = UserTokens::model()->findAllByUsers($users);
        if (count($tokens) == 1) { 
            $fields['to'] = array_shift($tokens);
        } else {
            $fields['registration_ids'] = $tokens;
        }

        $headers = array
        (
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
    }

}