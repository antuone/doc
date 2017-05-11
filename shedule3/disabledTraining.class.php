<?php
/**
 * Тренировка без записей
 */
class DisabledTraining extends Training {
	/**
	 * Undocumented function
	 */
	public function __construct() {
		parent::__construct(array());
		
		$train->isVisible = true;
		$train->isAvailable	= true;
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function toHTML() {
		return '<span cass="not_record">-</span>';
	}
}
?>
