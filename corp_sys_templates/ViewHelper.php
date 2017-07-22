<?php

namespace woo\view;

class ViewHelper
{

    static function getRequest()
    {
        return \woo\base\ApplicationRegistry::getRequest();
    }

}

?>

<?php

/*
 * Пример представления
 */

require_once ("woo/view/ViewHelper.php");
$request = \woo\view\ViewHelper::getRequest();
$venue = $request->getObjext('venue');

?>

<title><?php echo $venue->getName(); ?></title>