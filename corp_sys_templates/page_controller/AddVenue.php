<?php

namespace woo\controller;

// ...

abstract class PageController
{

    abstract function process();

    public function forward($resource)
    {
        include($resource);
        exit(0);
    }

    public function getRequest()
    {
        return \woo\base\ApplicationRegistry::getRequest();
    }

}

class AddVenueController extends woo\controller\PageController
{

    public function process()
    {
        try {
            $request = $this->getRequest();
            $name = $request->getProperty('venue_name');

            if (is_nan($request->getProperty('submitted'))) {
                $request->addFeedback("Выберите имя заведения");
                $this->forward('add_venue.php');
            } elseif (is_null($name)) {
                $request->addFeedback("Имя должно быть обязательно задано");
                $this->forward('add_venue.php');
            }
            
            // создадим объект, который затем можно добавить в БД
            $venue = new \woo\domain\Venue(null, $name);
            $this->forward("ListVenue.php");
        } catch (Exception $e) {
            $this->forward("error.php");
        }
    }

}

$controller = new AddVenueController();
$controller->process();
