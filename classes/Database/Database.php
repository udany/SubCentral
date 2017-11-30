<?PHP
/* Database module
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2014
 */
class Database extends Singleton {
    /**
     * @static
     * @return Database
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    public static $DefaultAddress = '';
    public static $DefaultUser = '';
    public static $DefaultPassword = '';
    public static $DefaultDatabase = '';
	public static $IgnoreTimezone = false;

    private $dumpfile = "";

    private static $db_handler;

    protected function __construct() {
        $this->dumpfile = GetDynamicDirectory() . date("Y_m_d") . "_querydump.txt";

        if (!self::$DefaultDatabase && defined('DATABASE_NAME')) self::$DefaultDatabase = DATABASE_NAME;

        // DB connect
        try {
            self::$db_handler = new PDO("mysql:host=".self::$DefaultAddress.";dbname=".self::$DefaultDatabase, self::$DefaultUser, self::$DefaultPassword);
        } catch (PDOException $e) {
            throw new Exception("Can't connect to the database " . DEBUG_ENABLED ? (self::$DefaultAddress . " with " . self::$DefaultUser . "/" . self::$DefaultPassword . ' - ' . $e->getMessage()) : "",503.1);
        }

        $this->Query("SET CHARACTER SET utf8");
	    
	    if (!self::$IgnoreTimezone)
	        $this->Query("SET time_zone = 'UTC'");
    }

    /**
     * @param $sql string
     * @param $args array
     * @param $select bool
     * @return resource|array
     */
    public function Query ($sql, $args=null, $select = false){
        if (is_array($args)){
            $sql = DNAParser::getInstance()->Format($sql, $args);
        }

        $statement = $this->Prepare($sql);

        return $this->Run($statement, null, $select);
    }

    public function Prepare($sql){
        return self::$db_handler->prepare($sql);
    }

    /**
     * @param $statement string|PDOStatement
     * @param $values
     * @param $select bool
     * @return mixed
     * @throws Exception
     */
    public function Run($statement, $values=null, $select=false){
        if (!($statement instanceof PDOStatement)){
            $statement = $this->Prepare($statement);
        }

        if (QUERY_DUMP_ENABLED) LogMessage($statement->queryString . ($values ? "\n".json_encode($values, JSON_PRETTY_PRINT) : ""), $this->dumpfile);

	    $status = @$statement->execute($values);

        if (!$status) {

            LogMessage('SQL ERROR: ' . implode(" | ", $statement->errorInfo()) . "\n".
                "Original SQL: " . $statement->queryString . ($values ? "\n".json_encode($values, JSON_PRETTY_PRINT) : ""),
	            '', true);
            throw new Exception("SQL ERROR - Check the log for details");
        }

        if ($select){
            $list = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $list;
        }else{
            return $statement->rowCount();
        }
    }


    /**
     * @return int
     */
    public function GetLastId(){
        return self::$db_handler->lastInsertId();
    }

    function Export($file, $options='',$path=''){
        $str = MYSQL_BIN.'mysqldump --user={0} {1} --host={2} {3}{4} > {5}';
        $str = DNAParser::getInstance()->Format($str,
            [
                self::$DefaultUser,
                self::$DefaultPassword ? '--password='.self::$DefaultPassword : '',
                self::$DefaultAddress,
                self::$DefaultDatabase,
                $options ? ' '.$options : '',
                $path."DbBackup/".$file]);
	    echo $str;
	    die();
	    //exec($str, $out);
	    //print_r($out);
    }
}
?>