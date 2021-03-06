<?php
$this->breadcrumbs=array(
	'Courses',
);

$this->menu=array(
	array('label'=>'Create Course', 'url'=>array('create')),
	array('label'=>'Manage Course', 'url'=>array('admin')),
);
?>
<?php
$this->toolbar=array(
		((!Yii::app()->user->isGuest) && Yii::app()->request->getQuery('mine',null)!==null)?
		array(
				'label'=>Yii::t('course','All courses'),
				'icon-position'=>'left',
				'icon'=>'circle-plus', // This a CSS class starting with ".ui-icon-"
				'url'=>array('/course/index'),
				'visible'=>true,
		):
		array(
				'label'=>Yii::t('course','My courses'),
				'icon-position'=>'left',
				'icon'=>'circle-plus', // This a CSS class starting with ".ui-icon-"
				'url'=>array('/course/index/mine'),
				'visible'=>(!Yii::app()->user->isGuest),
		),
		array(
				'label'=>Yii::t('course','Create course'),
				'icon-position'=>'left',
				'icon'=>'document',
				'url'=>array('/course/create'),
				'visible'=>UUserIdentity::isTeacher()||UUserIdentity::isAdmin()
		),
);
?>
<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
