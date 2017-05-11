<?php

$dbprefix = $modx->db->config['table_prefix'];
$tablename = $dbprefix . "training_restriction";

$createTableQuery = "CREATE TABLE IF NOT EXISTS `" . $tablename . "` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`entityid`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'идентефикатор сущности, которую нужно ограничить ( клуб, зал, тренировка и прочее)' ,
`disabled`  int(11) NOT NULL DEFAULT 0 ,
`restriction`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'ограничение в формате php' ,
`description`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'описание для ограничения' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
ROW_FORMAT=DYNAMIC
;";

$modx->db->query($createTableQuery);


$newRestrictions = array();
$incorrectRestrictions = array();
$existingRestrictions = array();

// нажали сохранить
if(isset($_POST['action']) && $_POST['action'] == 'save' && count($_POST['restrictions']) > 0) {
	foreach($_POST['restrictions'] as $resData) {
		$res = new TrainingRestriction($resData);

		/*if($res->isEmpty()) {
			continue;
		}*/

		if($res->isCorrectlyFilled() && $res->checkCondition()) {
			if($res->getId() != "") {
				$existingRestrictions[] = $res;
			} else {
				$newRestrictions[] = $res;
			}
		} else {
			$incorrectRestrictions[] = $res;
		}
	}
}


foreach ($newRestrictions as $res) {
	if($modx->db->insert($res->toArray(), $tablename)) {
		$res->setId($modx->db->getInsertId());
	} else {
		die("Ошибка при записи нового ограничения. Попробуйте перезагрузить страницу или обратиться в службу поддержки.");
	}
}

foreach ($existingRestrictions as $res) {
	$modx->db->update($res->toArray(), $tablename, "id = '" . $res->getId() . "'");
}

$restrictions = TrainingRestriction::makeArray();

if (count($restrictions) == 0) {
	$message = '<div class="alert alert-info" role="alert">Нет существующих ограничений</div>';
} 




