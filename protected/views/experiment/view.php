<?php
$this->homelink=CHtml::link(CHtml::encode($model->classRoom->course->title),array('/course/view','id'=>$model->classRoom->course_id,'class_room_id'=>$model->classRoom->id), array('class'=>'home'));
$this->breadcrumbs=array(
	CHtml::encode($model->title)."(".$this->classRoom->begin.")"=>array('classRoom/view','id'=>$model->class_room_id),
	Yii::t("t",'Experiments')=>array('classRoom/experiments','id'=>$model->class_room_id),
	CHtml::encode($model->title),
);

$cansubmit=false;
$report=null;
if(UUserIdentity::isStudent())
{
	$report=ExperimentReport::model()->find(array(
		'condition'=>'experiment_id=:experimentID and user_id='.Yii::app()->user->id,
		'params'=>array(':experimentID'=>$model->id),
	));
	if( $report==null)$cansubmit=!$model->isTimeOut();
	else {
		$cansubmit=($report->status==ExperimentReport::STATUS_ALLOW_EDIT ) ||($report->status==ExperimentReport::STATUS_ALLOW_LATE_EDIT);
	}

}
$this->toolbar= array(
	array(
		'label'=>Yii::t('t','Add a programming problem'),
		'icon-position'=>'left',
		'icon'=>'circle-plus', // This a CSS class starting with ".ui-icon-"
		'url'=>array('exerciseProblem/addProblemToExperiment','id'=>$model->id),
		'visible'=>UUserIdentity::isTeacher()||UUserIdentity::isAdmin(),
		'linkOptions'=>array('class'=>'create')
	),
	array(
		'label'=>Yii::t('course','Update experiment'),
		'icon-position'=>'left',
		'visible'=>UUserIdentity::isTeacher()||UUserIdentity::isAdmin(),//!Yii::app()->user->isGuest && $this->canAccess(array('model'=>$model),'update'),
		'url'=>array('update', 'id'=>$model->id),
	),
	array(
		'label'=>Yii::t('course','List reports'),
		'icon-position'=>'left',
		'visible'=>UUserIdentity::isTeacher()||UUserIdentity::isAdmin(),//!Yii::app()->user->isGuest && $this->canAccess(array('model'=>$model),'update'),
		'url'=>array('reports', 'id'=>$model->id),
	),
	array(
		'label'=>Yii::t('course',($report==null)?'Write a report':"Update your report"),
		'icon-position'=>'left',
		'visible'=>$cansubmit,//!Yii::app()->user->isGuest && $this->canAccess(array('model'=>$model),'update'),
		'url'=>($report==null)?array('/experimentReport/write', 'id'=>$model->id):array('/experimentReport/update', 'id'=>$report->id),
	),
	array(
		'label'=>'View my report',
		'icon-position'=>'left',
		'visible'=>($report!=null),//!Yii::app()->user->isGuest && $this->canAccess(array('model'=>$model),'update'),
		'url'=>array('/experimentReport/view', 'id'=>($report==null)?1:$report->id),
	),


);
$gMessages=(UClassRoomLookup::getEXPERIMENT_TYPE_MESSAGES());
$this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'sequence',
		'title',
		array(
			'name'=>'class_room',
			'type'=>'raw',
			'value'=>CHtml::link(CHtml::encode($model->classRoom->title),
				array('classRoom/view','id'=>$model->class_room_id)),
		),
		array(
			'name'=>'experiment_type_id',
			'type'=>'raw',
			'value'=>$gMessages[$model->experiment_type_id],
		),
		array(
			'name'=>'isClosed',
			'type'=>'raw',
			'value'=>$model->isClosed?"Yes":"No",
		),
		array(
			'name'=>'due_time',
			'type'=>'raw',
			'value'=>date_format(date_create($model->due_time),'Y年m月d日  H:i'),
		),
		array(
			'label'=>'Begin~End',
			'type'=>'raw',
			'value'=>$model->begin.'~'.$model->end,
		),
		array(
			'name'=>'aim',
			'type'=>'raw',
			'value'=>"<div>".$model->aim."</div>",
		),
		array(
			'name'=>'description',
			'type'=>'raw',
			'value'=>"<div>".$model->description."</div>",
		),
	),
));
if($model->exercise!==null){
	echo "<h3>".Yii::t('t',"Programming problems")."</h3>";
	$criteria = new CDbCriteria;
	//$criteria->select ("sequence","problem.title");
	$criteria->compare('exercise_id',$model->exercise_id);
	$criteria->order='sequence ASC';
	$dataProvider=new CActiveDataProvider('ExerciseProblem',array(
		'criteria' => $criteria));
	$arraycolums=array();
	if(UUserIdentity::isTeacher()||UUserIdentity::isAdmin())
	{
		$arraycolums[]=array(
				'class'=>'CButtonColumn',
				'template'=> '{view}{update}{delete}',
				'viewButtonUrl' => 'array("exerciseProblem/view",
				"id"=>$data->id)',
			'buttons'=>array(
				'update' =>array('url'=>'Yii::app()->createUrl("exerciseProblem/update",array("id"=>$data->id))',
					'options'=>array('class'=>'update'),
					),
			),
				'deleteButtonUrl' => 'array("exerciseProblem/delete",
				"id"=>$data->id)',				
			);
	
	}
	$arraycolums[]=array(
				'name' => 'sequence',
				'header' => Yii::t('t','Sequence'),
				'type' => 'raw',
				'value' => 'CHtml::encode($data["sequence"])'
			);
	$arraycolums[]=array(
				'name' => 'title',
				'header' => Yii::t('t','Problem title'),
				'type' => 'raw',
				'value' => ' CHtml::link(nl2br(CHtml::encode($data->title)),$data->getUrl(null))',
			);
	$arraycolums[]=array(
				'name' => 'memo',
				'header' => Yii::t('t','Memo'),
			);
	$this->widget('zii.widgets.grid.CGridView', array(
		//here might be a bug
		'afterAjaxUpdate'=>'js:function(id,data){$("a.update").formDialog({"onSuccess":function(data, e){alert(data.message);window.location.reload();},"close":function(){if($.clearScripts)$.clearScripts();$(this).detach()},"title":"'.Yii::t("t","Update a programming problem").'","minWidth":800,"height":710,"modal":true,"id":"yw1"});}',
		'dataProvider' => $dataProvider,
		'columns' => $arraycolums,
	));
}

