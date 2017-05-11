<?php
/**
 * Объект тренировка
 */
class Training
{
    // данные от 1С
    /**
     * id тренеровки
     * @var string
     */
    protected $id;
    /**
     * Дата и время начала тренеровки
     * @var Date
     */
    protected $begin;
    /**
     * Дата и время конца тренеровки
     * @var Date
     */
    protected $end;
    /**
     * Тип тренеровки
     * @var string
     */
    protected $type;
    /**
     *  Тренер
     * @var string
     */
    protected $coach;
    /**
     * Количество свободных мест
     * @var integer
     */
    protected $capacity;
    /**
     * Undocumented variable
     * @var integer
     */
    protected $reserv;
    /**
     * Undocumented variable
     * @var integer
     */
    protected $booking;
    /**
     * Undocumented variable
     * @var integer
     */
    protected $bookings;
    /**
     * Свободных мест
     * @var integer
     */
    protected $free;
    /**
     * Описание тренеровки
     * @var string
     */
    protected $description;
    /**
     * id тип тренеровки
     * @var string
     */
    protected $typeid;
    /**
     * Доступность тренеровки
     * @var boolean
     */
    protected $isAvailable = true;
    /**
     * Видимость тренеровки в расписании
     * @var boolean
     */
    protected $isVisible;
    /**
     * Конструктор
     * @param array $array { "id": "c426b879-d64d-11e6-8b7e-001e6717c644", "club": "01", "timebeg": "2017-01-17T08:30:00", "timeend": "2017-01-17T09:30:00", "room": "Большой бассейн", "roomid": "0e221511-47f3-11e1-9f1f-001e6717c644", "type": "Аквааэробика AquaFit", "typeid": "60559560-48db-11e1-9f1f-001e6717c644", "coach": "Ситникова Анастасия Алексеевна", "capacity": 9, "reserv": 5, "booking": 6, "bookings": 1, "free": 0 }
     */
    public function __construct($array)
    {
        $this->id = $array['id'];
        $this->begin = Date::parse($array['timebeg']);
        $this->end = Date::parse($array['timeend']);
        $this->type = $array['type'];
        $this->coach = $array['coach'];
        $this->capacity = $array['capacity'];
        $this->reserv = $array['reserv'];
        $this->booking = $array['booking'];
        $this->bookings = $array['bookings'];
        $this->typeid = $array['typeid'];
        $this->description = $array['description'];
        $this->free = $this->getFree();
        $this->isVisible = true;
    }
    /**
     * Обновляет количество свободных мест для тренеровки из кеша (из локальной БД), также каждую тренировку делает видимой и еще изменяет доступность каждой тренеровки по формуле (free > 0 && isVisible)
     * @todo смысл в этом кеше? то что это делает, мне не понятно
     * @param ScheduleCache $cache
     * @return void
     */
    public function updateCache($cache)
    {
        $this->free = $cache->getTrainingFree($this->id);
        $this->isVisible = true;
        $this->isAvailable = $this->free > 0 && $this->isVisible;
    }
    /**
     * Выводит в HTML тренеровку
     * @return void
     */
    public function toHTML()
    {
        global $modx;
        $coachName = explode(' ', $this->coach);
        return $modx->parseChunk(
            'schedule.rest.training',
            array(
                'id' => $this->id,
                'type' => $this->type,
                'coach' => $coachName[0] . ' ' . $coachName[1],
                'available' => $this->isAvailable ? '1' : '0',
                'free' => $this->free,
                'visible' => $this->isVisible ? '1' : '0',
                'timestamp' => $this->begin->getTimestamp(),
                'description' => $this->description,
                'typeid' => $this->typeid,
                //'debug' => var_export($this, true)
            ),
            '[+', '+]'
        );
    }
    /**
     * Сравнение двух времен
     * @param Date $tr1
     * @param Date $tr2
     * @return boolean
     */
    public static function compare($tr1, $tr2)
    {
        return $tr1->begin->getHoursAndMinutes() > $tr2->begin->getHoursAndMinutes();
    }
    /**
     * Undocumented function
     * @deprecated version
     * @param [type] $restriction
     * @return void
     */
    public function applyIfAppropriate($restriction)
    {
        if (!($restriction instanceof TrainingRestriction)) {
            return;
        }

        if ($restriction->getTrainingId() != $this->id && $restriction->getTrainingId() != $this->typeid) {
            return;
        }

        if ($restriction->check($this->begin) === false) {
            $this->setDisabled();
        }
    }
    /**
     * Возвращает видимость тренеровки
     * @return boolean
     */
    public function isVisible()
    {
        return $this->isVisible;
    }
    /**
     * Возвращает доступность тренеровки для записи
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->getFree() > 0 && $this->isVisible();
    }
    /**
     * Устанавливает тренировку невидимой и не доступной
     * @return void
     */
    public function setDisabled()
    {
        $this->isAvailable = false;
        $this->isVisible = false;
    }
    /**
     * Возвращает время начала или конца тренеровки
     * @param string $string begin or end
     * @return string
     */
    public function getTime($string = 'begin')
    {
        if ($string == 'begin') {
            return $this->begin;
        } else if ($string == 'end') {
            return $this->end;
        } else {
            return false;
        }
    }
    /**
     * Возвращает id тренеровки
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Возвращает тип тренеровки
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Возвращает описание тренеровки
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Возвращает id типа тренеровки
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeid;
    }
    /**
     * Обновляет описание из БД
     * @deprecated
     * @return void
     */
    public function updateDescription()
    {
        global $modx;

        $doc_id = $modx->db->getValue($modx->db->select(
            'id',
            $modx->getFullTableName('site_content'),
            'LOWER(`pagetitle`)="' . strtolower($this->type) . '" AND parent="79"'
        ));

        $this->description = $modx->db->getValue($modx->db->select(
            'value',
            $modx->getFullTableName('site_tmplvar_contentvalues'),
            'tmplvarid="47" AND contentid="' . $doc_id . '"'
        ));
    }
    /**
     * Отменяет доступность для записи с сайта
     * @return void
     */
    public function disableEnroll()
    {
        $this->isAvailable = false;
    }
    /**
     * Скрывает тренеровку
     * @return void
     */
    public function hide()
    {
        $this->isVisible = false;
    }
    /**
     * Возвращает число свободных мест
     * @todo несколько вариантов, на основе работы с 1С
     * return max(0, $this->capacity - $this->reserv - $this->booking);
     * return $this->free;
     * return $this->capacity - $this->reserv - $this->bookings
     * но там какая-то херь, не работает так, как написано, поэтому, пока данных от Петра нет - делаю просто
     * return $this->free;
     * UPD: Похоже, что у петра там херь какая-то, поэтому данные про free в выгрузке некорректные, приходится считать
     * если в резерве еще есть места для записи в клубе
     * @return void
     */
    public function getFree()
    {
        if ($this->reserv > ($this->booking - $this->bookings)) {
            $free = $this->capacity - $this->reserv - $this->bookings;
        } else {
            $free = $this->capacity - $this->booking;
        }
        return $free;
    }

}
?>
