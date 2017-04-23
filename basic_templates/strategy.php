<?php
/*
 * Пример шаблона Strategy и Composite
 */

abstract class Question
{

    protected $prompt;
    protected $marker;

    public function __construct($prompt, Marker $marker)
    {
        $this->marker = $marker;
        $this->prompt = $prompt;
    }

    function mark($response)
    {
        return $this->marker->mark($response);
    }

}

class TextQuestion extends Question
{
    // Выполняет действия, спецефичные для текстовых вопросов
}

class AVQuestion extends Question
{
    // Выполняет действия, спецефичные для мультимедийных (айдио- и видео-) вопросов 
}

abstract class Marker
{

    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

}

class MarkLogicMarker extends Marker
{

    private $engine;

    public function __construct($test)
    {
        parent::__construct($test);
        // $this->engine = new MarkParse($test);
    }

    function mark($response)
    {
        // return $this->engine->evaluate($response);
        // Возвратим фиктивное значение
        return true;
    }

}

class MatchMarker extends Marker
{

    function mark($response)
    {
        return ($this->test == $response);
    }

}

class RegexpMarker extends Marker
{

    function mark($response)
    {
        return (preg_match($this->test, $response));
    }

}

$markers = array(new RegexpMarker("/П.ть/"),
    new MatchMarker("Пять"),
    new MarkLogicMarker('$input equals "Пять'));

foreach ($markers as $marker) {
    echo get_class($marker) . '<br>';
    $question = new TextQuestion("Сколько лучей у Кремлевской звезды?", $marker);

    foreach (array("Пять", "Четыре") as $response) {
        echo "Ответ: $response: ";
        if ($question->mark($response)) {
            echo 'Правильно!<br>';
        } else {
            echo 'Неверно!<br>';
        }
    }
}


