<?php
require_once "settings.php";
require "ftpclass.php";
require "account.php";

ini_set('memory_limit', '999M');
ini_set('max_execution_time', 99999999);
ini_set('post_max_size','999M');
ini_set('upload_max_filesize','999M');
ini_set('max_input_time',99999999);
ini_set('default_socket_timeout',99999999);

$request = $_GET['r'];
$filename = utf8RawUrlDecode($_GET['f']);
$filesize = $_GET['s'];
$username = $_GET['u'];
$password = $_GET['p'];
$directory = $_GET['d'];

$newusername = $_GET['nu'];
$newpassword = $_GET['np'];
$newdirectory = utf8RawUrlDecode($_GET['nd']);
$newaccount = $_GET['nf'];
$newadmin = $_GET['na'];

$account = new Account();

switch ( $request ) {

        // Login
        case "login":
            if ( $account->isLoggedIn() ) {
                $account->logout();
            }
            if ( $account->login($username, $password) ) {
                echo 'success_login';
            }
            else {
                echo 'error_login';
            }
            break;

        case "logout":
            if ( $account->isLoggedIn() ) {
                $account->logout();
                echo 'success_logout';
            }
            else {
                echo 'error_logout';
            }
            break;
            

        // Directory/File List
        case "listfiles":
            // If not logged in, the break with error
           if ( $account->isLoggedIn() == false ) {
               echo 'error_login';
                break;
            }
            else {
                try {
                    // create new ftp object and login
                    $ftp = new Ftp;
                    $ftp->connect(FTP_HOST);
                    $ftp->login(FTP_USERNAME,FTP_PASSWORD);         

                    // list the directories and files as json
                    $newdir = str_replace('\\', '/', addslashes($account->getDirectory().$directory));
                    $ls = json_encode($ftp->dirList($newdir));          
                    print($ls);

                    // kill the ftp connection
                    $ftp->close();
                }
                catch (FtpException $e) {echo 'error_listfiles';}
                break;
            }

        // Download file
        case "downloadfile":
            // If not logged in, the break with error
            if ( $account->isLoggedIn() == false ) {
                echo 'error_login';
                break;
            }
            else {
                $newfilename = str_replace('\\', '/', addslashes($account->getDirectory().$filename));
                
                try {
                    if ( $filesize > 52428800 /* If the filesize is more than 50MB, then just redirect the header to the direct path */ ) {
                        header("Location: "."ftp://".FTP_USERNAME.":".FTP_PASSWORD."@".FTP_HOST."/".$newfilename);
                    }
                    else {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-Length: '.$filesize.';');
                        ob_clean();
                        flush();
                        //readfile("ftp://".FTP_USERNAME.":".FTP_PASSWORD."@".FTP_HOST."/".$newfilename);
                        readfile_chunked("ftp://".FTP_USERNAME.":".FTP_PASSWORD."@".FTP_HOST."/".$newfilename);
                    }
                }
                catch (FtpException $e) { echo 'error_downloadfile'; }
                break;
            }
            
        case "createaccount":
            if ( isset($newusername) && isset($newpassword) && isset($newdirectory) && isset($newaccount) && isset($newadmin) && $account->createNewAccount($newusername, $newpassword, $newdirectory, $newaccount, $newadmin) ) {
                print "ACCOUNT SUCCESSFULLY CREATED!<br /><br /><a href='/administrator/'>CLICK HERE</a> to return.";
            }
            else {
                print "ACCOUNT CREATION ERROR";
            }
            break;
}

function utf8RawUrlDecode ($source) {
    $decodedStr = "";
    $pos = 0;
    $len = strlen ($source);
    while ($pos < $len) {
        $charAt = substr ($source, $pos, 1);
        if ($charAt == '%') {
            $pos++;
            $charAt = substr ($source, $pos, 1);
            if ($charAt == 'u') {
                // we got a unicode character
                $pos++;
                $unicodeHexVal = substr ($source, $pos, 4);
                $unicode = hexdec ($unicodeHexVal);
                $entity = "&#". $unicode . ';';
                $decodedStr .= utf8_encode ($entity);
                $pos += 4;
            }
            else {
                // we have an escaped ascii character
                $hexVal = substr ($source, $pos, 2);
                $decodedStr .= chr (hexdec ($hexVal));
                $pos += 2;
            }
        } else {
            $decodedStr .= $charAt;
            $pos++;
        }
    }
    return $decodedStr;
}

function readfile_chunked($filename,$retbytes=true) { 
   $chunksize = 1*(1024*1024); // how many bytes per chunk 
   $buffer = ''; 
   $cnt =0; 
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
   } 
   while (!feof($handle)) { 
       $buffer = fread($handle, $chunksize); 
       echo $buffer;
       ob_flush(); 
       flush(); 
       if ($retbytes) { 
           $cnt += strlen($buffer); 
       } 
   } 
       $status = fclose($handle); 
   if ($retbytes && $status) { 
       return $cnt; // return num. bytes delivered like readfile() does. 
   } 
   return $status; 

} 
?>
