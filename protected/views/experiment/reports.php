<?php
$this->homelink=CHtml::link(CHtml::encode($model->classRoom->course->title),array('/course/view','id'=>$model->classRoom->course_id,'class_room_id'=>$model->classRoom->id), array('class'=>'home'));
$this->breadcrumbs=array(
	CHtml::encode($model->title)."(".$this->classRoom->begin.")"=>array('classRoom/view','id'=>$model->class_room_id),
	Yii::t("t",'Experiments')=>array('classRoom/experiments','id'=>$model->class_room_id),
	CHtml::encode($model->title)=>array('view','id'=>$model->id),
	Yii::t("t","Experiment reports"),
);
$this->toolbar= array(
	array(
		'label'=>$model->title,
		'icon-position'=>'left',
		'visible'=>!Yii::app()->user->isGuest,
		'icon'=>'document',
		'url'=>array('/experiment/view','id'=>$model->id),
	),
	array(
		'label'=>Yii::t('course','Other experiments'),
		'icon-position'=>'left',
		'visible'=>!Yii::app()->user->isGuest,
		'icon'=>'document',
		'url'=>array('/classRoom/experiments','id'=>$model->class_room_id),
	)		
);
$timeout=$model->afterDeadline();
$this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'groupUser-grid',
		'dataProvider'=>$dataProvider,
		'ajaxUpdate'=>false,
		'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>4,),
		'template'=>'{summary}{pager}{items}{pager}',
		'columns'=>array(
				array(
						'header'=>Yii::t('course','Student number'),
						'name'=>'schoolInfo.identitynumber',
						'type'=>'raw',
						'value'=>'CHtml::encode($data->schoolInfo->identitynumber)',
				),
				array(
						'header'=>Yii::t('course','Name'),
						'name'=>'name',
						'type'=>'raw',
						'value'=>'CHtml::link(CHtml::encode($data->info->lastname.$data->info->firstname),array("schoolInfo/view","id"=>$data->schoolInfo->user_id))',
				),
				array(
						'header'=>Yii::t('course','Operation'),
						'type'=>'raw',
						'value'=>'CHtml::link(Yii::t("main","send a message"),array("message/compose","id"=>$data->schoolInfo->user_id))',
				),
				
				/*
				array(
						'header'=>'Login name',
						'name'=>'username',
						'type'=>'raw',
						'value'=>'CHtml::link(CHtml::encode($data->username),array("user/user/view","id"=>$data->id),  array("target"=>"_blank"))',
				),
				*/
				array(
						'header'=>Yii::t('course','Score'),
						'name'=>'experimentReport.score',
						'type'=>'raw',
						'value'=>'($data->experimentReport!=null)? CHtml::link( ($data->experimentReport->score>0)?($data->experimentReport->score): ($data->experimentReport->canScore()?Yii::t("course","S"):Yii::t("course","V")),array("experimentReport/view","id"=>$data->experimentReport->id),  array("target"=>"_blank")) :'.
						'(('. $timeout.'==1)?CHtml::ajaxLink(Yii::t("course","R"),array("classRoom/resubmitReport","experiment_id"=>'.$model->id.',"user_id"=>$data->id), array("success" => "js:function(data){ window.location.reload(); }"), array("confirm"=>Yii::t("course","Do you allow her/him to resubmit a report?"))) :"")',
						//'value'=>'$data->experimentReport!=null && $data->experimentReport->score>0?$data->experimentReport->score:""',
				),
				array(
						'header'=>Yii::t('course','Updated time'),
						'name'=>'experimentReport.updated',
						'type'=>'raw',
						'value'=>'($data->experimentReport!=null)? date("Y-m-d h:m",$data->experimentReport->updated):""',
				),				
						/*
				array(
						'header'=>Yii::t('course','Operation'),
						'name'=>'experimentReport.score',
						'type'=>'raw',
						'class'=>'CButtonColumn',
						'template' => '{view} ',
						'viewButtonUrl'=>'array("/experimentReport/view/".( ($data->experimentReport!=null)?$data->experimentReport->id:""))',
						'buttons' => array(
								'view'=>array(
										'visible'=>'($data->experimentReport!=null)',
										'options'=>array('target'=>'_blank'),
								)
						)
						 
				)
						*/
		),
));

echo UCHtml::cssFile('pager.css');
/*
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'groupUser-grid',
	'dataProvider'=>$dataProvider,
	'ajaxUpdate'=>false,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>4,),
	'template'=>'{summary}{pager}{items}{pager}',
	'columns'=>array(
		array(
			'name'=>'Name',
			'type'=>'raw',
			'value'=>'CHtml::encode($data->user->info->lastname.$data->user->info->firstname)',
		),
		array(
			'name'=>'Student number',
			'type'=>'raw',
			'value'=>'CHtml::encode($data->user->schoolInfo->identitynumber)',
		),
		array(
			'header'=>'Login name',
			'type'=>'raw',
			'value'=>'CHtml::link(CHtml::encode($data->user->username),array("user/user/view","id"=>$data->user_id),  array("target"=>"_blank"))',
		),	
		array(
			'header'=>'Score',
			'type'=>'raw',
			'value'=>'$data->score!=0?$data->score:""',
		),
		array(
            'class'=>'CButtonColumn',
            'template' => '{view} ',
       		'viewButtonUrl'=>'array("/experimentReport/view/".$data->data)',
       		'buttons' => array(
       			'view'=>array(
       				'visible'=>'($data->data!=0)',
       				'options'=>array('target'=>'_blank'),
       			)
       		)
       
		)
	),
));
*/
?>

