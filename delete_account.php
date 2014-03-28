<?php
	require_once 'settings.php';
	require 'account.php';

	// Instantiate the account
	$account = new Account();

	if ( $account->isLoggedIn() && $account->isAdmin() ) {
		if ( isset($_GET['id']) ) {
			// Delete the account
			$account->deleteAccountFromID($_GET['id']);
		}

		// Redirect back to admin section
		header( 'Location: '.URL_ROOT.'/admin.php' );
	}
	else {
		// Redirect to login
		header( 'Location: '.URL_ROOT.'/login.php' );
	}
?>