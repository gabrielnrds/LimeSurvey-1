<?php
/* @var $this ProjectController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Projects',
);

$this->menu=array(
	array('label'=>'Create Project', 'url'=>array('create')),
	array('label'=>'Manage Project', 'url'=>array('admin')),
);
?>

<h1>Criar Projeto</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>

<h1>Projetos</h1>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'project-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'title',
		'coordinator',
		'coordinator_email',
		'completion_estimate',
		'current_hours',
		'status',
		
		array(
			'class'=>'CButtonColumn',
			'viewButtonUrl'=>'CHtml::submitButton()',
		),
	),
)); ?>
