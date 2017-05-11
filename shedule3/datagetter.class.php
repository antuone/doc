<?php
/**
 * Объект для получения расписания и других данных из БД 1С .
 */
class DataGetter
{
    /**
     * host у которого запрашиваются данные
     * @var string
     */
    private static $HOST = 'http://mail.bodyboom.ru:81';
    /**
     * Массив массивов url и ключи данных в ответе
     * @var array
     */
    private static $ACTIONS = array(
        'sheduleadv' => array(
            'url' => '/bb/hs/data/sheduleadv',
            'data' => 'shedule',
        ),
        'clubs' => array(
            'url' => '/bb/hs/data/clubs',
            'data' => 'clubs',
        ),
        'schedule' => array(
            'url' => '/bb/hs/data/shedule',
            'data' => 'shedule',
        ),
        'checkemail' => array(
            'url' => '/bb/hs/data/checkemail',
            'data' => array(
                'isemailcorrect',
                'isemailexist',
            ),
        ),
        'checkphone' => array(
            'url' => '/bb/hs/data/checkemail',
            'data' => array(
                'isemailcorrect',
                'isemailexist',
                'istelcorrect',
            ),
        ),
        'regemail' => array(
            'url' => '/bb/hs/data/regemail',
            'data' => 'response',
        ),
        'unregemail' => array(
            'url' => '/bb/hs/data/unregemail',
            'data' => 'response',
        ),
        'training' => array(
            'url' => '/bb/hs/data/sheduleadv',
            'data' => 'shedule',
        ),
        'regvisit' => array(
            'url' => '/bb/hs/data/regvisit',
            'data' => 'response',
        ),
    );
    /**
     * Хранит параметры для запроса
     * @var array
     */
    private $urlData;
    /**
     * Дата и время
     * @var string
     */
    private $dateTime;
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->dateTime = new Date();
        $this->dateTime->setTime(0, 0, 0);

        // базовые параметры
        $this->urlData = array(
            'api' => 'test',
        );

