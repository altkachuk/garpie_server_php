<?php

class SessionUserLocationController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
            'postOnly + create',
            array(
                'application.filters.UserAccessPostFilter + create'
            )
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('create'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	
	public function actionCreate() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $model = new SessionUserLocations();
        foreach ($data as $key => $value) {
            $model[$key] = $value;
        }
        $model->time = $this->getTimestamp();
        $model->save();
        
        echo json_encode($model->toObject());
	}
    
    private function getTimestamp() {
        $timestamp = microtime(true);
        return $timestamp;
    }
}
