<?php
/**
 * Объект стартовая тренировка
 */
class StartingTraining extends Training {
	/**
	 * Конструктор
	 * @param string $hoursAndMinutes "12:15"
	 * @param integer $weekDay 1-7
	 * @param boolean $currentWeek true - текущая неделя, false - следующая
	 */
	public function __construct($hoursAndMinutes, $weekDay, $currentWeek = true) {
		$start = new Date();
		
		if($currentWeek === false) {
			$start->modify('+6 days');
			
		}

		if($hoursAndMinutes != null) {
			$start->setHoursAndMinutes($hoursAndMinutes->getHoursAndMinutes());
		}

		$start->setWeekday($weekDay);

		parent::__construct(array(
				'id' => 'START',
				'type' => 'Стартовая<br> тренировка',
				'timebeg' => $start->toString(),
				'timeend' => $start->modify('+1 hour')->toString()
			));

		$this->description = "Стартовая тренировка и вводный инструктаж.";
		$this->isVisible = true; 
		$this->isAvailable = true;
		$this->free = 1;
	}
}
?>
