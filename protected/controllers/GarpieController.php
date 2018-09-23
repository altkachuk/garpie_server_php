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
class GarpieController extends Controller
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
    
    public function getAll() {
        $garpies = Garpies::model()->findAllPredefined();
        
        $result = array('status'=>'200', 'garpies'=>$garpies);
        echo json_encode($result);
    }
}
