<?php
/**
 * Объект правило(ограничение) для тренировки
 */
class TrainingRestriction
{
    /**
     * id самого ограничения
     * @var string
     */
    private $id;
    /**
     * id тренировки, для которой это ограничение
     * @var string
     */
    private $entityId;
    /**
     * Огринчеине в формате php
     * @var string
     */
    private $restriction;
    /**
     * Описание правила
     * @var string
     */
    private $description;
    /**
     * Объект для проверки правил для расписания
     * @var Condition
     */
    private $condition;
    /**
     * Конструктор
     * @param array $arr {id, entityid, restriction, enabled, disabled}
     */
    public function __construct($arr)
    {
        if (isset($arr['id']) && !empty($arr['id'])) {
            $this->id = $arr['id'];
        } else {
            $this->id = "";
        }

        $this->entityId = $arr['entityid'];
        $this->restriction = $arr['restriction'];

        $this->condition = new Condition($this->restriction);
        $this->description = $arr['description'];

        if (empty($arr['enabled']) || $arr['enabled'] != 'on') {
            $this->disabled = 1;
        } else {
            $this->disabled = 0;
        }

        if (isset($arr['disabled'])) {
            $this->disabled = $arr['disabled'];
        }
    }
    /**
     * Выполняет правило и возвращает результат. Если есть ошибки, то выводит их.
     * @return boolean true - тренировка доступна для записи, false - нет
     * @deprecated
     */
    public function checkCondition()
    {
        return $this->condition->parseExpression();
    }
    /**
     * Проверяет равно ли выражение правила "false"
     * @return boolean
     */
    public function isSimpleCondition()
    {
        return $this->condition->isSimple();
    }
    /**
     * Возвращает описание правила
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Выполняет правило для указанной даты и возвращает результат.
     * @param Date $date
     * @return boolean true - тренировка доступна для записи, false - нет
     */
    public function check($date)
    {
        return $this->condition->check($date);
    }
    /**
     * Выполняет правило для текущей даты и возвращает результат.
     * @return boolean true - тренировка доступна для записи, false - нет
     */
    public function checkNow()
    {
        $date = new Date();
        return $this->check($date);
    }
    /**
     * Возвращает этот объект в виде массива
     * @return array {entityid, restriction, disabled, description}
     */
    public function toArray()
    {
        global $modx;
        $this->condition->flushErrors();
        $res = array(
            'entityid' => $this->entityId,
            'restriction' => $modx->db->escape($this->restriction),
            'disabled' => $this->disabled,
            'description' => $this->description,
        );
        return $res;
    }
    /**
     * Возвращает id ограничения
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Возвращает id тренировки
     * @return string
     */
    public function getTrainingId()
    {
        return $this->entityId;
    }
    /**
     * Возвращает id тренировки или комнаты
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
    /**
     * Возвращает правило PHP
     * @return string
     */
    public function getRestriction()
    {
        return $this->restriction;
    }
    /**
     * Возращает isDisabled
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
    /**
     * Undocumented function
     * @todo что-то тут не так
     * @return true
     */
    public function isCorrectlyFilled()
    {
        //return !empty($this->entityId) && !empty($this->restriction);
        return true;
    }
    /**
     * Проверка на пустоту параметров entityId и restriction
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->entityId) && empty($this->restriction);
    }
    /**
     * Установка id для ограничения
     * @param string $newId
     * @return void
     */
    public function setId($newId)
    {
        $this->id = $newId;
    }
    /**
     * Вывести ошибки
     * @return string
     */
    public function getConditionErrors()
    {
        return $this->condition->flushErrors();
    }
    /**
     * Возвращает массив всех ограничений
     * @todo Интересный подход получить массив и перезаписать этот массив в другой массив и только потом вернуть
     * @return array
     */
    public static function makeArray()
    {
        global $modx;

        $dbprefix = $modx->db->config['table_prefix'];
        $tablename = $dbprefix . "training_restriction";
        $data = $modx->db->makeArray($modx->db->query("select * from " . $tablename . " order by id asc;"));

        $restrictions = array();

        foreach ($data as $res) {
            $restrictions[] = new TrainingRestriction($res);
        }

        return $restrictions;
    }
    /**
     * Возвращает массив всех ограничений c disabled = 0
     * @todo Интересный подход получить массив и перезаписать этот массив в другой массив и только потом вернуть
     * @return void
     */
    public static function makeArrayFromActive()
    {
        global $modx;

        $dbprefix = $modx->db->config['table_prefix'];
        $tablename = $dbprefix . "training_restriction";
        $data = $modx->db->makeArray($modx->db->query("select * from " . $tablename . " where disabled = 0 order by id asc;"));

        $restrictions = array();

        foreach ($data as $res) {
            $restrictions[] = new TrainingRestriction($res);
        }

        return $restrictions;
    }
}
