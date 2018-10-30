<?php

/**
 * This is the model class for table "user_position".
 *
 * The followings are the available columns in table 'user_position':
 * @property integer $id
 * @property integer $session_user_id
 * @property double $latitude
 * @property double $longitude
 * @property double $altitude
 * @property double $time
 *
 * The followings are the available model relations:
 * @property SessionUsers $session_user
 */
class SessionUserLocations extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserPosition the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'session_user_locations';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('session_user_id', 'numerical', 'integerOnly'=>true),
			array('latitude, longitude, altitude, time', 'numerical'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, session_user_id, latitude, longitude, altitude, time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'session_user' => array(self::BELONGS_TO, 'SessionUsers', 'session_user_id'),
		);
	}
    
    public function createSessionUserLocation($session_user_id, $latitude, $longitude, $altitude) {
        $query = 'INSERT INTO session_user_locations(session_user_id, latitude, longitude, altitude) '
                . 'VALUES(:session_user_id, :latitude, :longitude, :altitude)';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command->bindParam(':session_user_id', $session_user_id, PDO::PARAM_INT);
        $command->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $command->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $command->bindParam(':altitude', $altitude, PDO::PARAM_STR);
        $command->execute();
    }
    
    public function toObject()
    {
        $result = array(
            'id' => $this->id,
            'session_user_id' => $this->session_user_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'altitude' => $this->altitude);
        return $result;
    }
}