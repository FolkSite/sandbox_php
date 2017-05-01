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
        $this->numberCycles = $numberCycles;
        $this->firstWord = $firstWord;
        $this->secondWord = $secondWord;
        $this->firstWordTrigger = $firstWordTrigger;
        $this->secondWordTrigger = $secondWordTrigger;
    }
    
    /**
     * Запускает цикл
     * @return array - массив с результатам выполнения цикла
     */
    public function getCycleResult()
    {
        $cycleArray = array();

        for ($index = 0; $index < $this->numberCycles; $index++) {

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
        $cycleArray = $FizzBuzz->getCycleResult();

        if (empty($cycleArray)) {
            throw new FizzBuzzException("Не смог получить информацию для отображения");
        }
        
        $count = 0;

        foreach ($cycleArray as $cycleString) {
            echo $count . ': ' . $cycleString . '<br>';
            $count++;
        }
    }

}

$fizzbuzz = new FizzBuzz(100, 'Fizz', 'Buzz', 3, 5);

ViewFizzBuzz::printFizzBuzz($fizzbuzz);
