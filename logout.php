<?php
	require_once 'settings.php';
	require 'account.php';

	// Instantiate the account
	$account = new Account();

	// If logged in, then log out
	if ( $account->isLoggedIn() ) {
		$account->logout();
	}

	// Take user to login page
	header( 'Location: '.URL_ROOT.'/login.php' );
?>