<?php

require_once 'settings.php';

class MySqlConnection {
    /********************************************/
    /*            PRIVATE VARIABLES             */
    /********************************************/
    private $queryResult;
    private $currentConnection;
    
    // Hold an instance of the class
    private static $instance;    
    
    /********************************************/
    /*             PUBLIC FUNCTIONS             */
    /********************************************/
    // The singleton method
    public static function singletonConstruct() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }
    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    public function __destruct() {
        // Free memory if not already freed
        // $this->freeResult();
        
        // Disconnect from mysql server
        mysql_close($this->currentConnection) or die(mysql_error());
    }
    public function getAssocArray() {
        return mysql_fetch_assoc($this->queryResult);
    }
    public function getQueryValue($rowNum) {
        return mysql_result($this->queryResult,$rowNum);
    }
    public function freeResult() {
        if ( $this->queryResult != NULL && is_resource($this->queryResult) ) {
            mysql_free_result($this->queryResult);
        }
    }
    public function resultIsEmpty() {
        if ( $this->queryResult ) {
            if ( $this->getNumRows() > 0 ) {
                return false;
            }
        }
        
        return true;
    }
    public function getNumRows() {
        return mysql_num_rows($this->queryResult);
    }
    public function getSafeStringArray(/* Comma-separated strings */) {
        // Store the number of arguments
        $numstrings = func_num_args();
        
        // Store the arguments in array
        $stringlist = func_get_args();
        
        // Cycle through each argument and make it safe!
        for ( $i = 0; $i < $numstrings; $i++ ) {
            $stringlist[$i] = mysql_real_escape_string($stringlist[$i]);
        }
        
        // Return safe string array in same order as input
        return $stringlist;
    }
    public function query($query) {
        $this->queryResult = mysql_query($query,$this->currentConnection);
        return $this->queryResult;
    }
    public function getSingleQueryValue($query) {
        $this->query($query);
        return $this->getQueryValue(0);
    }
    public function queryWasSuccessful() {
        if ( $this->queryResult == false ) {
            return false;
        }
        else {
            return true;
        }
    }
    /********************************************/
    /*            PRIVATE FUNCTIONS             */
    /********************************************/
    // A private constructor; prevents direct creation of object
    private function __construct() {
        // Connect to mysql server
        $this->connectToHost();
        
        // Connect to primary database
        $this->connectToDB();
    }
    private function connectToHost() {
        $this->currentConnection = mysql_connect(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD)or die(mysql_error());
    }
    private function connectToDB() {
        mysql_select_db(MYSQL_DB,$this->currentConnection)or die(mysql_error());
    }
}
?>