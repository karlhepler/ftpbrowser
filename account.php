<?php

require "mysqlconnection.php";

class Account {    
    
    // Construct and start session
    public function __construct() {
        if ( !isset($_SESSION) ) {
            session_start();
        }        
    }
    
    // Create a new account
    public function createNewAccount($username, $password, $directory, $account, $isadmin) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make variables safe
        $safeStrings = $mysql->getSafeStringArray($username,$password,$directory,$account);
        
        // Encrypt the password
        $safeStrings[1] = md5($safeStrings[1]);
        
        // Make sure the username is all lower case
        $safeStrings[0] = strtolower($safeStrings[0]);
        
        // Create database if it doesn't already exist
        $mysql->query("
            CREATE TABLE IF NOT EXISTS `accounts` (
                `id` int(4) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `username` varchar(100) NOT NULL,
                `password` varchar(100) NOT NULL,
                `directory` longtext NOT NULL,
                `account` varchar(500) NOT NULL,
                `isadmin` bool NOT NULL,
                `createdate` datetime DEFAULT NULL,
                `lastlogin` datetime DEFAULT NULL,
                UNIQUE (`username`)
            ) ENGINE=MyISAM AUTO_INCREMENT=1
        ");
        
        // Get current datetime
        $datetime = date("Y-m-d H:i:s");

        // Directory has to be fixed
        // $directory = str_replace('%2F', '/', $directory);
        
        // Insert into table
        $mysql->query("
            INSERT INTO `accounts`
            (`username`,`password`,`directory`,`account`,`createdate`,`isadmin`)
            VALUES
            ('".$safeStrings[0]."','".$safeStrings[1]."','".$safeStrings[2]."','".$safeStrings[3]."','".$datetime."','".$isadmin."')
        ");
        
        // Free memory
        $mysql->freeResult();
        
        return true;
    }
    
    // Delete an account with this accountID
    public function deleteThisAccount() {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Delete this account from MySQL
        $mysql->query("DELETE FROM `accounts` WHERE `id`='".$this->getID()."'");
        
        // Free memory
        $mysql->freeResult();
        
        // Log out
        $this->logout();
    }
    // Delete an account with specified id
    public function deleteAccountFromID($id) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        $safeStrings = $mysql->getSafeStringArray($id);
        
        // Delete this account from MySQL
        $mysql->query("DELETE FROM `accounts` WHERE `id`='".$safeStrings[0]."'");
        
        // Free memory
        $mysql->freeResult();
    }
    
    // Stops and destroys the current session
    public function logout() {
        session_unset(); 
        session_destroy();
    }
    
    // Returns the MYSQL ID for the account
    public function getID() {
        return $_SESSION['ftp_id'];
    }
    
    // Returns the default FTP directory
    public function getDirectory() {
        return $_SESSION['ftp_directory'];
    }
    
    // Returns the account name
    public function getAccount() {
        return $_SESSION['ftp_account'];
    }
    // Returns the account name if id is supplied
    public function getAccountFromID($id) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make strings safe
        $safeStrings = $mysql->getSafeStringArray($id);
        
        // Select the user that matches up
        $mysql->query("SELECT * FROM `accounts` WHERE `id`='".$safeStrings[0]."' LIMIT 1");
        