$this->widget('comments.widgets.ECommentsListWidget', array(
	'model' => $model,
));

$this->widget('application.extensions.formDialog.FormDialog', array('link'=>'a.create',
		'options'=>array('onSuccess'=>'js:function(data, e){alert(data.message);window.location.reload();}',
				'dialogClass'=>'rbam-dialog',
				'close'=>'js:function(){if($.clearScripts)$.clearScripts();$(this).detach()}',
				'title'=>Yii::t('t', 'Add a programming problem'),
				'minWidth'=>800,
				'height'=>710,
				'modal'=>true,
		)
));

$this->widget('application.extensions.formDialog.FormDialog', array('link'=>'a.update',
		'options'=>array('onSuccess'=>'js:function(data, e){alert(data.message);window.location.reload();}',
				'dialogClass'=>'rbam-dialog',
				'close'=>'js:function(){if($.clearScripts)$.clearScripts();$(this).detach()}',
				'title'=>Yii::t('t', 'Update a programming problem'),
				'minWidth'=>800,
				'height'=>710,
				'modal'=>true,
		)
));
 $this->widget('ext.EAjaxUpload.EAjaxUploadBasic',
		array(
				'id'=>'uploadFile',
				'config'=>array(
						'button'=>'js:jQuery("#fileUploader")[0]',
						'action'=>UCHtml::url('upload/create/type/chapter'.(isset($model->root)?('/book/'.(int)($model->root)):'')),
						'allowedExtensions'=>array("jpg","jpeg","png","gif","txt","rar","zip","ppt","chm","pdf","doc","7z"),//array("jpg","jpeg","gif","exe","mov" and etc...
						'sizeLimit'=>10*1024*1024,// maximum file size in bytes
						'minSizeLimit'=>10,// minimum file size in bytes
						'onComplete'=>'js:function(id, fileName, responseJSON){ if (typeof(responseJSON.success)!="undefined" && responseJSON.success){insertFile(fileName,responseJSON);}}',
						//'messages'=>array(
						//                  'typeError'=>"{file} has invalid extension. Only {extensions} are allowed.",
						//                  'sizeError'=>"{file} is too large, maximum file size is {sizeLimit}.",
						//                  'minSizeError'=>"{file} is too small, minimum file size is {minSizeLimit}.",
						//                  'emptyError'=>"{file} is empty, please select files again without it.",
						//                  'onLeave'=>"The files are being uploaded, if you leave now the upload will be cancelled."
						//                 ),
						//'showMessage'=>"js:function(message){ alert(message); }"
				)
)); 
if($model->classRoom->hasMathFormula)$this->widget('application.components.widgets.MathJax',array());
