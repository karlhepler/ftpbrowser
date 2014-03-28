<?php
	require_once 'settings.php';
	require 'account.php';

	$account = new Account();	

	if ( $account->isLoggedIn() && $account->isAdmin() ) {
		if ( isset($_POST['account']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['directory']) ) {
			if ( $account->createNewAccount($_POST['username'],$_POST['password'],'/array1/Documents/'.$_POST['directory'],$_POST['account'],isset($_POST['admin'])) ) {
				header( 'Location: '.URL_ROOT.'/admin.php' );
			}
			else {
				echo "**** ERROR CREATING USER ****<br>-- PROBLEM CREATING DATABASE ENTRY --";
			}
		}
		else {
			echo "**** ERROR CREATING USER ****<br>-- POST VARIABLES DID NOT TRANSFER --";
		}		
	}
	else {
		echo "**** ERROR CREATING USER ****<br>-- NOT LOGGED IN AND NOT ADMIN --";
	}
?>