<?php

/**
 * This is the model class for table "messages".
 *
 * The followings are the available columns in table 'messages':
 * @property integer $id
 * @property integer $user_id
 * @property integer $session_id
 * @property string $value
 * @property double $time
 *
 * The followings are the available model relations:
 * @property Users $user
 * @property Sessions $session
 */
class Messages extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Messages the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'messages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, user_id, session_id', 'numerical', 'integerOnly'=>true),
            array('time', 'numerical'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, session_id, time, value', 'safe', 'on'=>'search'),
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
            'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
			'session' => array(self::BELONGS_TO, 'Sessions', 'session_id'),
		);
	}
    
    public function findAllBySession($session_id) {
        $query = 'SELECT t1.id, t1.user_id, t1.session_id, t1.value, t1.time, t3.uniqueid '
                . 'FROM messages t1 '
                . 'RIGHT JOIN users t3 ON t3.id = t1.user_id '
                . 'WHERE t1.session_id >= :session_id';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':session_id', $session_id, PDO::PARAM_INT);
        $messages = $command->queryAll();
        
        return $messages;
    }
    
    public function findAllByTime($user_id, $timestamp) {
        $query = 'SELECT t1.id, t1.user_id, t1.session_id, t1.value, t1.time, t3.uniqueid '
                . 'FROM messages t1 '
                . 'RIGHT JOIN users t3 ON t3.id = t1.user_id '
                . 'WHERE t1.time >= :timestamp AND EXISTS '
                . '(SELECT t2.user_id '
                . 'FROM session_users AS t2 '
                . 'WHERE t1.session_id = t2.session_id AND t2.user_id = :user_id AND t2.enabled = 1 AND t2.state > 0)';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $command->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);
        $messages = $command->queryAll();
        
        return $messages;
    }
    
    public function toObject() {
        $result = array(
            'id' => $this->id,
            'uniqueid' => $this->user->uniqueid,
            'session_id' => $this->session_id,
            'value' => $this->value,
            'time' => $this->time
        );
        return $result;
    }
}