        $this->bounds = array(
            'start' => '',
            'end' => '',
        );
    }
    /**
     * Устанавливает параметр club для запроса данных
     * @param string $clubId Номер клубной карты
     * @return void
     */
    public function setClub($clubId)
    {
        $clubId = (int) $clubId;
        if ($clubId < 10) {
            $clubId = '0' . $clubId;
        }
        $this->urlData['club'] = $clubId;
    }
    /**
     * Устанавливает параметр id для запроса данных
     * @param string $trainingId ID Тренировки из 1С
     * @return void
     */
    public function setTraining($trainingId)
    {
        $this->urlData['id'] = $trainingId;
    }
    /**
     * Устанавливает параметр data для запроса данных
     * @param Data $date Дата с начала которой будет осуществлятся поиск расписания тренировок
     * @return void
     */
    public function setDate($date)
    {
        $this->urlData['date'] = $date->toString();
    }
    /**
     * Устанавливает текущую дату и время 00:00:00 в параметр updated для запроса данных
     * @return void
     */
    public function setUpdated()
    {
        $now = new Date();
        $now->setTime(0, 0, 0);
        $this->urlData['updated'] = $now->toString();
    }
    /**
     * Выполняет запрос в зависимости от установленных параметров
     * @todo Зря наверно сделали одну функцию для разных запросов, запутатся можно и легко допустить ошибку (Одна функция на основе какой-то логики выдает разные результаты - надо разделить по разным)
     * @param string $action Параметр в зависимости от которого будет осуществлятся запрос. Смотреть массив $ACTIONS
     * @return array|bool|string ?
     */
    private function performRequest($action)
    {
        $url = self::$HOST . self::$ACTIONS[$action]['url'];

        if (count($this->urlData)) {
            $url .= '?' . http_build_query($this->urlData);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($res, true);

        if ($action == 'regvisit') {
            return $data;
        }

        $key = self::$ACTIONS[$action]['data'];

        if ($key === 'response') {
            return $data['response'];
        }

        if ($data['response'] == 0) {
            return false;
        }

        if (is_array($key)) {
            $output = array();

            foreach ($key as $k) {
                $output[$k] = $data[$k];
            }
            return $output;
        }

        if (isset($data[$key])) {
            return $data[$key];
        } else {
            return true;
        }
    }
    /**
     * Устанавливает параметры для запроса:
     * промежуток дат и времени для расписания
     * @param Data $dateStart Начало даты для запроса расписания
     * @param Data $dateEnd Конец даты для запроса расписания
     * @return void
     * @deprecated
     */
    public function setBounds($dateStart, $dateEnd)
    {
        $this->bounds['start'] = $dateStart;
        $this->bounds['end'] = $dateEnd;
    }
    /**
     * Устанавливает параметры для запроса:
     * промежуток дат и времени для расписания на эту неделю
     * @return void
     * @deprecated
     */
    public function setBoundsToThisWeek()
    {
        $weekStart = new Date();
        $weekStart->setTimestamp(strtotime("monday this week"));
        $weekStart->setTime(0, 0, 0);

        $weekEnd = new Date();
        $weekEnd->setTimestamp(strtotime("sunday this week"));
        $weekEnd->setTime(0, 0, 0);

        $this->setBounds($weekStart, $weekEnd);
    }
    /**
     * Устанавливает параметры для запроса:
     * промежуток дат и времени для расписания на следующую неделю
     * @return void
     * @deprecated
     */
    public function setBoundsToNextWeek()
    {
        $weekStart = new Date();
        $weekStart->setTimestamp(strtotime("monday next week"));
        $weekStart->setTime(0, 0, 0);

        $weekEnd = new Date();
        $weekEnd->setTimestamp(strtotime("sunday next week"));
        $weekEnd->setTime(0, 0, 0);

        $this->setBounds($weekStart, $weekEnd);
    }
    /**
     * Функция для получения расписания
     * @todo Видимо устаревшая функция. Запрашивает данные несколько раз за каждый день. Возможно раньше не была реализована функция запроса на сервере сразу за несколько дней
     * @param string $action Параметр в зависимости от которого будет осуществлятся запрос. Смотреть массив $ACTIONS
     * @return array Расписание в виде массива
     * @deprecated
     */
    public function getDataWithinBounds($action)
    {
        $result = array();
        $temp = 0;
        for ($date = $this->bounds['start']; $date <= $this->bounds['end']; $date->modify('+1 day')) {
            $this->setDate($date);
            $temp++;
            $response = $this->performRequest($action);
            if ($response === false) {
                continue;
            }
            $result = array_merge($result, $response);
            $t = count($result);
        }
        if (count($result) === 0) {
            return false;
        }
        return $result;
    }
    /**
     * Устанавливает параметр для запроса - количество дней расписания
     * @param integer $days Количество дней, начиная с параметра date, для которых будет запрашиватся расписание
     * @return void
     */
    private function setDaysCount($days)
    {
        $this->urlData['days'] = $days;
    }
    /**
     * Устанавливает параметр для запроса - номер карты
     * @param string $number Номер клубной карты
     * @return void
     */
    private function setCardNumber($number)
    {
        $this->urlData['cardnumber'] = $number;
    }
    /**
     * Устанавливает параметр для запроса - email
     * @param string $email Адрес электронной почты
     * @return void
     */
    private function setEmail($email)
    {
        $this->urlData['email'] = $email;
    }
    /**
     * Устанавливает параметр для запроса - номер телефона
     * @param string $phone Номер телефона
     * @return void
     */
    private function setPhone($phone)
    {
        $this->urlData['tel'] = $phone;
    }
    /**
     * Функция для получения расписания
     * @link http://mail.bodyboom.ru:81/bb/hs/data/sheduleadv?api=test&club=1&days=14&date=2017-01-17T00%3A00%3A00
     * @param string $clubId ID клуба
     * @param string $week "this week" or "next week"
     * @return Schedule Расписание тренировок
     * @deprecated
     */
    public static function scheduleadv($clubId, $week = '')
    {
        $getter = new DataGetter();
        $getter->setClub($clubId);
        $date = new Date();
        $date->setTime(0, 0, 0);
        $getter->setDaysCount(7);

        if ($week == 'this week') {

        } elseif ($week == 'next week') {
            $date->modify('+7 day');
        } else {
            $getter->setDaysCount(14);
        }

        $getter->setDate($date);

        $result = $getter->performRequest('sheduleadv');

        if ($result === false) {
            return $result;
        }

        $schedule = new Schedule();
        $schedule->setClubId($clubId);
        $schedule->parseData($result);
        $schedule->updateDescription();
        return $schedule;
    }
    /**
     * Функция для получения клубов
     * @link http://mail.bodyboom.ru:81/bb/hs/data/clubs?api=test&updated=2017-01-17T00%3A00%3A00
     * @todo Запрос требует параметра updated, причем неважно какую дату отправлять - результат один
     * @return array [{"code": "01", "adress" : "Качалова, 10", "phone1" : "2 276 276", "phone2" : "", "color" : "#-1-1-1", "updated" : "2016-10-06T11:32:56"}]
     */
    public static function clubs()
    {
        $getter = new DataGetter();
        $getter->setUpdated();
        $result = $getter->performRequest('clubs');

        $clubs = array();

        if ($result === false) {
            return $clubs;
        }

        foreach ($result as $key => $value) {
            $clubs[] = $value;
        }

        return $clubs;
    }
    /**
     * Функция для получения расписания
     * @param string $clubId ID клуба
     * @param string $week "this week" or "next week"
     * @return Schedule Расписание тренировок
     * @deprecated
     */
    public static function schedule($clubId, $week)
    {
        $getter = new DataGetter();
        $getter->setClub($clubId);
        $getter->setUpdated();

        if ($week == 'next week') {
            $getter->setBoundsToNextWeek();
        } else {
            $getter->setBoundsToThisWeek();
        }

        $result = $getter->getDataWithinBounds('schedule');

        if ($result === false) {
            return $result;
        }

        if ($week == 'next week') {
            $currentWeek = false;
        } else {
            $currentWeek = true;
        }

        $schedule = new Schedule($currentWeek);
        $schedule->setClubId($clubId);
        $schedule->parseData($result);
        $schedule->updateDescription();

        return $schedule;
    }
    /**
     * Функция для получения расписания
     * (Рекомендую использовать эту)
     * @param string $clubId ID клуба
     * @param string $week "this week" or "next week"
     * @return Schedule Расписание тренировок
     * @author anton <asikuo@gmail.com>
     */
    public static function schedule2($clubId, $week = 'this week')
    {
        $getter = new DataGetter();
        $getter->setClub($clubId);
        $getter->setDaysCount(7);

        $date = new Date();

        if ($week == 'this week') {
            $date->setTimestamp(strtotime("monday this week"));
        } elseif ($week == 'next week') {
            $date->setTimestamp(strtotime("monday next week"));
        }
        $date->setTime(0, 0, 0);

        $getter->setDate($date);

        $result = $getter->performRequest('sheduleadv');

        if ($result === false) {
            return $result;
        }

        $schedule = new Schedule();
        $schedule->setClubId($clubId);
        $schedule->parseData($result);
        $schedule->updateDescription();
        return $schedule;
    }
    /**
     * Проверка существования такой почты с таким номером карты
     * @link http://mail.bodyboom.ru:81/bb/hs/data/checkemail?api=test&cardnumber=0007000000153&email=myargut@mail.ru
     * @param string $email Электронная почта
     * @param string $cardNumber Номер клубной карты
     * @return array {"isemailcorrect" : 1, "isemailexist" : 1} или {}
     */
    public static function checkEmail($email, $cardNumber)
    {
        $getter = new DataGetter();
        $getter->setEmail($email);
        $getter->setCardNumber($cardNumber);
        return $getter->performRequest('checkemail');
    }
    /**
     * Проверка существования почты и номером телефона с таким номер клубной карты
     * @link http://mail.bodyboom.ru:81/bb/hs/data/checkemail?api=test&cardnumber=0007000000153&email=myargut@mail.ru&tel=4994
     * @param string $email Адрес электронной посты
     * @param string $cardNumber Номер клубной карты
     * @param string $phone Номер телефона
     * @return array {"isemailcorrect" : 1, "isemailexist" : 1, "istelcorrect" : 1} or {}
     */
    public static function checkPhone($email, $cardNumber, $phone)
    {
        $getter = new DataGetter();
        $getter->setEmail($email);
        $getter->setCardNumber($cardNumber);
        $getter->setPhone($phone);
        return $getter->performRequest('checkphone');
    }
    /**
     * Регистрация почты
     * @link http://mail.bodyboom.ru:81/bb/hs/data/regemail?api=test&cardnumber=0007000000153&email=myargut@mail.ru
     * @param string $email Адрес электронной почты
     * @param string $cardNumber Номер клубной карты
     * @return bool true - зарегистрированно, false - нет
     */
    public static function regemail($email, $cardNumber)
    {
        $getter = new DataGetter();
        $getter->setEmail($email);
        $getter->setCardNumber($cardNumber);
        return $getter->performRequest('regemail');
    }
    /**
     * Метод отвязывает email, который был привязан в 1С к карте $cardNumber
     * @link http://mail.bodyboom.ru:81/bb/hs/data/unregemail?api=test&cardnumber=0007000000153
     * @todo !!!!! Не для использования. Только для разработки отсального функционала.
     * @param string $cardNumber Номер клубной карты
     * @return bool true - отвязалось, false - нет
     * @deprecated
     */
    public static function unregemail($cardNumber)
    {
        $getter = new DataGetter();
        $getter->setCardNumber($cardNumber);
        return $getter->performRequest('unregemail');
    }
    /**
     * Получение данных об тренировке
     * @link http://mail.bodyboom.ru:81/bb/hs/data/sheduleadv?api=test&club=01&date=2016-11-09T00:00:00&id=17d7d425-a62a-11e6-8b7e-001e6717c644
     * @param string $clubId Номер клубной карты
     * @param string $trainingId ID тренировки
     * @param string $timestamp Дата и время тренировки в виде временного штампа
     * @return array [0]{"id":"17d7d425-a62a-11e6-8b7e-001e6717c644","club":"01","timebeg":"2016-11-09T08:00:00","timeend":"2016-11-09T09:00:00","room":"Кардио-силовой тренажерные залы","roomid":"0e22150e-47f3-11e1-9f1f-001e6717c644","type":"Персональное занятие тренажерный зал","typeid":"3fe9c739-1921-11e4-8033-001e6717c644","coach":"Иваняшина Юлия Вячеславовна","capacity":0,"reserv":0,"booking":0,"bookings":0,"free":0}
     * @todo нужно доделать получения актуальной информации о старотовой тренировки, на основе данных из 1с, чтобы не просто отдавать количество мест = 1
     */
    public static function training($clubId, $trainingId, $timestamp)
    {
        $date = new Date();
        $date->setTimestamp($timestamp);
        $date->setTime(0, 0, 0);
        $getter = new DataGetter();
        $getter->setClub($clubId);
        $getter->setDate($date);
        $getter->setTraining($trainingId);
        $res = $getter->performRequest('training');

        if ($res === false) {
            return false;
        }

        if (!isset($res[0])) {
            return false;
        }

        $training = new Training($res[0]);

        return $training;
    }
    /**
     * Записать на тренировку
     * @link http://mail.bodyboom.ru:81/bb/hs/data/regvisit?api=test&cardnumber=0007000000153&id=17d7d425-a62a-11e6-8b7e-001e6717c644&club=01&date=2016-11-09T00:00:00
     * @param string $clubId ID клуба
     * @param string $cardNumber Номер клубной карты
     * @param string $trainingId ID тренировки
     * @param string $timestamp Дата и время тренировки в виде временного штампа
     * @return bool true - запись удалась, false - нет
     */
    public static function enroll($clubId, $cardNumber, $trainingId, $timestamp)
    {
        $date = new Date();
        $date->setTimestamp($timestamp);
        $getter = new DataGetter();
        $getter->setClub($clubId);
        $getter->setCardNumber($cardNumber);
        $getter->setTraining($trainingId);
        $getter->setDate($date);

        $res = $getter->performRequest('regvisit');
        return $res;
    }
}
?>