        // If there is data in the returned table, then the login is successful
        if ( $mysql->resultIsEmpty() == false ) {
            
            // Get the variables
            $result = $mysql->getAssocArray();
            
            // save the id
            $account = $result['account'];
            
            // Free memory
            $mysql->freeResult();
            
            return $account;
        }
        else {
            return false;
        }
    }
    
    // Returns true if the account is logged in
    public function isLoggedIn() {
        // If the session is registered...
        if ( isset($_SESSION['ftp_id']) ) {
            return true;
        }
        // If the session is NOT registered...
        else {
            return false;
        }
    }
    
    // Log in and start the session with username and password
    public function login($username,$password) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make strings safe
        $safeStrings = $mysql->getSafeStringArray($username,$password);
        
        // Encrypt password
        $safeStrings[1] = md5($safeStrings[1]);
        
        // Make sure the username is all lower case
        $safeStrings[0] = strtolower($safeStrings[0]);
        
        // Select the user that matches up
        $mysql->query("SELECT * FROM `accounts` WHERE `username`='".$safeStrings[0]."' AND `password`='".$safeStrings[1]."' LIMIT 1");
        
        // If there is data in the returned table, then the login is successful
        if ( $mysql->resultIsEmpty() == false ) {
            // Start Session
            session_start();
            
            // Get session variables!
            $result = $mysql->getAssocArray();
            
            // Register necessary session variables
            $_SESSION['ftp_id'] = $result['id'];
            $_SESSION['ftp_directory'] = $result['directory'];
            $_SESSION['ftp_account'] = $result['account'];
            $_SESSION['ftp_username'] = $result['username'];
            $_SESSION['ftp_password'] = $result['password'];
            $_SESSION['ftp_isadmin'] = $result['isadmin'];
            
            // record current date and time
            $datetime = date("Y-m-d H:i:s");
            $mysql->query("UPDATE `accounts` SET `lastlogin`='$datetime' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1");
            
            // Free memory
            $mysql->freeResult();
            
            return true;
        }
        // If there is no data, then the login isn't successful
        else {
            // Free memory
            $mysql->freeResult();
            
            return false;
        }
    }
    
    // Get username
    public function getUsername() {
        return $_SESSION['ftp_username'];
    }
    
    // Get password (it will already be in md5 form
    public function getPassword() {
        return $_SESSION['ftp_password'];
    }
    
    // Change the username
    public function setUsername($newusername) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make strings safe
        $safeStrings = $mysql->getSafeStringArray($newusername);
        
        // Make sure the username is all lower case
        $safeStrings[0] = strtolower($safeStrings[0]);
        
        if ( $mysql->query("UPDATE `accounts` SET `username`='$safeStrings[0]' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1") ) {
            $_SESSION['ftp_username'] = $safeStrings[0];
            // Free memory
            $mysql->freeResult();
            return true;
        }
        else {
            // Free memory
            $mysql->freeResult();
            return false;
        }
    }
    
    // Change the password
    public function setPassword($newpassword) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make strings safe
        $safeStrings = $mysql->getSafeStringArray($newpassword);
        
        // Encrypt password
        $safeStrings[1] = md5($safeStrings[0]);
        
        if ( $mysql->query("UPDATE `accounts` SET `password`='$safeStrings[0]' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1") ) {
            $_SESSION['ftp_password'] = $safeStrings[0];
            // Free memory
            $mysql->freeResult();
            return true;
        }
        else {
            // Free memory
            $mysql->freeResult();
            return false;
        }
    }
    
    // Change the default directory
    public function setDirectory($newdirectory) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make strings safe
        $safeStrings = $mysql->getSafeStringArray($newdirectory);
        
        // Make sure the username is all lower case
        $safeStrings[0] = strtolower($safeStrings[0]);
        
        if ( $mysql->query("UPDATE `accounts` SET `directory`='$safeStrings[0]' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1") ) {
            $_SESSION['ftp_directory'] = $safeStrings[0];
            // Free memory
            $mysql->freeResult();
            return true;
        }
        else {
            // Free memory
            $mysql->freeResult();
            return false;
        }
    }
    
    // Change the account name
    public function setAccount($newaccount) {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        // Make strings safe
        $safeStrings = $mysql->getSafeStringArray($newaccount);       
        
        if ( $mysql->query("UPDATE `accounts` SET `account`='$safeStrings[0]' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1") ) {
            $_SESSION['ftp_account'] = $safeStrings[0];
            // Free memory
            $mysql->freeResult();
            return true;
        }
        else {
            // Free memory
            $mysql->freeResult();
            return false;
        }
    }
    
    // Determine if this account is admin
    public function isAdmin() {
        return $_SESSION['ftp_isadmin'];
    }
    
    // Make this account admin
    public function makeAdmin() {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        if ( $mysql->query("UPDATE `accounts` SET `isadmin`='true' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1") ) {
            $_SESSION['ftp_isadmin'] = true;
            // Free memory
            $mysql->freeResult();
            return true;
        }
        else {
            // Free memory
            $mysql->freeResult();
            return false;
        }
    }
    
    // Unmake admin
    public function unmakeAdmin() {
        // Connect to MySql Database
        $mysql = MySqlConnection::singletonConstruct();
        
        if ( $mysql->query("UPDATE `accounts` SET `isadmin`='false' WHERE `id`='".$_SESSION['ftp_id']."' LIMIT 1") ) {
            $_SESSION['ftp_isadmin'] = false;
            // Free memory
            $mysql->freeResult();
            return true;
        }
        else {
            // Free memory
            $mysql->freeResult();
            return false;
        }
    }
    
    // List all accounts
    public function listAccounts() {
        if ( $this->isAdmin() ) {
            // Connect to MySql Database
            $mysql = MySqlConnection::singletonConstruct();
                        
            $result = $mysql->query("SELECT * FROM `accounts`");
            
            // If there is data in the returned table, then the login is successful
            if ( $mysql->resultIsEmpty() == false ) {

                // return true;
                $result = [];
                while ( $row = $mysql->getAssocArray() ) {
                    array_push($result, $row);
                }                

                // Free memory
                $mysql->freeResult();

                return $result;
            }
            // If there is no data, then the login isn't successful
            else {
                // Free memory
                $mysql->freeResult();

                return false;
            }
        }
        else {
            return false;
        }
    }
    
    // Change account information of an account id
    public function setAccountInfo($username,$password,$directory,$account) {
        if ( $this->isAdmin() ) {
            return true;
        }
        else {
            return false;
        }
    }
    
}

?>
