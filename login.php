<?php
  require_once 'settings.php';
  require 'account.php';

  // Instantiate the account
  $account = new Account();

  // If the account is logged in, then take them to the index
  if ( $account->isLoggedIn() ) {
    header( 'Location: '.URL_ROOT );
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
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title><?php echo URL_TITLE; ?> - Login</title>

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

    <div class="container">

      <form action="\" method="POST" class="form-signin" role="form">
        <h2 class="form-signin-heading"><?php echo URL_TITLE; ?> <br><small>Client Access Login</small></h2>
        <input type="text" class="form-control" name="username" placeholder="User Name" required autofocus>
        <input type="password" class="form-control" name="password" placeholder="Password" required>
        <input class="btn btn-lg btn-primary btn-block" type="submit" value="Sign in">
      </form>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>