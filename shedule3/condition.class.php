<?php
/**
 * Объект для проверки правил расписания
 */

class Condition
{
    /**
     * Выражение в формате PHP для проверки правила
     * @var string
     */
    private $expression;
    /**
     * Буфер для ошибок
     * @var string
     */
    private $errors;
    /**
     * Констуктор объекта
     * @param string $expression Строка в формате PHP для проверки правила
     */
    public function __construct($expression)
    {
        $this->expression = "return " . $expression . ";";
    }
    /**
     * Добавляет текст ошибки в буфер
     * @param string $message Сообщение об ошибке
     * @return void
	 * @deprecated
     */
    private function error($message)
    {
        $this->errors .= $message . '<br>';
    }
    /**
     * Проверяет равно ли выражение правила "false"
     * @return boolean true - выражение равно строке "false", false - не равно
     */
    public function isSimple()
    {
        return $this->expression == "false";
    }
    /**
     * Возвращает буфер с ошибками и очищает его
     * @return string Строка с текстом ошибок
	 * @deprecated
     */
    public function flushErrors()
    {
        $res = $this->errors;
        $this->errors = "";
        return $res;
    }
    /**
     * Выполняет правило и возвращает результат. Если есть ошибки, то выводит их.
     * @todo возможно использовалась для тестирования - все даты на сегодняшний день
     * @return boolean true - расписание можно делать доступным для записи, false - нет
	 * @deprecated
     */
    public function parseExpression()
    {
        ob_start();

        $now = new Date();
        $day = new Date();

        $res = eval($this->expression);

        $error = ob_get_contents();

        ob_end_clean();

        if (!empty($error)) {
            $this->error($error);
            return false;
        }

        return true;
    }
    /**
     * Эта функция определяет - будет ли тренировка с карандашем. Выполняет правило и возвращает результат.
     * @param Date $date Дата на которую хотите проверить правило
     * @return boolean true - расписание можно делать доступным для записи, false - нет
     */
    public function check($date)
    {
        ob_start();

        $now = new Date();
        $day = $date;

        $res = eval($this->expression);

        ob_end_clean();

        return $res;
    }
};
?>