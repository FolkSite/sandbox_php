<?php
    require_once("woo/base/Registry.php");
    $request = \woo\base\ApplicationRegistry::getRequest();
?>

<html>
    <head>
        <title>Добавление заведения</title>
    </head>
    
    <body>
        <h1>Добавление заведения</h1>
        
        <table>
            <tr>
                <td>
                    <?php
                        print $request->getFeedbackString("</td></tr><tr><td>");
                    ?>
                </td>
            </tr>
        </table>
        
        <form action="AddVenue.php" method="get">
            <input type="hidden" name="submitted" value="yes" />
            <input type="text" name="venue_name" />
        </form>
    </body>
    
</html>
