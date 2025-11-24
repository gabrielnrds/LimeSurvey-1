<?php

/**
 * This is the model class for table "limesurvey.project".
 *
 * The followings are the available columns in table 'limesurvey.project':
 * @property integer $id
 * @property string $title
 * @property string $coordinator
 * @property string $coordinator_email
 * @property string $completion_estimate
 * @property string $current_hours
 * @property string $status
 */
class Project extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'limesurvey.project';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, coordinator, coordinator_email', 'required'),
			array('title, coordinator, coordinator_email, status', 'length', 'max'=>255),
			array('completion_estimate, current_hours', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title, coordinator, coordinator_email, completion_estimate, current_hours, status', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Title',
			'coordinator' => 'Coordinator',
			'coordinator_email' => 'Coordinator Email',
			'completion_estimate' => 'Completion Estimate',
			'current_hours' => 'Current Hours',
			'status' => 'Status',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('coordinator',$this->coordinator,true);
		$criteria->compare('coordinator_email',$this->coordinator_email,true);
		$criteria->compare('completion_estimate',$this->completion_estimate,true);
		$criteria->compare('current_hours',$this->current_hours,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Project the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
