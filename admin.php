<?php
  require_once "settings.php";
  require "ftpclass.php";
  require 'account.php';

  // Instantiate the account
  $account = new Account();

  // Check to make sure logged in - if not, redirect to login page
  if ( !$account->isLoggedIn() ) {
    // If no post variables, then just redirect to login page
    header( 'Location: '.URL_ROOT.'/login.php' );
  }

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

      <title><?php echo URL_TITLE; ?> - User Administration</title>

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
              <li><a href="/">File Access</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <?php
                if ( $account->isAdmin() ) {
                  ?><li class="active"><a href="admin.php">User Administration</a></li><?php
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
        <?php
        if ( !$account->isAdmin() ) {
          ?>
          <div class="alert alert-danger">
            <strong>OOPS!</strong> You need to be an administrator to view this page!
          </div>
          <?php
        }
        else {
        ?>
          <div class="row">
            <!-- LEFT COLUMN - CREATE USER -->
            <div class="col-sm-6">
              <form class="form-horizontal" role="form" action="create_account.php" method="post">
                <!-- Account Name -->
                <div class="form-group">
                  <label for="account" class="col-sm-2 control-label">Account Name</label>
                  <div class="col-sm-10">
                    <input name="account" type="text" class="form-control" id="account" placeholder="Account Name" required>
                  </div>
                </div>
                <!-- User Name -->
                <div class="form-group">
                  <label for="username" class="col-sm-2 control-label">User Name</label>
                  <div class="col-sm-10">
                    <input name="username" type="text" class="form-control" id="username" placeholder="User Name" required>
                  </div>
                </div>
                <!-- Password -->
                <div class="form-group">
                  <label for="password" class="col-sm-2 control-label">Password</label>
                  <div class="col-sm-10">
                    <input name="password" type="text" class="form-control" id="password" placeholder="Password" required>
                  </div>
                </div>
                <!-- FTP Directory -->
                <div class="form-group">
                  <label for="directory" class="col-sm-2 control-label">FTP Directory</label>
                  <div class="col-sm-10">
                    <select name="directory" id="directory" class="form-control" required>
                      <?php
                        try {
                          // create new ftp object and login
                          $ftp = new Ftp();
                          $ftp->connect(FTP_HOST);
                          $ftp->login(FTP_USERNAME,FTP_PASSWORD);

                          $files = $ftp->dirList('/array1/Documents/'.$dir);

                          // kill the ftp connection
                          $ftp->close();
                        }
                        catch (FtpException $e) {echo 'Unable to list files';}

                        // List the folders first, then the files
                        foreach ($files as $key => $file) {
                          if ( $file['isDir'] ) {
                            ?>
                              <option value="<?php echo $file['text']; ?>"><?php echo $file['text']; ?></option>
                            <?php
                          }
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <!-- Administrator -->
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                      <label>
                        <input name="admin" type="checkbox"> Make Administrator
                      </label>
                    </div>
                  </div>
                </div>
                <!-- SUBMIT -->
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <input type="submit" class="btn btn-default" value="Create User">
                  </div>
                </div>
              </form>
            </div>
            <!-- RIGHT COLUMN - LIST USERS -->
            <div class="col-sm-6">
              <?php
                $accounts = $account->listAccounts();
                if ( count($accounts) > 0 ) {
                  ?>
                    <table id="account-list" class="table table-hover">
                      <thead>
                        <tr>
                          <th></th>
                          <th>Account Name</th>
                          <th>User Name</th>
                          <th>FTP Directory</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          foreach ($accounts as $key => $account) {
                            ?>
                              <tr>
                                <td><?php echo $account['isadmin'] ? '<span class="glyphicon glyphicon-lock"></span>' : ''; ?></td>
                                <td><?php echo $account['account']; ?></td>
                                <td><?php echo $account['username']; ?></td>
                                <td><?php echo substr($account['directory'], 17); ?></td>
                                <td><a onclick="return conaccount('Are you sure you want to delete this account?');" href="delete_account.php?id=<?php echo $account['id']; ?>"><span class="glyphicon glyphicon-remove"></span></a></td>
                              </tr>
                            <?php
                          }
                        ?>
                      </tbody>
                    </table>
                  <?php
                }
              ?>
            </div>
          </div>
        <?php } ?>
      </div> <!-- /container -->


      <!-- Bootstrap core JavaScript
      ================================================== -->
      <!-- Placed at the end of the document so the pages load faster -->
      <script src="js/jquery-1.11.0.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/main.js"></script>
    </body>
  </html>