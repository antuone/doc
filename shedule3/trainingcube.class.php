<?php
/**
 * Внутренее представление куба расписания
 */
class TrainingCube implements Iterator
{
    /**
     * Х - время занятий, Y - дни недели, Z - тренировки в этот день, в это времмя
     * @var array
     */
    public $cube;
	/**
	 * Конструктор
	 */
    public function __construct()
    {
        $this->cube = array();
    }
    /**
	 * Добавляем время для всех дней недели
	 * @param Date $date
	 * @return void
	 */
    public function addTime($date)
    {
        if (!($date instanceof Date)) {
            return;
        }

        $time = $date->getHoursAndMinutes();

        if (isset($this->cube[$time])) {
            return;
        }

        for ($weekDay = 1; $weekDay <= 7; $weekDay++) {
            $this->cube[$time][$weekDay] = array();
        }
    }
	/**
	 * Добавить тренеровку в куб
	 * @param Training $training
	 * @return void
	 */
    public function insert($training)
    {
        $time = $training->getTime('begin');

        $this->addTime($time);

        $weekDayIndex = $time->getWeekDayNumber();
        $hoursAndMinutes = $time->getHoursAndMinutes();

        $this->cube[$hoursAndMinutes][$weekDayIndex][] = $training;
    }
	/**
	 * Сортирует тренеровки по времени
	 * @return void
	 */
    public function sort()
    {
        uksort($this->cube, array($this, 'compareTime'));
    }
	/**
	 * Инициализирует стартовые тренеровки в кубе
	 * @param WorkingHours $workingHours
	 * @param boolean $currentWeek
	 * @todo этот метод вызывать только для комнаты со стартовыми тренировками
	 * @return void
	 */
    public function makeStartingTrainings($workingHours, $currentWeek = true)
    {
        $now = new Date();

        if (!$currentWeek) {
            $now->modify('Monday next week');
        }

        foreach ($this->cube as $time => $data) {
            if (substr($time, -2) != '00') {
                unset($this->cube[$time]);
            }
        }

        //Рабочие дни
        for ($weekDay = 1; $weekDay <= 5; $weekDay++) {
            $this->makeStartingTrainingsForOneDay(
                $workingHours->weekDays,
                $now,
                $workingHours->totalPeriod,
                $weekDay,
                $currentWeek
            );
        }

        //Суббота
        $this->makeStartingTrainingsForOneDay(
            $workingHours->saturday,
            $now,
            $workingHours->totalPeriod,
            6,
            $currentWeek
        );

        //Воскресенье
        $this->makeStartingTrainingsForOneDay(
            $workingHours->sunday,
            $now,
            $workingHours->totalPeriod,
            7,
            $currentWeek
        );

        $this->sort();
    }
	/**
	 * Инициализирует данные для тренировки для куба
	 * @param Period $workingPeriod
	 * @param Date $now
	 * @param Period $totalPeriod
	 * @param integer $weekDay
	 * @param boolean $currentWeek
	 * @return void
	 */
    private function makeStartingTrainingsForOneDay($workingPeriod, $now, $totalPeriod, $weekDay, $currentWeek)
    {
        $start = $workingPeriod->from;
        $end = $workingPeriod->to;
        $from = $totalPeriod->from;
        $to = $totalPeriod->to;

        $current = new Date();
        if (!$currentWeek) {
            $current->modify('+6 days');
        }
        //$current->setWeekday($weekDay);
        $current->setHoursAndMinutes($from->getHoursAndMinutes());

        $finish = new Date();
        if (!$currentWeek) {
            $finish->modify('+6 days');
        }
        //$finish->setWeekday($weekDay);
        $finish->setHoursAndMinutes($to->getHoursAndMinutes());

        for (; $current <= $finish; $current->modify('+1 hour')) {
            $time = $current->getHoursAndMinutes();

            if ($current->getMinutesStamp() + 60 > $end->getMinutesStamp()) {
                $this->cube[$time][$weekDay] = array(new DisabledTraining());
            }

            if ($current->getMinutesStamp() < $start->getMinutesStamp() || $current->getMinutesStamp() > $end->getMinutesStamp()) {
                $this->cube[$time][$weekDay] = array(new DisabledTraining());
                continue;
            }

            if (count($this->cube[$time][$weekDay]) > 0) {
                $this->cube[$time][$weekDay] = array(new DisabledTraining());
                continue;
            }

            if ($currentWeek) {
                if ($weekDay < $now->getWeekDayNumber()) {
                    // все дни до сегодня делаем неактивными- тренировка уже прошла
                    $this->cube[$time][$weekDay] = array(new DisabledTraining());
                    continue;
                } else if ($weekDay == $now->getWeekDayNumber()) {
                    // сегодня - за пол часа до тренировки делаем неактивными
                    $diff = $current->diff($now);
                    $diffMinutes = $diff->h * 60 + $diff->i;
                    if ($diffMinutes <= 30 || $diff->invert == 0) {
                        $this->cube[$time][$weekDay] = array(new DisabledTraining());
                        continue;
                    }
                }
            }

            $this->cube[$time][$weekDay] = array(new StartingTraining($current, $weekDay, $currentWeek));
        }
    }
	/**
	 * Сравнивает время
	 * @param ? $time1
	 * @param ? $time2
	 * @return void
	 */
    public function compareTime($time1, $time2)
    {
        return $time1 > $time2;
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function current()
    {
        return current($this->cube);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function key()
    {
        return key($this->cube);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function next()
    {
        return next($this->cube);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function rewind()
    {
        return reset($this->cube);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function valid()
    {
        $key = $this->key();
        return ($key !== null && $key !== false);
    }
}
