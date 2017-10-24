<?php
// Код для добавления новых объектов к БД
require_once ("woo/domain/Venue.php");
require_once ("woo/domain/Space.php");

$venue = new \woo\domain\Venue(null, "The Green Trees");
$venue->addSpace(
        new \woo\domain\Space(null, "The Space Upstairs")
);
$venue->addSpace(
        new \woo\domain\Space(null, "The Bar Stage")
);
\woo\domain\ObjectWatcher::instance()->performOperations();
