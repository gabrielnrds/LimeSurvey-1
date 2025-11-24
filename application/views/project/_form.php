<?php
/* @var $this ProjectController */
/* @var $model Project */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'project-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Campos com <span class="required">*</span> são obrigatórios.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'Título'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Coordenador'); ?>
		<?php echo $form->textField($model,'coordinator',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'coordinator'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Email Coordenador'); ?>
		<?php echo $form->textField($model,'coordinator_email',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'coordinator_email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Estimativa de Conclusão'); ?>
		<?php echo $form->textField($model,'completion_estimate'); ?>
		<?php echo $form->error($model,'completion_estimate'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'CRIAR' : 'SALVAR'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->