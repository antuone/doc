<?php
/**
 * Объект для работы с датой и временем
 */
class Date extends DateTime
{
    /**
     * Формат даты и времени используемый в запросах
     * @var string
     */
    private static $FORMAT = "Y-m-d\TH:i:s";
    /**
     * Временная зона
     * @var [type]
     */
    private static $TIMEZONE = null;
    /**
     * Конструктор создает объект с временной зоной 'Asia/Yekaterinburg'
     */
    public function __construct()
    {
        if (self::$TIMEZONE == null) {
            self::$TIMEZONE = new DateTimeZone('Asia/Yekaterinburg');
        }
        parent::__construct('NOW', self::$TIMEZONE);
    }
    /**
     * Возвращает дату и время в виде строки с установленным форматом в $this->$FORMAT
     * @return string Дата в виде строки в формате "Y-m-d\TH:i:s"
     */
    public function toString()
    {
        return parent::format(self::$FORMAT);
    }
    /**
     * Возвращает объект Data
     * @param string $string Строка в формате "Y-m-d\TH:i:s"
     * @return Data
     */
    public static function parse($string)
    {
        if (empty($string)) {
            return false;
        }

        $datetime = DateTime::createFromFormat(self::$FORMAT, $string, new DateTimeZone('Asia/Yekaterinburg'));
        if (!$datetime) {
            return false;
        }

        $date = new Date();
        $date->setTimestamp($datetime->getTimestamp());
        return $date;
    }
    /**
     * Возвращает часы и минут
     * @return string Строка в формате 'H:i'
     */
    public function getHoursAndMinutes()
    {
        return $this->format('H:i');
    }
    /**
     * Возвращает часы и минуты
     * @return string Строка в формате 'G:i'
     */
    public function getHoursAndMinutesWithoutLeadingZero()
    {
        return $this->format('G:i');
    }
    /**
     * Возвращает день недели
     * @return string  c 1(понедельник) до 7(воскресенье)
     */
    public function getWeekDayNumber()
    {
        return $this->format('N');
    }
    /**
     * Возвращает порядковый номер дня 
     * @return string (1 - 32)
     */
    public function getDayNumber()
    {
        return $this->format('j');
    }
    /**
     * Возвращает порядковый номер месяца
     * @return string (1 - 12)
     */
    public function getMonthNumber()
    {
        return $this->format('n');
    }
    /**
     * Возвращает штамп минут
     * @return string
     */
    public function getMinutesStamp()
    {
        return $this->format('H') * 60 + $this->format('i');
    }
    /**
     * Возвращает число минут 
     * @return string (0 - 59)
     */
    private function getMinutes()
    {
        return $this->format('i');
    }
    /**
     * Возвращает число часа 
     * @return string (0 - 23)
     */
    private function getHours()
    {
        return $this->format('H');
    }
    /**
     * Возвращает число недели
     * @return string (1 - 7)
     */
    public function getWeekNumber()
    {
        return $this->format('W');
    }
    /**
     * Возвращает число дня, число месяца, номер дня недели или другое
     * @todo Функция видимо для того чтобы сократить текст кода, в ущерб наглядности, а может для чего-то другого
     * @param string $text month, day, weekday, week, time, minutes, hours, daystamp или timestamp
     * @return string ?
     */
    public function val($text)
    {
        switch ($text) {
            case 'month':
                return $this->getMonthNumber();

            case 'day':
                return $this->getDayNumber();

            case 'weekday':
                return $this->getWeekDayNumber();

            case 'week':
                return $this->getWeekNumber();

            case 'time':
                return $this->getMinutesStamp();

            case 'minutes':
                return $this->getMinutes();

            case 'hours':
                return $this->getHours();

            case 'daystamp':
                return $this->format('z');

            case 'timestamp':
                return $this->getTimestamp();

            default:
                return 0;
        }
    }
    /**
     * Возвращает день и месяц
     * @return string Строка в формате 'j.n'
     */
    public function getDayAndMonth()
    {
        return $this->format('j.n');
    }
    /**
     * Устанавливает часы и минуты
     * @param string $str В формате "12:30"
     * @return void
     */
    public function setHoursAndMinutes($str)
    {
        $arr = explode(":", $str);

        if (array_key_exists(0, $arr)) {
            $h = $arr[0];
        } else {
            $h = 0;
        }

        if (array_key_exists(1, $arr)) {
            $m = $arr[1];
        } else {
            $m = 0;
        }

        if (array_key_exists(2, $arr)) {
            $s = $arr[2];
        } else {
            $s = 0;
        }

        $this->setTime($h, $m, $s);
    }
    /**
     * Устанавливает номер недели и возвращает себя
     * @param integer $number (1 - 7)
     * @return Date $this
     */
    public function setWeekday($number)
    {
        $diff = $number - $this->getWeekDayNumber();

        if ($diff < 0) {
            $this->modify('-' . $diff . ' day');
        } else if ($diff > 0) {
            $this->modify('+' . $diff . ' day');
        }

        return $this;
    }
}
?>
