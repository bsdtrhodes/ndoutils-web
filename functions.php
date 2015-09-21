<?php

	/*
	 * $Id$
	 *
	 * This file will contain functions to help us.
	 */

	date_default_timezone_set("America/New_York");

	/*
	 * This function checks for injections, used like:
	 * if (IsInjected($visitor_email)) or even
	 * if (IsInjected($visitor_submission))
	 * print "Bad data submitted!";
	 */
	function IsInjected($str) {
		$injections = array('(\n+)',
		    '(\r+)',
		    '(\t+)',
		    '(%0A+)',
		    '(%0D+)',
		    '(%08+)',
		    '(%09+)'
		    );
		$inject = join('|', $injections);
		$inject = "/$inject/i";
		/*
		 * We check for NOT false because a string is returned
		 * in the true case.
		 */
		if ((preg_match($inject, $str)) || (strpbrk($str, "'()[]*&^%$#;!<>/\"") !== false)) {
			/* print "$str contains invalid chars"; */
			return true;
		} else {
			return false;
		}
	}

	/* Hash a string with a prefix */
	function phasher($pstr) {
		$pfix = "Tr1-";
		$hashed_pw = sha1($pfix . $pstr);
		return $hashed_pw;
	}

