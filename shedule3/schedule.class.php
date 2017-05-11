<?php 
/**
 * Объект хранящий расписание
 */
class Schedule implements Iterator {
	/**
	 * Массив комнат
	 * @var Room[]
	 */
	private $rooms;
	/**
	 * Текущая неделя
	 * @var boolean
	 */
	private $currentWeek;
	/**
	 * id клуба НЕ из 1С
	 * @var string
	 */
	private $clubId;
	/**
	 * Конструктор
	 * @param boolean $currentWeek
	 */
	public function __construct($currentWeek = true) {
		$this->rooms = array();
		$this->currentWeek = $currentWeek;
	}
	/**
	 * Инициализирует данные комнат и тренировок пришедших из 1С
	 * @param array $data Массив тренировок { "id": "c426b879-d64d-11e6-8b7e-001e6717c644", "club": "01", "timebeg": "2017-01-17T08:30:00", "timeend": "2017-01-17T09:30:00", "room": "Большой бассейн", "roomid": "0e221511-47f3-11e1-9f1f-001e6717c644", "type": "Аквааэробика AquaFit", "typeid": "60559560-48db-11e1-9f1f-001e6717c644", "coach": "Ситникова Анастасия Алексеевна", "capacity": 9, "reserv": 5, "booking": 6, "bookings": 1, "free": 0 }
	 * @return void
	 */	
	public function parseData($data) {	
		foreach($data as $val) {
			$room = $this->findRoom($val);
			if($room === false) {
				$room = new Room($val);	
				$this->rooms[] = $room;
			}
			$room->add(new Training($val));			
		}	

		foreach ($this->rooms as $room) {
			$room->buildCube();
		}

		$this->invertStartingTrainings();

		usort($this->rooms, array($this, 'compareRooms')); 
	}
	/**
	 * Сравнение комнат по имени
	 * @param Room $room1
	 * @param Room $room2
	 * @return boolean
	 */
	public function compareRooms($room1, $room2) {
		return $room1->getName() > $room2->getName();
	}
	/**
	 * Установка id клуба
	 * @param string $id
	 * @return void
	 */
	public function setClubId($id) {
		$this->clubId = $id;
	}
	/**
	 * Возвращает id клуба
	 * @return string
	 */
	public function getClubId() {
		return $this->clubId;
	}
	/**
	 * Cтартовые тренировки
	 * @return void
	 */
	public function invertStartingTrainings() {
		$clubsWorkingHours = array (
			'38666533-46cf-11e3-bccf-001e6717c644' => array ( // id зала с тренировками
					'weekdays' => '8:00-22:30',
					'saturday' => '9:00-20:00',
					'sunday' => '10:00-20:00',
					'club' => '1'
			),  // Качалова, 10
			'a4ccb408-8391-11e1-a122-001e6717c644' => array ( // id зала с тренировками
					'weekdays' => '7:00-23:00',
					'saturday' => '9:00-18:00',
					'sunday' => '9:00-18:00',
					'club' => '5'
			),  // 1-я Красноармейская
			'5471163b-46cf-11e3-bccf-001e6717c644' => array ( // id зала с тренировками
					'weekdays' => '8:00-22:30',
					'saturday' => '10:00-18:00',
					'sunday' => '10:00-18:00',
					'club' => '6'
			),  // Стахановская, 43 
			'6e6ce14c-46cf-11e3-bccf-001e6717c644' => array ( // id зала с тренировками
					'weekdays' => '9:00-22:00',
					'saturday' => '10:00-18:00',
					'sunday' => '10:00-18:00',
					'club' => '999'
			),  // Хабаровская, 56 // этого клуба уже нет
			'd1c1252c-2119-11e3-afae-001e6717c644' => array ( // id зала с тренировками
			//'5647af1b-f70d-11e5-8343-001e6717c644' => array(
					'weekdays' => '7:00-22:00',
					'saturday' => '10:00-18:00',
					'sunday' => '10:00-18:00',
					'club' => '7'
			),  // Запорожская, 1а
			'4c24cdf3-46cf-11e3-bccf-001e6717c644' => array ( // id зала с тренировками
					'weekdays' => '8:00-22:30',
					'saturday' => '10:00-18:00',
					'sunday' => '10:00-18:00',
					'club' => '9'
			)   // Лодыгина 9
		);

		foreach ($this->rooms as $key => $room) {
			if(isset($clubsWorkingHours[$room->getId()])) {
				$workingHours = new WorkingHours($clubsWorkingHours[$room->getId()]);
				$room->makeStartingTrainings($workingHours, $this->currentWeek);
				return;
			}
		}
		
		$newRoom = new Room(array(
				'room' => 'Стартовые тренировки'
			));	

		$workingHours = null;
		foreach ($clubsWorkingHours as $key => $hours) {
			if($hours['club'] == $this->clubId) {
				$workingHours = new WorkingHours($hours);
			}
		}


		if ($workingHours == null) {
			return;
		}

		$newRoom->buildCube();
		$newRoom->makeStartingTrainings($workingHours, $this->currentWeek);
		$this->rooms[] = $newRoom;	
	}
    /**
     * Обновить описание для каждой тренировкий во всех комнатах
     * @todo очень затратная по времени и ресурсам - делает много запросов, когда можно сделать одним
     * @return void
	 * @deprecated
     */
	public function updateDescription() {
		foreach($this->rooms as $room) {
			$room->updateDescription();
		}
	}
	/**
	 * Возвращает комнату если найдена
	 * @param array $arr {"roomid":"123","room":"321"}
	 * @return Room|false Комната если найдена, false если нет
	 */
	private function findRoom($arr) {
		if(array_key_exists('roomid', $arr)) {
			foreach($this->rooms as $room) {
				if($room->getId() == $arr['roomid']) {
					return $room;
				}
			}
		} else if(array_key_exists('room', $arr)) {
			foreach($this->rooms as $room) {
				if(strcmp($room->getName(),$arr['room']) === 0) {
					return $room;	
				}
			}
		} 		
		
		return false;
	}
	/**
	 * Возвращает в HTML все расписание
	 * @return string
	 */	
	public function toHTML() {
		$output = "";
		
		foreach($this->rooms as $room) {
			$output .= $room->toHTML();
		}	
		
		return $output;
	}
	/**
	 * Применение правил для всех комнат и тренировок
	 * @param TrainingRestriction[] $restrictions
	 * @return void
	 */
	public function applyRestrictions($restrictions) {
		foreach($this->rooms as $key => $room) {
			$room->applyRestrictions($restrictions);
		}
	}
	/**
	 * Обновляет количество свободных мест для каждой тренировки из кеша (из локальной БД), также каждую тренировку делает видимой и еще изменяет доступность каждой тренировки по формуле (free > 0 && isVisible)
	 * @todo смысл в этом кеше? то что это делает, мне не понятно
	 * @param ScheduleCache $cache
	 * @return void
	 */
	public function updateCache($cache) {
		foreach ($this->rooms as $room) {
			$room->updateCache($cache);
		}
	}
    /**
     * implementing Iterator interface
     * @return void
     */
	public function current() {
		return current($this->rooms);
	}
    /**
     * implementing Iterator interface
     * @return void
     */
	public function key() {
		return key($this->rooms);
	}
    /**
     * implementing Iterator interface
     * @return void
     */
	public function next() {
		return next($this->rooms);
	}
    /**
     * implementing Iterator interface
     * @return void
     */
	public function rewind() {
		return reset($this->rooms);
	}
    /**
     * implementing Iterator interface
     * @return void
     */
	public function valid() {
 		$key = $this->key();
		return ($key !== NULL && $key !== FALSE);
	}
}
?>

