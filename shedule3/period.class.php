<?php
/**
 * Объект хранит период времени в часах и минутах
 */
class Period
{
    /**
     * Начало переода
     * @var Date
     */
    public $from;
    /**
     * Конец периода
     * @var Date
     */
    public $to;
    /**
     * Конструктор
     * @param string $str Строка времени в формате "12:15-15:20"
     */
    public function __construct($str)
    {
        if (strlen($str) == 0) {
            return;
        }

        if (strpos($str, "-") == -1) {
            return;
        }

        $time = explode("-", $str);

        $this->from = new Date();
        $this->from->setHoursAndMinutes($time[0]);

        $this->to = new Date();
        $this->to->setHoursAndMinutes($time[1]);
    }
}
?>
