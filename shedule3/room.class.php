<?php
/**
 * Объект комната в спорткомплексах.
 * Хранит тренировки. Применяет к ним правила.
 * Выводит тренировки в HTML.
 */
class Room implements Iterator
{
    /**
     * Идентификатор комнаты
     * @var string
     */
    private $id;
    /**
     * Название комнаты
     * @var string
     */
    private $name;
    /**
     * Массив тренировок
     * @var Training[]
     */
    private $trainings;
    /**
     * Внутренее представление куба расписания для этой комнаты
     * @var TrainingCube
     */
    private $cube;
    /**
     * Конструктор
     * @param array $arr {"room":"","roomid":""}
     */
    public function __construct($arr)
    {
        $this->name = $arr['room'];
        $this->id = $arr['roomid'];
        $this->trainings = array();
    }
    /**
     * Добавить тренировку в комнату
     * @param Training $training Объект тренировки
     * @return void
     */
    public function add($training)
    {
        if ($training instanceof Training) {
            $this->trainings[] = $training;
        }
    }
    /**
     * Возвращает имя комнаты
     * @return string Имя
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Возвращает id комнаты
     * @return string ID
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Сортирует тренировке по времени, от раннего к позднему
     * @return void
     */
    private function sort()
    {
        usort($this->trainings, array('Training', 'compare'));
    }
    /**
     * Выводит комнату с тренировками в HTML
     * @return void
     */
    public function toHTML()
    {
        global $modx;

        if ($this->visibleTrainingsCount() == 0) {
            return "";
        }

        $this->sort();

        $content = "";

        $time = new Date();

        foreach ($this->cube as $hoursAndMinutes => $data) {
            $time->setHoursAndMinutes($hoursAndMinutes);
            $content .= "<tr>";
            $content .= "<td class='schedule_time'>" . $time->getHoursAndMinutesWithoutLeadingZero() . "</td>";

            foreach ($data as $weekDayIndex => $trainings) {

                $content .= "<td>";

                foreach ($trainings as $train) {
                    if ($train->isVisible()) {
                        $content .= $train->toHTML();
                    }
                }
                $content .= "</td>";
            }
            $content .= "</tr>";
        }

        return $modx->parseChunk(
            'schedule.rest.room',
            array(
                'name' => $this->name,
                'id' => $this->id,
                'content' => $content,
                'period' => $this->makePeriod(),
            ),
            '[+', '+]'
        );
    }
    /**
     * Применить правила к комнате.
     * @param TrainingRestriction[] $restrictions Массив правил
     * @return void
     */
    public function applyRestrictions($restrictions)
    {
        /*Если комната есть в ограничениях и правило вернуло false, то все тренировки делаем невидимыми.*/
        foreach ($restrictions as $res) {
            if ($res->getEntityId() == $this->id) {
                if ($res->checkNow() === false) {
                    foreach ($this->trainings as $train) {
                        $train->setDisabled();
                    }
                    return;
                }
            }
        }

        $now = new Date();

        foreach ($this->trainings as $train) {
            $found = false;
            /* Если тренировка сегодня и до нее осталось меньше 30 минут, то делаем ее недоступной*/
            if ($now->val('daystamp') == $train->getTime()->val('daystamp') && ($train->getTime()->val('time') - $now->val('time')) < 30) {
                $train->disableEnroll();
            }
            /*Ищем тренировку в ограничениях*/
            foreach ($restrictions as $res) {
                if ($train->getId() == $res->getEntityId() || $train->getTypeId() == $res->getEntityId()) {
                    $found = true;
                    if ($res->check($train->getTime()) === false) {
                        /* если код в expression возвращает false
                        то тренировака становится без "карандаша"*/
                        if ($res->isSimpleCondition()) {
                            $train->setDisabled();
                        } else {
                            $train->disableEnroll();
                        }
                    }
                    break;
                }
            }
            if (!$found) {
                $train->disableEnroll();
            }
        }
    }
    /**
     * Обновить описание для каждой тренировкий в этой комнате
     * @todo очень затратная по времени и ресурсам - делает много запросов, когда можно сделать одним
     * @return void
	 * @deprecated
     */
    public function updateDescription()
    {
        foreach ($this->trainings as $train) {
            $train->updateDescription();
        }
    }
    /**
     * Возвращает текст периода.
     * @return string  Пример: (с 1 мая по 7 мая)
     */
    private function makePeriod()
    {
        if (count($this->trainings) == 0) {
            return;
        }

        $dateMin = $this->trainings[0]->getTime('begin');
        $now = new Date();
        $monday = new Date();
        $sunday = new Date();

        $current = $now->getWeekNumber() == $dateMin->getWeekNumber();

        if ($current) {
            $monday->modify('Monday this week');
            $sunday->modify('Sunday this week');
        } else {
            $monday->modify('Monday next week');
            $sunday->modify('Sunday next week');
        }

        $months = array(
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря',
        );

        return "(с "
        . $monday->getDayNumber()
        . " "
        . $months[$monday->getMonthNumber() - 1]
        . " по "
        . $sunday->getDayNumber()
            . " "
            . $months[$sunday->getMonthNumber() - 1]
            . ")";
    }
    /**
     * Инициализирует внутренее представление куба расписания
     * @return void
     */
    public function buildCube()
    {
        $this->cube = new TrainingCube();
        foreach ($this->trainings as $train) {
            $this->cube->insert($train);
        }
        $this->cube->sort();
    }
    /**
     * Инициальзирует внутренее представление куба расписания для стартовых тренеровок
     * @param string $workingHours Объект WorkingHours
     * @param boolean $currentWeek true - текущая неделя, false - слудующая
     * @return void
     */
    public function makeStartingTrainings($workingHours, $currentWeek)
    {
        $this->cube->makeStartingTrainings($workingHours, $currentWeek);
    }
	/**
	 * Возвращает количество отображаемых тренеровок
	 * @return integer Количество тренировок
	 */
    private function visibleTrainingsCount()
    {
        $count = 0;

        foreach ($this->cube as $hoursAndMinutes => $date) {
            foreach ($date as $weekDayIndex => $trainings) {
                foreach ($trainings as $train) {
                    if ($train->isVisible()) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
	/**
	 * Обновляет количество свободных мест для каждой тренировки из кеша (из локальной БД), также каждую тренировку делает видимой и еще изменяет доступность каждой тренировки по формуле (free > 0 && isVisible)
	 * @todo смысл в этом кеше? то что это делает, мне не понятно
	 * @param ScheduleCache $cache
	 * @return void
	 */
    public function updateCache($cache)
    {
        foreach ($this->trainings as $train) {
            $train->updateCache($cache);
        }
    }
    /**
	 * implementing Iterator interface
	 * @return void
	 */
    public function current()
    {
        return current($this->trainings);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function key()
    {
        return key($this->trainings);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function next()
    {
        return next($this->trainings);
    }
    /**
     * implementing Iterator interface
     * @return void
     */
    public function rewind()
    {
        return reset($this->trainings);
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
?>
