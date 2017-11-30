<?PHP
/* Singleton module for the DNA Framework system
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2016
 */
abstract class Singleton {
    protected static $instances = array();

    /**
     * @static
     * @return Self
     */
    public static function getInstance() {
        if (!isset(self::$instances[get_called_class()]) ) {
            $class = get_called_class();
            self::$instances[get_called_class()] = new $class;
        }

        return self::$instances[get_called_class()];
    }
}
?>