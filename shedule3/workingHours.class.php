<?php
/**
 * Объект для рабочего времени на всю неделю
 */
class WorkingHours
{
    private $schedule;
    public $weekDays;
    public $saturday;
    public $sunday;

    /*
    Формат входного массива:
    'weekdays' => '8:30-22:30',
    'saturday' => '9:00-10:00',
    'sunday' => '13:00-22:00'
     */

    public function __construct($arr)
    {

        for ($weekDay = 1; $weekDay <= 5; $weekDay++) {
            $this->schedule[$weekDay] = new Period($arr['weekdays']);
        }

        $this->schedule[6] = new Period($arr['saturday']);
        $this->schedule[7] = new Period($arr['sunday']);

        $this->weekDays = $this->schedule[1];
        $this->saturday = $this->schedule[6];
        $this->sunday = $this->schedule[7];
        $this->totalPeriod = new Period("");
        $this->totalPeriod->from = $this->getMin();
        $this->totalPeriod->to = $this->getMax();
    }
    /**
     * Возвращает самое раннее время начала работы
     * @return string
     */
    private function getMin()
    {
        $min = $this->schedule[1]->from;

        for ($weekDay = 2; $weekDay <= 7; $weekDay++) {
            if ($this->schedule[$weekDay]->from < $min) {
                $min = $this->schedule[$weekDay]->from;
            }
        }

        return $min;
    }
    /**
     * Возвращает самое позднее время окнчания работы
     * @return string
     */
    private function getMax()
    {
        $max = $this->schedule[1]->to;

        for ($weekDay = 2; $weekDay <= 7; $weekDay++) {
            if ($this->schedule[$weekDay]->to > $max) {
                $max = $this->schedule[$weekDay]->to;
            }
        }
        return $max;
    }
}
