<?php

if($exercise->type_id== Exercise::EXERCISE_TYPE_COURSE){
	$this->breadcrumbs=array(
		'Classes'=>array('/classRoom/index'),
		$exercise->experiment->classRoom->title=>array('/classRoom/'.$exercise->experiment->classRoom->id),
		$exercise->experiment->title=>array('/experiment/'.$exercise->experiment->id),
	);
	if($exercise_problem!=null)
	{
		$this->breadcrumbs[$exercise_problem->sequence.$exercise_problem->title]=array('/exerciseProblem/'.$exercise_problem->id);	
	}
	$this->breadcrumbs[]='submitions';
}
else if($exercise->type_id== Exercise::EXERCISE_TYPE_PROGRAMMING_CONTEST)
{
	$this->breadcrumbs=array(
			'Contests'=>array('/programmingContest/index'),
			$exercise->programming_contest->name=>array('/programmingContest/'.$exercise->programming_contest->id),
	);
	if(isset($exercise_problem) && $exercise_problem!=null)
	{
		$this->breadcrumbs[$exercise_problem->sequence.$exercise_problem->title]=array('/exerciseProblem/'.$exercise_problem->id);	
	}
	$this->breadcrumbs[]='submitions';
}
?>
<?php
$needRefresh=(Yii::app()->request->getQuery('refresh',null)!==null);
$this->menu=array(
	array('label'=>'Create Submition', 'url'=>array('create')),
	array('label'=>'Manage Submition', 'url'=>array('admin')),
);
Yii::import('ext.qtip.QTip');
// qtip options
$opts = array(
    'position' => array(
        'corner' => array(
            'target' => 'rightMiddle',
            'tooltip' => 'leftMiddle'
            )
        ),
    'content'=>false,
    'show' => array(
        'when' => array('event' => 'mouseover' ),
    ),
    'hide' => array(
        'when' => array('event' => 'mouseout' ),
    ),
    'style' => array(
        'color' => 'red',
        'name' => 'cream',
    	'width'=>500,
        'border' => array(
            'width' => 3,
            'radius' => 5,
        ),
    )
);
 
// apply tooltip on the jQuery selector (1 parameter)
QTip::qtipd('.mes', $opts);
?>
<?php if($needRefresh) {
	echo CHtml::script('
 $(document).ready(function() {
   var refreshId = setInterval(function() {
	   $.fn.yiiGridView.update(\'submition-grid\');
   }, 6000);
});
function resetQtip()
{
$(".mes").qtip({\'position\':{\'corner\':{\'target\':\'rightMiddle\',\'tooltip\':\'leftMiddle\'}},\'content\':false,\'show\':{\'when\':{\'event\':\'mouseover\'}},\'hide\':{\'when\':{\'event\':\'mouseout\'}},\'style\':{\'color\':\'red\',\'name\':\'cream\',\'width\':500,\'border\':{\'width\':3,\'radius\':5}}});
}
');
}
 ?>
<h1><?php echo ((!Yii::app()->user->isGuest) && Yii::app()->request->getQuery('mine',null)!==null)?'My ':'';?> Submitions <?php 	if(isset($exercise_problem) && $exercise_problem!=null) echo  ' for '.CHtml::link($exercise_problem->sequence.'.'.CHtml::encode($exercise_problem->title),array("exerciseProblem/view","id"=>$exercise_problem->id));?></h1>
<?php
		if(Yii::app()->request->isAjaxRequest )
		{
			Yii::app()->getClientScript()->registerCoreScript('yii'); 			
		}

	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'submition-grid',
	'dataProvider'=>$dataProvider,
	'ajaxUpdate'=>true,
	'afterAjaxUpdate'=>'function(){resetQtip();}',
	'template'=>$needRefresh?'{summary}{items}':'{summary}{pager}{items}{pager}',
	'columns'=>array(
		array(
			'name'=>'id',
			'type'=>'raw',
			'value'=>'CHtml::link($data->id,array("view","id"=>$data->id))',
		),
		array(
			'name'=>'Author',
			'type'=>'raw',
			'visible'=>((Yii::app()->user->isGuest) || Yii::app()->request->getQuery('mine',null)===null),
			'value'=>'$data->user?CHtml::link(CHtml::encode($data->user->fullName),array("user/user/view","id"=>$data->user_id)):""',
		),
		array(
				'header'=>'Studnet ID',
				'type'=>'raw',
				'visible'=>((Yii::app()->user->isGuest) || Yii::app()->request->getQuery('mine',null)===null),
				'value'=>'$data->user->schoolInfo?CHtml::link(CHtml::encode($data->user->schoolInfo->identitynumber),array("schoolInfo/view","id"=>$data->user_id)):""',
		),		
		array(
			'name'=>'Status',
			'type'=>'raw',
			'value'=>'((strlen($data->result)>0)?"<div style=\'color:red;\' title=\'".str_replace("\r\n","<br/>",CHtml::encode($data->result))."\' class=\'mes\' color=\'blue\'>":"").ULookup::$JUDGE_RESULT_MESSAGES[$data->status].(strlen($data->result)>0?"</div>":"")',
		),
		array(
			'name'=>'problem',
			'type'=>'raw',
			'visible'=>!isset($exercise_problem)||($exercise_problem==null),
			'value'=>'CHtml::link(CHtml::encode($data->problem->title),array("exerciseProblem/view/0/problem/".$data->problem_id."/exercise/".$data->exercise_id))',
		),
		'created',
		array(
			'name'=>'modification_times',
			'visible'=>(UUserIdentity::isTeacher())||(UUserIdentity::isAdmin()),
		),
		array(
			'name'=>'used_time',
			'type'=>'raw',
			'value'=>'($data->status==ULookup::JUDGE_RESULT_ACCEPTED)?$data->used_time."ms":""',
		),
		array(
			'name'=>'used_memory',
			'type'=>'raw',
			'value'=>'($data->status==ULookup::JUDGE_RESULT_ACCEPTED)?($data->used_memory>>10)."K":""',
		),
		array(
			'name'=>'compiler_id',
			'type'=>'raw',
			'value'=>'CHtml::encode(UCompilerLookup::display($data->compiler_id))',
		),		
		array(
			'name'=>'code_length',
			'type'=>'raw',
			'value'=>'$data->code_length',
		),

	),
));
