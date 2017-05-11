<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
	<div class="container">
		<div class="row">
			<?php echo $message; ?>
		</div>
		<form method="POST" id="restrictions">
			<?php $i = 0; ?>
			<input type="hidden" name="action" value="save">
			<?php foreach($restrictions as $res) { ?>
			<div class="row">
				<input type="hidden" name="restrictions[<?php echo $i; ?>][id]" class="form-control" value="<?php echo $res->getId(); ?>">
				<div class="col-xs-3">
					<input type="text" name="restrictions[<?php echo $i; ?>][description]" class="form-control" placeholder="Описание" value="<?php echo $res->getDescription(); ?>">
				</div>
				<div class="col-xs-3">
					<input type="text" name="restrictions[<?php echo $i; ?>][entityid]" class="form-control" placeholder="ID клуба, тренировки или зала" value="<?php echo $res->getEntityId(); ?>">
				</div>
				<div class="col-xs-3">
					<input type="text" name="restrictions[<?php echo $i; ?>][restriction]" class="form-control" placeholder="Правило" value="<?php echo $res->getRestriction(); ?>">
				</div>
				<div class="col-xs-3">
					<label>
						<input type="checkbox" name="restrictions[<?php echo $i; ?>][enabled]" <?php echo ($res->isDisabled() ? "FFF" : "checked"); ?>> Включено
					</label>
				</div>
			</div>
			<?php 
					$i++; 
				} 
			?>		

			<?php foreach($incorrectRestrictions as $res) { ?>
			<div class="row">
				<input type="hidden" name="restrictions[<?php echo $i; ?>][id]" class="form-control" value="<?php echo $res->getId(); ?>">
				<div class="col-xs-3">
					<input type="text" name="restrictions[<?php echo $i; ?>][description]" class="form-control" placeholder="Описание" value="<?php echo $res->getDescription(); ?>">
				</div>
				<div class="col-xs-3 <?php echo $res->getEntityId() == "" ? "has-error" : ""; ?>">
					<input type="text" name="restrictions[<?php echo $i; ?>][entityid]" class="form-control" placeholder="ID клуба, тренировки или зала" value="<?php echo $res->getEntityId(); ?>">
				</div>
				<div class="col-xs-3 <?php echo ($res->getRestriction() == "" || !$res->checkCondition()) ? "has-error" : ""; ?>">
					<input type="text" name="restrictions[<?php echo $i; ?>][restriction]" class="form-control" placeholder="Правило" value="<?php echo $res->getRestriction(); ?>">
				</div>
				<div class="col-xs-3">
					<label>
						<input type="checkbox" name="restrictions[<?php echo $i; ?>][enabled]" <?php echo ($res->isDisabled() ? "" : "checked"); ?>> Включено
					</label>
				</div>
				<div class="col-xs-3">
					<?php echo $res->getConditionErrors(); ?>
				</div>
			</div>
			<?php 
					$i++; 
				} 
			?>	
		</form>


		<div class="row">
			<div class="col-xs-12 text-right">
				<button class="btn btn-default btn-success" type="submit" data-index="<?php echo $i; ?>" onclick="addRow(this);"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Добавить</button>
				<button class="btn btn-default btn-primary" type="submit" onclick="$('#restrictions').submit();"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Сохранить</button>
			</div>
		</div>
	</div>

	<script>
		function addRow(btn) {
			var i = $(btn).data('index');
			$(btn).data('index', i + 1);

			var row = '<div class="row">' +
						'<div class="col-xs-3">' +
							'<input type="text" name="restrictions[' + i + '][description]" class="form-control" placeholder="Описание">' +
						'</div>' +
						'<div class="col-xs-3">' +
							'<input type="text" name="restrictions[' + i + '][entityid]" class="form-control" placeholder="ID клуба, тренировки или зала">' +
						'</div>' +
						'<div class="col-xs-3">' +
							'<input type="text" name="restrictions[' + i + '][restriction]"  class="form-control" placeholder="Правило">' +
						'</div>' +
						'<div class="col-xs-3">' +
							'<label>' +
								'<input type="checkbox" name="restrictions[' + i + '][enabled]"  checked="true"> Включено' +
							'</label>' +
						'</div>' +
					'</div>';
			$('#restrictions').append(row);
		}

	</script>
</body>
</html>