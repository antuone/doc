<?php
/**
 * Объект якобы для кеширования расписания
 * @todo Не полное кеширование получается
 */
class ScheduleCache
{
    /**
     * Название таблицы в которой хранится кеш
     * @var string
     */
    private $tablename;
    /**
     * Конструктор создает таблицу в БД если не создана
	 * @todo данных в таблице маловато
     */
    public function __construct()
    {
        global $modx;

        $dbprefix = $modx->db->config['table_prefix'];
        $this->tablename = $dbprefix . "schedule_cache";

        $createTableQuery = "CREATE TABLE IF NOT EXISTS `" . $this->tablename . "` (
		`trainingid`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'id тренировки из 1C' ,
		`free`  int(11) NOT NULL COMMENT 'Количество свободных мест для сайта' ,
		`club` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'номер клуба из 1C' ,
		PRIMARY KEY (`trainingid`)
		)
		ENGINE=InnoDB
		DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
		AUTO_INCREMENT=1
		ROW_FORMAT=DYNAMIC
		;";

        $modx->db->query($createTableQuery);
    }
    /**
     * Запрос к БД на количество свободных мест в тренировках.
     * @return array  Возвращает ассоциативный массив 1C-идентефикатор => free
     */
    private function fetch()
    {
        global $modx;

        $res = $modx->db->select("*", $this->tablename, "", "trainingid asc");
        $array = array();

        while ($row = $modx->db->getRow($res)) {
            $array[$row['trainingid']] = $row['free'];
        }

        return $array;
    }
	/**
	 * Удалить таблицу в БД
	 * @return void
	 */
    private function drop()
    {
        global $modx;
        $modx->db->query("drop table " . $this->tablename);
    }
	/**
	 * Очистить таблицу в БД
	 * @return void
	 */
    private function truncateAll()
    {
        global $modx;
        $modx->db->query("truncate table " . $this->tablename);
    }
	/**
	 * Удалить из таблицы тренировки с указанным клубом
	 * @param string $clubId ID клуба
	 * @return void
	 */
    private function truncate($clubId)
    {
        global $modx;
        $modx->db->query("delete from " . $this->tablename . " where club = " . $clubId);
    }
    /**
	 * Кэширует расписание
	 * @param array $array Массив расписаний
	 * @return void
	 */
    public static function storeArrayOfSchedules($array)
    {
        global $modx;

        $cache = new ScheduleCache();
        $cache->truncateAll();

        foreach ($array as $schedule) {
            foreach ($schedule as $room) {
                foreach ($room as $training) {
                    $fields = array(
                        'trainingid' => $training->getId(),
                        'free' => $training->getFree(),
                    );
                    $modx->db->insert($fields, $cache->tablename);
                }
            }
        }
    }
	/**
	 * Кэширует расписание
	 * @param Schedule $schedule Расписание
	 * @return void
	 */
    public static function storeSchedule($schedule)
    {
        global $modx;

        $cache = new ScheduleCache();
        $cache->truncate($schedule->getClubId());

        foreach ($schedule as $room) {
            foreach ($room as $training) {
                $fields = array(
                    'trainingid' => $training->getId(),
                    'free' => $training->getFree(),
                    'club' => $schedule->getClubId(),
                );
                $modx->db->insert($fields, $cache->tablename);
            }
        }
    }

    /**
     * Возвращает ассоциативный массив 1C-идентефикатор => free
     * @return array
     */
    public static function withdrawTrainings()
    {
        global $modx;

        $cache = new ScheduleCache();
        return $cache->fetch();
    }
	/**
	 * Возвращает из кеша количество свободных мест для указанного id тренировки
	 * @param string $training1CId
	 * @return void
	 */
    public function getTrainingFree($training1CId)
    {
        global $modx;

        $res = $modx->db->select("free", $this->tablename, "trainingid = '" . $training1CId . "';");

        if ($res === false) {
            return 0;
        }

        $value = $modx->db->getValue($res);

        if (!$value) {
            return 0;
        }

        return $value;
    }
};
?>
