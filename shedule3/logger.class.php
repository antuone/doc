<?php
/**
 * Объект для вывода ошибок
 */
class Logger
{
    /**
     * Буфер для ошибок
     * @var string
     */
    private static $buffer = "";
    /**
     * Разделитель ошибок
     * @var string
     */
    private static $delimeter = "\n";
    /**
     * Добавляет сообщение в буфер
     * @param string $message Текст сообщения
     * @return void
     */
    public static function debug($message)
    {
        if (DEBUG) {
            self::$buffer .= $message . self::$delimeter;
        }
    }
    /**
     * Возвращает строки с ошибками
     * @return string Строка буфера
     */
    public static function getDebugBuffer()
    {
        return self::$buffer;
    }
    /**
     * Печатает ошибку
     * @param string $message Строка которая выведится
     * @return void
     */
    public static function error($message)
    {
        echo "Error: " . $message;
    }
    /**
     * Очищает буфер с ошибками
     * @return void
     */
    public static function freeBuffer()
    {
        self::$buffer = "";
    }
};
?>
