<?php
/* @var $this ProjectController */
/* @var $data Project */
?>

<div class="view">

	<b><?php echo 'ID' ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo 'Título' ?>:</b>
	<?php echo CHtml::encode($data->title); ?>
	<br />

	<b><?php echo 'Coordenador' ?>:</b>
	<?php echo CHtml::encode($data->coordinator); ?>
	<br />

	<b><?php echo 'E-mail do Coordenador' ?>:</b>
	<?php echo CHtml::encode($data->coordinator_email); ?>
	<br />

	<b><?php echo 'Estimativa de conclusão' ?>:</b>
	<?php echo CHtml::encode($data->completion_estimate); ?>
	<br />

	<b><?php echo 'Horas atuais' ?>:</b>
	<?php echo CHtml::encode($data->current_hours); ?>
	<br />

	<b><?php echo 'Status' ?>:</b>
	<?php echo CHtml::encode($data->status); ?>
	<br />
	
</div>