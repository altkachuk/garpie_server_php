<?php

/**
 * This is the model class for table "user_tokens".
 *
 * The followings are the available columns in table 'user_tokens':
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 *
 * The followings are the available model relations:
 * @property Users $user
 */
class UserTokens extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserTokens the static model class
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
		return 'user_tokens';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('token', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, token', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'token' => 'Token',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('token',$this->token,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    
    public function findAllByUsers($users) {
        $query = 'SELECT token '
                . 'FROM user_tokens ';
        
        $i = 0;
        foreach ($users as $user) {
            if ($i == 0) {
                $query .= 'WHERE user_id = :user_id' . $i;
            } else {
                $query .= ' OR user_id = :user_id' . $i;
            }
            $i++;
        }
        
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $i = 0;
        foreach ($users as $user) {
            $command->bindValue(':user_id' . $i, $user['id'], PDO::PARAM_INT);
            $i++;
        }
        $result = $command->queryAll();
        
        $tokens = array();
        foreach ($result as $item) {
            $tokens[] = $item['token'];
        }
        
        
        return $tokens;
    }
}