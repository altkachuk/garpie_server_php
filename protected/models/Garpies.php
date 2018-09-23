<?php

/**
 * This is the model class for table "garpies".
 *
 * The followings are the available columns in table 'garpies':
 * @property integer $id
 * @property string $name
 * @property string $icon
 * @property string $texture
 * @property string $texture_static
 * @property string $predefined
 *
 * The followings are the available model relations:
 * @property SessionUsers[] $sessionUsers
 */
class Garpies extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Garpies the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'garpies';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('id', 'integerOnly'=>true),
            array('predefined', 'length', 'max'=>1),
			array('name, icon, texture, texture_static', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, icon, texture, texture_static, predefined', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'sessionUsers' => array(self::HAS_MANY, 'SessionUsers', 'garpie_id'),
		);
	}
    
    public function findById($id) {
        $query = 'select id, name, icon, texture, texture_static, predefined from garpies where id = :id';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':id', $id, PDO::PARAM_INT);
        $garpie = $command->query();
        $garpie = $garpie->read();
        
        if ($garpie != null && $garpie['predefined']==1) {
            $garpie['icon'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpie['icon'];
            $garpie['texture'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpie['texture'];
            $garpie['texture_static'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpie['texture_static'];
        }
        
        return $garpie;
    }
    
    public function findAllPredefined() {
        $query = 'select id, name, icon, texture, texture_static, predefined from garpies where predefined = 1';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $garpies = $command->queryAll();
        
        for ($i = 0; $i < count($garpies); $i++) {
            if ($garpies[$i]['predefined']==1) {
                $garpies[$i]['icon'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['icon'];
                $garpies[$i]['texture'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['texture'];
                $garpies[$i]['texture_static'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['texture_static'];
            }
        }
        
        return $garpies;
    }
    
    public function findAllBySession($session_id) {
        $query = 'SELECT distinct t1.id, t1.name, t1.icon, t1.texture, t1.texture_static, t1.predefined '
                . 'FROM garpies t1 '
                . 'LEFT JOIN session_users t2 ON t2.garpie_id = t1.id '
                . 'WHERE t2.session_id = :session_id AND t2.enabled = 1 AND t2.state <> 0';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':session_id', $session_id, PDO::PARAM_INT);
        $garpies = $command->queryAll();
        
        for ($i = 0; $i < count($garpies); $i++) {
            if ($garpies[$i]['predefined']==1) {
                $garpies[$i]['icon'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['icon'];
                $garpies[$i]['texture'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['texture'];
                $garpies[$i]['texture_static'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['texture_static'];
            }
        }
        
        return $garpies;
    }
    
    public function findAllByTime($user_id, $timestamp) {
        $query = 'SELECT distinct t1.id, t1.name, t1.icon, t1.texture, t1.texture_static, t1.predefined '
                . 'FROM garpies t1 '
                . 'LEFT JOIN session_users t2 ON t2.garpie_id = t1.id '
                . 'RIGHT JOIN users t4 ON t4.id = t2.user_id '
                . 'WHERE t2.time >= :timestamp AND EXISTS '
                . '(SELECT t3.user_id '
                . 'FROM session_users AS t3 '
                . 'WHERE t2.session_id = t3.session_id AND t3.user_id = :user_id AND ((t3.enabled = 1 AND t3.state > 0)))';
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);
        $command->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $garpies = $command->queryAll();
        
        for ($i = 0; $i < count($garpies); $i++) {
            if ($garpies[$i]['predefined']==1) {
                $garpies[$i]['icon'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['icon'];
                $garpies[$i]['texture'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['texture'];
                $garpies[$i]['texture_static'] = Yii::app()->request->getBaseUrl(true) . '/' . $garpies[$i]['texture_static'];
            }
        }
        
        return $garpies;
    }
    
    public function toObject() {        
        $result = array('id'=>$this->id, 'name'=>$this->name, 'icon'=>$this->icon, 'texture'=> $this->texture, 'texture'=> $this->texture_static);
        return $result;
    }
}