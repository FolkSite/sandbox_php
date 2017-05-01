<?php
/*
 * Генерирует исключения
 */
class FizzBuzzException extends Exception
{
    
}

/**
 * Тут должно быть описание класса и аргументов
 */
class FizzBuzz
{

    private $numberCycles;
    private $firstWord;
    private $secondWord;
    private $firstWordTrigger;
    private $secondWordTrigger;

    function __construct(int $numberCycles, string $firstWord, string $secondWord, int $firstWordTrigger, int $secondWordTrigger)
    {
        $this->numberCycles = $numberCycles;
        $this->firstWord = $firstWord;
        $this->secondWord = $secondWord;
        $this->firstWordTrigger = $firstWordTrigger;
        $this->secondWordTrigger = $secondWordTrigger;
    }

    public function startCycle()
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
 * Тут должно быть описание класса и аргументов
 */
class ViewFizzBuzz
{

    public static function printFizzBuzz(FizzBuzz $FizzBuzz)
    {
        $cycleArray = $FizzBuzz->startCycle();

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
