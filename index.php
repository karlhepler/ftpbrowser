<?php
  require_once "settings.php";
  require "ftpclass.php";
  require 'account.php';
  require 'helpers.php';

  // Set memory limits etc
  ini_set('memory_limit', '999M');
  ini_set('max_execution_time', 99999999);
  ini_set('post_max_size','999M');
  ini_set('upload_max_filesize','999M');
  ini_set('max_input_time',99999999);
  ini_set('default_socket_timeout',99999999);

  // Instantiate the account
  $account = new Account();

  // Check to make sure logged in - if not, redirect to login page
  if ( !$account->isLoggedIn() ) {
    // Check for post variables first
    if ( isset($_POST['username']) && isset($_POST['password']) ) {
      // Try to login - otherwise, redirect
      if ( !$account->login( $_POST['username'], $_POST['password'] ) )
        header( 'Location: '.URL_ROOT.'/login.php' );
    }
    else
      // If no post variables, then just redirect to login page
      header( 'Location: '.URL_ROOT.'/login.php' );
  }

  // Check to make sure this isn't requesting a file
  if ( !isset($_GET['dl']) ) {

    // GET THE FILE LIST! -----------------------------------------
    // Set the ftp directory as root for this user
    $dir = $account->getDirectory();

    // Add any folders requested
    if ( isset($_GET['ls']) ) $dir .= utf8RawUrlDecode($_GET['ls']);

    try {
        // create new ftp object and login
        $ftp = new Ftp();
        $ftp->connect(FTP_HOST);
        $ftp->login(FTP_USERNAME,FTP_PASSWORD);

        $files = $ftp->dirList($dir);

        // kill the ftp connection
        $ftp->close();
      }
      catch (FtpException $e) {echo 'Unable to list files';}
  ?>
  <!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="">
      <meta name="author" content="">
      <link rel="shortcut icon" href="favicon.ico">

      <title><?php echo URL_TITLE; ?> - File List</title>

      <!-- Bootstrap core CSS -->
      <link href="css/bootstrap.min.css" rel="stylesheet">

      <!-- Bootstrap theme -->
      <link href="css/bootstrap-theme.css" rel="stylesheet">

      <!-- Custom styles for this template -->
      <link href="css/style.css" rel="stylesheet">

      <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
    </head>

    <body>

      <!-- Fixed navbar -->
      <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>"><?php echo URL_TITLE; ?></a>
          </div>
          <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li class="active"><a href="/">File Access</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <?php
                if ( $account->isAdmin() ) {
                  ?><li><a href="admin.php">User Administration</a></li><?php
                }
              ?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $account->getAccount(); ?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li class="disabled"><a href="settings.php">Settings</a></li>
                  <li class="divider"></li>
                  <li><a href="logout.php">Log Out</a></li>
                </ul>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>

      <div class="container">

        <ol class="breadcrumb">
          <?php
            // Remove the home part of the directory
            $dir = substr($dir, strlen($account->getDirectory())+1);

            // Explode the directory
            $xDir = explode('/', $dir);

            // If there is only 1, then it's just the home...
            if ( count($xDir) == 0 || $xDir[0] == '' ) {
              // Echo ONLY Home and make it active
              ?><li class="active">Home</li><?php
            }
            else {
              // Echo Home
              ?><li><a href="/">Home</a></li><?php            
              // Remove the last element of the array (active dir)
              $last = array_pop($xDir);
              // Cycle through all of the others
              foreach ($xDir as $key => $value) {
                // Echo the directory name
                $imploded = implode( '/', array_slice($xDir,0,$key+1) );
                // Make sure there is a beginning slash
                if ( $imploded[0] != '/' )
                  $imploded = '/'.$imploded;

                ?><li><a href="?ls=<?php echo urlencode($imploded); ?>"><?php echo $value; ?></a></li><?php
              }          
            // Finally, output the last one...
          ?>
          <li class="active"><?php echo $last; ?></li>
          <?php
            // Now put the last one back
            array_push($xDir, $last);
          } ?>
        </ol>

        <table id="file-list" class="table table-hover">
          <tbody>
            <?php

              if ( count($xDir) > 0 )
                $dir = implode('/',$xDir).'/';

              // Make sure there is a beginning slash
              if ( $dir[0] != '/' )
                $dir = '/'.$dir;

              // List the folders first, then the files
              foreach ($files as $key => $file) {
                if ( $file['isDir'] ) {
                  ?>              
                    <tr class="ls" data-href="<?php echo urlencode($dir.$file['text']); ?>">
                      <td><span class="glyphicon glyphicon-folder-open"></span></td>
                      <td><?php echo $file['text']; ?></td>
                    </tr>
                  <?php
                }
              }
              foreach ($files as $key => $file) {
                if ( !$file['isDir'] ) {
                  ?>              
                    <tr class="dl" data-size="<?php echo $file['size']; ?>" data-href="<?php echo urlencode($dir.$file['text']); ?>">
                      <td><span class="glyphicon glyphicon-file"></span></td>
                      <td><?php echo $file['text']; ?></td>
                    </tr>
                  <?php
                }
              }
            ?>
          </tbody>
        </table>      

      </div> <!-- /container -->


      <!-- Bootstrap core JavaScript
      ================================================== -->
      <!-- Placed at the end of the document so the pages load faster -->
      <script src="js/jquery-1.11.0.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/main.js"></script>
    </body>
  </html>
<?php
  }
  // IT IS REQUESTING A FILE! DOWNLOAD IT!!!
  else {

    try {
        if ( $_GET['size'] > 52428800 /* If the _GET['size'] is more than 50MB, then just redirect the header to the direct path */ ) {
            header("Location: "."ftp://".FTP_USERNAME.FTP_PASSWORD."@".FTP_HOST.$account->getDirectory().$_GET['dl']);
        }
        else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($_GET['dl']).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: '.$_GET['size'].';');
            ob_clean();
            flush();
            readfile_chunked("ftp://".FTP_USERNAME.FTP_PASSWORD."@".FTP_HOST.$account->getDirectory().$_GET['dl']);
        }
    }
    catch (FtpException $e) { echo 'error_downloadfile'; }

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