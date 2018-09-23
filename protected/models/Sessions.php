<?php

/**
 * This is the model class for table "sessions".
 *
 * The followings are the available columns in table 'sessions':
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $enabled
 * @property double $time
 *
 * The followings are the available model relations:
 * @property SessionUsers[] $sessionUsers
 * @property Messages[] $messages
 */
class Sessions extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Sessions the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'sessions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('time', 'numerical'),
			array('enabled', 'length', 'max'=>1),
            array('name, description', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, description, enabled, time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'sessionUsers' => array(self::HAS_MANY, 'SessionUsers', 'session_id'),
            'messages' => array(self::HAS_MANY, 'Messages', 'session_id'),
		);
	}
    
    public function findById($id) {
        $query = 'SELECT id, name, description, enabled, time FROM sessions WHERE id  = :id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':id', $id, PDO::PARAM_INT);
        $session = $command->query();
        $session = $session->read();
        
        return $session;
    }
    
    public function findAllByTime($user_id, $timestamp) {
        $query = 'SELECT t1.id, t1.name, t1.description, t1.enabled, t1.time '
                . 'FROM sessions t1 '
                . 'LEFT JOIN session_users t2 ON t2.session_id = t1.id '
                . 'WHERE t2.user_id = :user_id AND (t1.time >= :timestamp OR (t2.state = 0 AND t2.time >= :timestamp)) AND t2.enabled = 1';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $command->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);
        $sessions = $command->queryAll();
        
        return $sessions;
    }
    
    public function toObject() {
        $result = array(
            'id'=>$this->id, 
            'name' =>  $this->name, 
            'description' => $this->description, 
            'enabled' => $this->enabled);
        
        return $result;
    }
}