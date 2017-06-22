<?php

/**
 * Выводит на экран числа от 1 до 100. При этом вместо чисел, кратных трем, 
 * программа должна выводить слово «Fizz», а вместо чисел, кратных пяти — слово 
 * «Buzz». Если число кратно и 3, и 5, то программа должна выводить слово «FizzBuzz»
 */

/**
 * Генерирует исключения
 */
class FizzBuzzException extends Exception
{
    
}

/**
 * Генерирует ответ для задачи
 */
class FizzBuzz
{

    private $numberCycles;
    private $firstWord;
    private $secondWord;
    private $firstWordTrigger;
    private $secondWordTrigger;

    /**
     * Конструктор FizzBuzz
     * @param int $numberCycles количество итераций
     * @param string $firstWord первое слово
     * @param string $secondWord второе слово
     * @param int $firstWordTrigger номер итерации для вывода первого слова
     * @param int $secondWordTrigger номер итерации для вывода второго слова
     */
    function __construct(int $numberCycles, string $firstWord, string $secondWord, int $firstWordTrigger, int $secondWordTrigger)
    {

        if ($numberCycles <= 0) {
            throw new FizzBuzzException("Количество итераций не может быть меньше либо равно нулю");
        } else {
            $this->numberCycles = $numberCycles;
        }

        if ($firstWordTrigger <= 0 || $secondWordTrigger <= 0) {
            throw new FizzBuzzException("Кратность число не должна быть меньше нуля");
        } elseif ($firstWordTrigger > $numberCycles || $secondWordTrigger > $numberCycles) {
            throw new FizzBuzzException("Кратность не должна быть больше количества итераций");
        } else {
            $this->firstWordTrigger = $firstWordTrigger;
            $this->secondWordTrigger = $secondWordTrigger;
        }

        $this->firstWord = $firstWord;
        $this->secondWord = $secondWord;
    }

    /**
     * Запускает цикл
     * @return array - массив с результатам выполнения цикла
     */
    public function getCycleResult()
    {
        $cycleArray = array();

        // к $numberCycles прибавляется единица, чтобы было ровно 100 итераций, а не 99
        // $index = 1 чтобы отсчет начинался с 1, а не нуля
        for ($index = 1; $index < ($this->numberCycles + 1); $index++) {

            if ($index % $this->firstWordTrigger === 0 && $index % $this->secondWordTrigger === 0) {
                $cycleArray[] = $this->firstWord . $this->secondWord;
            } elseif ($index % $this->firstWordTrigger === 0) {
                $cycleArray[] = $this->firstWord;
            } elseif ($index % $this->secondWordTrigger === 0) {
                $cycleArray[] = $this->secondWord;
            } else {
                $cycleArray[] = $index;
            }
        }

        return $cycleArray;
    }

}

/**
 * Представление FizzBuzz
 */
class ViewFizzBuzz
{

    /**
     * Выводит на экран по строкам содержимое полученного массива
     * @param FizzBuzz $FizzBuzz
     * @throws FizzBuzzException
     */
    public static function printFizzBuzz(FizzBuzz $FizzBuzz)
    {
        // если скрипт запущен в командной строке, то концом строки будет символ 
        // соответствующий платформе
        if (php_sapi_name() === 'cli') {
            $lineBreakHTML = PHP_EOL;
        } else {
            $lineBreakHTML = '<br>';
        }

        $cycleArray = $FizzBuzz->getCycleResult();

        if (empty($cycleArray)) {
            throw new FizzBuzzException("Не смог получить информацию для отображения");
        }

        // $count = 1 потому что отсчет начинается не с нуля
        $count = 1;

        foreach ($cycleArray as $cycleString) {
            echo $count . ': ' . $cycleString . $lineBreakHTML;
            $count++;
        }
    }

    public static function printFizzBuzzCliHelp()
    {
        echo 'Скрипту необходимо передать 4 аргумента:' . PHP_EOL .
        'int $numberCycles количество итераций' . PHP_EOL .
        'string $firstWord первое слово' . PHP_EOL .
        'string $secondWord второе слово' . PHP_EOL .
        'int $firstWordTrigger номер итерации для вывода первого слова' . PHP_EOL .
        'int $secondWordTrigger номер итерации для вывода второго слова' . PHP_EOL;
    }

}

// если скрипт запускается из командной строки, то оттуда же берутся аргументы
if (!empty($argv)) {

    if ($argc === 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
        ViewFizzBuzz::printFizzBuzzCliHelp();
        exit();
    }

    if ($argc === 6) {
        $fizzbuzz = new FizzBuzz($argv[1], $argv[2], $argv[3], $argv[4], $argv[5]);
    } else {
        throw new FizzBuzzException("Аргументов должно быть не меньше пяти");
    }
} else {
    // если скрипт запускается откуда-то еще
    $fizzbuzz = new FizzBuzz(100, 'Fizz', 'Buzz', 3, 5);
}

ViewFizzBuzz::printFizzBuzz($fizzbuzz);
