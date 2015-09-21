<?php

	/*
	 * XXXTR: Document more here.
	 */
	include("credentials.inc");
	include("functions.php");

	if (!isset($_POST['host']) || (!isset($_POST['format']))) {
		print "You must submit a request type\n";
		exit (1);
	}

	/*
	 * Prevent injection attacks.
	 */
	$_host = $_POST['host'];
	$_format = $_POST['format'];
	if (IsInjected($_host) || (IsInjected($_format))) {
		print "Bad characters found in the string\n";
		exit (1);
	}

	/* Create the database class stuff */
	$mysqli = new mysqli($hostname, $username, $password, $database);
	if ($mysqli->connect_error) {
		printf("Error connecting to database $database: %s\n",
		$mysqli->connect_error);
		include("footer.inc");
		exit (1);
	}

	if (!$result = $mysqli->query("SELECT * FROM nagios_hosts")) {
		printf("Error selecting data from database: %s\n",
		$result->error);
		$mysqli->close();
		include("footer.inc");
		exit (1);
	}

	if ($result->num_rows <= 0) {
		print "We received zero lines from the db but was returned true.";
		$mysqli->close();
		include("footer.inc");
		exit (1);
	}

	$hostrawid = $mysqli->query("SELECT host_object_id FROM
	    nagios_hosts WHERE alias = '$_host'");
	$hostdata = $hostrawid->fetch_assoc();
	$hostid = "{$hostdata['host_object_id']}";

	$staterawid = $mysqli->query("SELECT * FROM
	    nagios_hoststatus WHERE host_object_id = '$hostid'");
	$statedata = $staterawid->fetch_assoc();
	$hoststate = "{$statedata['current_state']}";
	$hostoutput = "{$statedata['output']}";
	$hostlast_check = "{$statedata['last_check']}";
	$hostnext_check = "{$statedata['next_check']}";
	$hostlast_state_change = "{$statedata['last_state_change']}";
	$hostlast_hard_state_change = "{$statedata['last_hard_state_change']}";
	$hostlast_time_up = "{$statedata['last_time_up']}";
	$hostlast_time_down = "{$statedata['last_time_down']}";
	$mysqli->close();

	$_format = strtolower($_format);
	if (strcmp($_format, "xml") === 0) {
		/* Print XML output */
		print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		print "<host>\n";
		print "\t<hostid>$hostid</hostid>\n";
		print "\t<hoststatus>$hoststate</hoststatus>\n";
		print "\t<hostoutput>$hostoutput</hostoutput\n";
		print "\t<last_check>$hostlast_check</last_check>\n";
		print "\t<next_check>$hostnext_check</next_check>\n";
		print "\t<last_state_change>$hostlast_state_change</last_state_change>\n";
		print "\t<last_hard_state_change>$hostlast_hard_state_change</last_hard_state_change>\n";
		print "\t<last_time_up>$hostlast_time_up</last_time_up>\n";
		print "\t<last_time_down>$hostlast_time_down</last_time_down>\n";
		print "</host>\n";
	} else if (strcmp($_format, "json") === 0) {
		/* Print out JSON output */
		$jarray = array("hostid" => $hostid, "hoststatus" => $hoststate,
		    "hostoutput" => $hostoutput, "last_check" => $hostlast_check,
		    "next_check" => $hostnext_check,
		    "last_state_change" => $hostlast_state_change,
		    "last_hard_state_change" => $hostlast_hard_state_change,
		    "last_time_up" => $hostlast_time_up,
		    "last_time_down" => $hostlast_time_down);
		print json_encode($jarray);
		print "\n";
	} else {
		print "Host state is: $hoststate\n";
	}
	exit (0);
?>
