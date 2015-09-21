<?php

	/*
	 * XXXTR: Document more here.
	 */
	include("credentials.inc");
	include("functions.php");

	/*
	 * Verify we have data.  We must get a host and/or service.
	 */
	if ((!isset($_POST['host'])) || (!isset($_POST['service'])) ||
	    (!isset($_POST['format']))) {
		print "You must submit a request type\n";
		exit (1);
	}

	$_host = $_POST['host'];
	$_svs = $_POST['service'];
	$_format = $_POST['format'];
	if (IsInjected($_host) || (IsInjected($_svs)) ||
	    (IsInjected($_format))) {
		print "Illegal characters found in POST.\n";
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

	$svsrawid = $mysqli->query("SELECT service_object_id FROM
	    nagios_services WHERE host_object_id = '$hostid' AND
	    display_name = '$_svs'");
	$svsdata = $svsrawid->fetch_assoc();
	$svsid = "{$svsdata['service_object_id']}";

	$staterawid = $mysqli->query("SELECT * FROM
	    nagios_servicestatus WHERE service_object_id = '$svsid'");
	$statedata = $staterawid->fetch_assoc();
	$svsstate = "{$statedata['output']}";
	$svslast_check = "{$statedata['last_check']}";
	$svsnext_check = "{$statedata['next_check']}";
	$svslast_state_change = "${statedata['last_state_change']}";
	$svslast_hard_state_change = "{$statedata['last_hard_state_change']}";
	$svslast_time_ok = "{$statedata['last_time_ok']}";
	$svslast_time_critical = "{$statedata['last_time_critical']}";
	$svsexectime = "{$statedata['execution_time']}";
	$mysqli->close();

	$_format = strtolower($_format);
	if (strcmp($_format, "xml") === 0) {
		/* Print XML output */
		print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		print "<service>\n";
		print "\t<serviceid>$svsid</serviceid>\n";
		print "\t<servicestatus>$svsstate</servicestatus>\n";
		print "\t<last_check>$svslast_check</last_check>\n";
		print "\t<next_check>$svsnext_check</next_check>\n";
		print "\t<last_state_change>$svslast_state_change</last_state_change>\n";
		print "\t<last_hard_state_change>$svslast_hard_state_change</last_hard_state_change>\n";
		print "\t<last_time_ok>$svslast_time_ok</last_time_ok>\n";
		print "\t<last_time_critical>$svslast_time_critical</last_time_critical>\n";
		print "\t<execution_time>$svsexectime</execution_time>\n";
		print "</service>\n";
	} else if (strcmp($_format, "json") === 0) {
		/* Print out JSON output */
		$jarray = array("serviceid" => $svsid, "servicestatus" => $svsstate,
		    "last_check" => $svslast_check, "next_check" => $svsnext_check,
		    "last_state_change" => $svslast_state_change,
		    "last_hard_state_change" => $svslast_hard_state_change,
		    "last_time_ok" => $svslast_time_ok,
		    "last_time_critical" => $svslast_time_critical,
		    "execution_time" => $svsexectime);
		print json_encode($jarray);
		print "\n";
	} else {
		print "Service state output is: $svsstate\n";
	}
	exit (0);
?>
