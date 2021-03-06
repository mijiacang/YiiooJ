<?php

class MultipleChoiceController extends CMController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $contentMenu=1;
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
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
				'actions'=>array('view','list'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','createFill','updateFill','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index','admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$model=$this->loadModel($id);
		if($model->chapter)
			$this->course=$model->chapter->book->course;
		$choiceOptionManager=new ChoiceOptionManager();
		$choiceOptionManager->load($model);
		
		$this->render('view',array(
			'model'=>$model,
			'choiceOptionManager'=>$choiceOptionManager,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreateFill($id=null)
	{
		$model=new MultipleChoice;
		$choiceOptionManager=new ChoiceOptionManager();
	
		if( ((int)$id)==0 && $this->getCourse())$id=$this->getCourse()->chapter_id;
	
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
	
		$model->question_type= ULookup::EXAMINATION_PROBLEM_TYPE_QUESTION;
	
		if(isset($_POST['MultipleChoice']))
		{
			$model->attributes=$_POST['MultipleChoice'];
			$model->answer="-1";
			$choiceOptionManager->manage(isset($_POST['ChoiceOption'])?$_POST['ChoiceOption']:array());
			if (!isset($_POST['noValidate']))
			{
				$valid=$model->validate();
				$valid=$choiceOptionManager->validate($model) && $valid;
	
				if($valid)
				{
					if($model->save())
					{
						$choiceOptionManager->save($model);
						$this->redirect(array('view','id'=>$model->id));
					}
				}
			}
		}
	
		$treeArray=array();
		if($id!=null)
		{
			$nodeRoot=Chapter::model()->findByPk($id);
			$nodeRoot=$nodeRoot->book;
	
			if($nodeRoot===null)
				throw new CHttpException(404,'The requested page does not exist.');
			$treeArray[$nodeRoot->id]=str_repeat('&nbsp;',2*($nodeRoot->level-1)).CHtml::encode($nodeRoot->name);
			$tree=$nodeRoot->descendants()->findAll();
			if(!empty($tree))
			{
				foreach ($tree as $node)
				{
					//var_dump($node);
					$treeArray[$node->id]=str_repeat('&nbsp;',2*($node->level-1)).CHtml::encode($node->name);
				}
			}
		}
		$this->render('create',array(
				'model'=>$model,
				'choiceOptionManager'=>$choiceOptionManager,
				'chapters'=>$treeArray,
				'type'=>'Fill'
		));
	}	
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($id=null)
	{
		$model=new MultipleChoice;
		$model->question_type=ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_SINGLE;
		$choiceOptionManager=new ChoiceOptionManager();

		if( ((int)$id)==0 && $this->getCourse())$id=$this->getCourse()->chapter_id;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		
		if(isset($_POST['MultipleChoice']))
		{
			$model->attributes=$_POST['MultipleChoice'];
			$choiceOptionManager->manage(isset($_POST['ChoiceOption'])?$_POST['ChoiceOption']:array());
			if (isset($_POST['OldValue']))
			{
				if($_POST['OldValue']==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_SINGLE)
				{
					foreach($choiceOptionManager->items as $id1=>$choiceOption){
						$choiceOption->isAnswer=($id1==$model->answer)?1:0;
					}
				}
				else
				{
					$model->answer="";
					foreach($choiceOptionManager->items as $id1=>$choiceOption){
						if($choiceOption->isAnswer)$model->answer=$id1;
					}
				}
			}
			if (!isset($_POST['noValidate']))
			{
				if($model->question_type==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_MULTIPLE)
				{
					$answer=array();
					foreach($choiceOptionManager->items as $id1=>$choiceOption){
						if($choiceOption->isAnswer)$answer[]=$id1;
					}
					sort($answer);
					$model->answer=join($answer,",");
				}
				$valid=$model->validate();
				$valid=$choiceOptionManager->validate($model) && $valid;
		
				if($valid)
				{
					if($model->save())
					{
						if($model->question_type==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_MULTIPLE
							||$model->question_type==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_SINGLE
							){
							$choiceOptionManager->save($model);
							$answer_faker= preg_split('/,/',$model->answer);
							$answer=array();
							foreach($choiceOptionManager->items as $id1=>$choiceOption){
								if(in_array($id1,$answer_faker))$answer[]=$choiceOption->id;
							}
							sort($answer);
							
							$model->answer = join($answer,",");
							$model->save();
							$this->redirect(array('view','id'=>$model->id));
						}
					}
				}
			}
		}
		
		$treeArray=array();
		if($id!=null)
		{
			$nodeRoot=Chapter::model()->findByPk($id);
			$nodeRoot=$nodeRoot->book;
				
			if($nodeRoot===null)
				throw new CHttpException(404,'The requested page does not exist.');
			$treeArray[$nodeRoot->id]=str_repeat('&nbsp;',2*($nodeRoot->level-1)).CHtml::encode($nodeRoot->name);
			$tree=$nodeRoot->descendants()->findAll();
			if(!empty($tree))
			{
				foreach ($tree as $node)
				{
					//var_dump($node);
					$treeArray[$node->id]=str_repeat('&nbsp;',2*($node->level-1)).CHtml::encode($node->name);
				}
			}
		}		
		$this->render('create',array(
				'model'=>$model,
				'choiceOptionManager'=>$choiceOptionManager,
				'chapters'=>$treeArray,
			'type'=>'choice'
				
		));		
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdateFill($id)
	{
		$model=$this->loadModel($id);
		$choiceOptionManager=new ChoiceOptionManager();
		$choiceOptionManager->load($model);
	
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
	
		if(isset($_POST['MultipleChoice']))
		{
			$model->attributes=$_POST['MultipleChoice'];
			$model->answer="-1";
			$choiceOptionManager->manage(isset($_POST['ChoiceOption'])?$_POST['ChoiceOption']:array());
			if (!isset($_POST['noValidate']))
			{
				$valid=$model->validate();
				$valid=$choiceOptionManager->validate($model) && $valid;
	
				if($valid)
				{
					if($model->save())
					{
							$choiceOptionManager->save($model);
							$this->redirect(array('view','id'=>$model->id));
					}
				}
			}
		}
	
	
		$treeArray=array();
		if($model->chapter!=null)
		{
			$nodeRoot=$model->chapter->book;
			if($nodeRoot===null)
				throw new CHttpException(404,'The requested page does not exist.');
			$treeArray[$nodeRoot->id]=str_repeat('&nbsp;',2*($nodeRoot->level-1)).CHtml::encode($nodeRoot->name);
			$tree=$nodeRoot->descendants()->findAll();
			if(!empty($tree))
			{
				foreach ($tree as $node)
				{
					//var_dump($node);
					$treeArray[$node->id]=str_repeat('&nbsp;',2*($node->level-1)).CHtml::encode($node->name);
				}
			}
		}
	
		$this->render('update',array(
				'model'=>$model,
				'choiceOptionManager'=>$choiceOptionManager,
				'chapters'=>$treeArray,
				'type'=>'Fill'
		));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
		$choiceOptionManager=new ChoiceOptionManager();
		$choiceOptionManager->load($model);
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['MultipleChoice']))
		{
			$model->attributes=$_POST['MultipleChoice'];
			$choiceOptionManager->manage(isset($_POST['ChoiceOption'])?$_POST['ChoiceOption']:array());
			if (isset($_POST['OldValue']))
			{
				if($_POST['OldValue']==0)
				{
					foreach($choiceOptionManager->items as $id1=>$choiceOption){
						$choiceOption->isAnswer=($id1==$model->answer)?1:0;
					}
				}
				else
				{
					$model->answer="";
					foreach($choiceOptionManager->items as $id1=>$choiceOption){
						if($choiceOption->isAnswer)$model->answer=$id1;
					}
				}
			}
			if (!isset($_POST['noValidate']))
			{
				if($model->question_type==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_MULTIPLE)
				{
					$answer=array();
					foreach($choiceOptionManager->items as $id1=>$choiceOption){
						if($choiceOption->isAnswer)$answer[]=$id1;
					}
					sort($answer);
					$model->answer=join($answer,",");
				}
				$valid=$model->validate();
				$valid=$choiceOptionManager->validate($model) && $valid;
		
				if($valid)
				{
					if($model->save())
					{
						if($model->question_type==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_MULTIPLE
								||$model->question_type==ULookup::EXAMINATION_PROBLEM_TYPE_MULTIPLE_CHOICE_SINGLE
						){
							$choiceOptionManager->save($model);
							$answer_faker=preg_split('/,/',$model->answer);
							$answer=array();
							foreach($choiceOptionManager->items as $id1=>$choiceOption){
								if(in_array($id1,$answer_faker))$answer[]=$choiceOption->id;
							}
							
							sort($answer);
							$model->answer = join($answer,",");
							$model->save();
							$this->redirect(array('view','id'=>$model->id));
						}
					}
				}
			}
		}
		
		
		$treeArray=array();
		if($model->chapter!=null)
		{
			$nodeRoot=$model->chapter->book;
			if($nodeRoot===null)
				throw new CHttpException(404,'The requested page does not exist.');
			$treeArray[$nodeRoot->id]=str_repeat('&nbsp;',2*($nodeRoot->level-1)).CHtml::encode($nodeRoot->name);
			$tree=$nodeRoot->descendants()->findAll();
			if(!empty($tree))
			{
				foreach ($tree as $node)
				{
					//var_dump($node);
					$treeArray[$node->id]=str_repeat('&nbsp;',2*($node->level-1)).CHtml::encode($node->name);
				}
			}
		}
		$answer_faker=preg_split('/,/',$model->answer);
		foreach($choiceOptionManager->items as $id=>$choiceOption){
			$choiceOption->isAnswer=(in_array($id,$answer_faker))?1:0;
		}
		
		$this->render('update',array(
			'model'=>$model,
			'choiceOptionManager'=>$choiceOptionManager,
			'chapters'=>$treeArray,
			'type'=>''
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('MultipleChoice');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}
	/**
	 * Lists all models.
	 */
	public function actionList($id)
	{
		if( ((int)$id)==0 && $this->getCourse())$id=$this->getCourse()->chapter_id;
		
		$nodeRoot=Chapter::model()->findByPk($id);
		$criteria=new CDbCriteria(array(
	    ));
		if(Yii::app()->request->getQuery('type',null)!==null)
		{
			$criteria->compare("question_type", (int)Yii::app()->request->getQuery('type',null));
		}
		$criteria->with=array("chapter");
		$criteria->compare("chapter.root", $id);
		$criteria->addBetweenCondition("chapter.lft", $nodeRoot->lft,$nodeRoot->rgt);
		$criteria->order=("chapter.lft");
		$dataProvider=new EActiveDataProvider('MultipleChoice',
				array(
						'criteria'=>$criteria,
						'pagination'=>array(
								'pageSize'=>30,
						),
				)
		);		
		$this->render('index',array(
				'dataProvider'=>$dataProvider,
				'root'=>$nodeRoot,
		));
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new MultipleChoice('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['MultipleChoice']))
			$model->attributes=$_GET['MultipleChoice'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=MultipleChoice::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='multiple-choice-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
