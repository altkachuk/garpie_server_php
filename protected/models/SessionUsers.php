<?php

/**
 * This is the model class for table "session_users".
 *
 * The followings are the available columns in table 'session_users':
 * @property integer $id
 * @property integer $user_id
 * @property integer $session_id
 * @property integer $garpie_id
 * @property string $is_master
 * @property integer $state
 * @property string $enabled
 * @property double $time
 * 
 *
 * The followings are the available model relations:
 * @property Users $user
 * @property Sessions $session
 * @property Garpies $garpie
 * @property SessionUserLocations[] $sessionUserLocations
 */
class SessionUsers extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SessionUsers the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'session_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('session_id, user_id, garpie_id, state', 'numerical', 'integerOnly'=>true),
            array('time', 'numerical'),
            array('is_master, enabled', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, session_id, garpie_id, is_master, state, enabled, time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
			'session' => array(self::BELONGS_TO, 'Sessions', 'session_id'),
			'garpie' => array(self::BELONGS_TO, 'Garpies', 'garpie_id'),
            'sessionUserLocations' => array(self::HAS_MANY, 'SessionUserLocations', 'session_user_id'),
		);
	}
    
    public function findBySession($user_id, $session_id) {
        $query = 'SELECT t1.id, t1.user_id, t1.session_id, t1.garpie_id, t1.is_master, t1.state, t1.enabled, t2.uniqueid '
                . 'FROM session_users t1 '
                . 'LEFT JOIN users t2 ON t2.id = t1.user_id '
                . 'WHERE t1.user_id  = :user_id AND t1.session_id = :session_id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $command->bindValue(':session_id', $session_id, PDO::PARAM_INT);
        $sessionUser = $command->query();
        $sessionUser = $sessionUser->read();
        
        return $sessionUser;
    }
    
    public function findAllBySession($session_id) {
        $query = 'SELECT t1.id, t1.user_id, t1.session_id, t1.garpie_id, t1.is_master, t1.state, t1.enabled, t2.latitude, t2.longitude, t2.altitude, t4.uniqueid '
                . 'FROM session_users t1 '
                . 'LEFT JOIN session_user_locations t2 ON t2.id = (SELECT MAX(id) FROM session_user_locations WHERE session_user_id = t1.id) '
                . 'RIGHT JOIN users t4 ON t4.id = t1.user_id '
                . 'WHERE t1.session_id = :session_id';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':session_id', $session_id, PDO::PARAM_INT);
        $session_users = $command->queryAll();
        
        return $session_users;
    }
    
    public function findAllByTime($user_id, $timestamp) {
        $query = 'SELECT t1.id, t1.user_id, t1.session_id, t1.garpie_id, t1.is_master, t1.state, t1.enabled, t1.time, t2.latitude, t2.longitude, t2.altitude, t4.uniqueid '
                . 'FROM session_users t1 '
                . 'LEFT JOIN session_user_locations t2 ON t2.id = (SELECT MAX(id) FROM session_user_locations WHERE session_user_id = t1.id) '
                . 'RIGHT JOIN users t4 ON t4.id = t1.user_id '
                . 'WHERE t1.time >= :timestamp AND EXISTS '
                . '(SELECT t3.user_id '
                . 'FROM session_users AS t3 '
                . 'WHERE t1.session_id = t3.session_id AND t3.user_id = :user_id AND ((t3.enabled = 1 AND t3.state > 0) OR (t3.enabled = 0 AND t3.time >= :timestamp)))';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $session_users = $command->queryAll();
        
        return $session_users;
    }
    
    public function createSessionUser($user_id, $session_id, $timestamp, $is_master = 0, $state = 0, $garpie_id = null) {
        if ($garpie_id == null) {
            $query = 'INSERT INTO session_users(user_id, session_id, is_master, state, time) '
                    . 'VALUES(:user_id, :session_id, :is_master, :state, :time)';
        } else {
            $query = 'INSERT INTO session_users(user_id, session_id, is_master, state, garpie_id, time) '
                    . 'VALUES(:user_id, :session_id, :is_master, :state, :garpie_id, :time)';
        }
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $command->bindParam(':session_id', $session_id, PDO::PARAM_INT);
        $command->bindParam(':is_master', $is_master, PDO::PARAM_STR);
        $command->bindParam(':state', $state, PDO::PARAM_INT);
        $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
        if ($garpie_id != null)
            $command->bindParam(':garpie_id', $garpie_id, PDO::PARAM_INT);
        $command->execute();
    }

    public function udateTime($id, $timestamp) {
        $query = 'UPDATE session_users '
                . 'SET time = :time '
                . 'WHERE id = :id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
        $command->bindParam(':id', $id, PDO::PARAM_INT);
        $command->execute();
    }
    
    public function readd($id, $timestamp) {
        $query = 'UPDATE session_users '
                . 'SET time = :time, state = 0 , enabled = 1 '
                . 'WHERE id = :id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
        $command->bindParam(':id', $id, PDO::PARAM_INT);
        $command->execute();
    }
    
    public function accept($user_id, $session_id, $garpie_id, $timestamp) {
        $query = 'UPDATE session_users '
                . 'SET garpie_id = :garpie_id, state = 1, time = :time '
                . 'WHERE user_id = :user_id AND session_id = :session_id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $command->bindParam(':session_id', $session_id, PDO::PARAM_INT);
        $command->bindParam(':garpie_id', $garpie_id, PDO::PARAM_INT);
        $command->bindParam(':time', $timestamp, PDO::PARAM_STR);
        
        $command->execute();
    }

    public function toObject() {
        $result = array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->user->toObject(),
            'session_id' => $this->session_id,
            'garpie_id' => $this->garpie_id,
            'is_master' => $this->is_master,
            'state' => $this->state,
            'enabled' => $this->enabled,
            'uniqueid' => $this->user->uniqueid
        );
        
        return $result;
    }
}