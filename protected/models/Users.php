<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property integer $id
 * @property string $uniqueid
 * @property string $name
 * @property string $description
 * @property string $photo
 * @property string $enabled
 *
 * The followings are the available model relations:
 * @property SessionUsers[] $sessionUsers
 * @property Messages[] $messages
 */
class Users extends CActiveRecord
{
    static $staticUniqueId = '+000000000000';
    
    
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('enabled', 'length', 'max'=>1),
			array('uniqueid', 'required'),
			array('uniqueid, name, description', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uniqueid, name, description, enabled', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'sessionUsers' => array(self::HAS_MANY, 'SessionUsers', 'user_id'),
            'messages' => array(self::HAS_MANY, 'Messages', 'user_id'),
		);
	}
    
    public function findByUniqueid($uniqueid) {
        $query = 'select id, uniqueid, name, description, photo, enabled from users where uniqueid  = :uniqueid';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':uniqueid', $uniqueid, PDO::PARAM_STR);
        $user = $command->query();
        $user = $user->read();
        
        return $user;
    }
    
    public function findAllBySession($session_id) {
        $query = 'SELECT t1.id, t1.uniqueid, t1.name, t1.description, t1.photo, t1.enabled '
                . 'FROM users t1 '
                . 'LEFT JOIN session_users t2 ON t2.user_id = t1.id '
                . 'WHERE t2.session_id = :session_id AND t1.uniqueid <> :uniqueid';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':session_id', $session_id, PDO::PARAM_INT);
        $command->bindValue(':uniqueid', '+000000000000', PDO::PARAM_STR);
        $session_users = $command->queryAll();
        
        return $session_users;
    }
    
    public function findAllByTime($user_id, $timestamp) {
        $query = 'SELECT t1.id, t1.uniqueid, t1.name, t1.description, t1.photo, t1.enabled '
                . 'FROM users t1 '
                . 'LEFT JOIN session_users t2 ON t2.user_id = t1.id '
                . 'WHERE t2.time >= :timestamp AND EXISTS '
                . '(SELECT t3.user_id '
                . 'FROM session_users AS t3 '
                . 'WHERE t2.session_id = t3.session_id AND t3.user_id = :user_id AND t3.enabled = 1 AND t3.state > 0)';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $users = $command->queryAll();
        
        return $users;
    }
    
    public function createStaticUser() {
        $query = 'INSERT INTO users(uniqueid, name, enabled) '
                . 'VALUES(:uniqueid, \'static\', 1)';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':uniqueid', Users::$staticUniqueId, PDO::PARAM_STR);
        $command->execute();
    }
    
    public function toObject() {
        $result = array(
            'id'=>$this->id, 
            'uniqueid'=> $this->uniqueid,
            'name' =>  $this->name, 
            'description' => $this->description, 
            'photo' => $this->photo, 
            'enabled' => $this->enabled);
        
        return $result;
    }
}