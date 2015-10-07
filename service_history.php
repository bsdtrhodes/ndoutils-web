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

	/*
	 * Get the current date and seven days prior.
	 */
	$today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
	$oneweekago = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-7, date("Y")));
	$results = $mysqli->query("SELECT * FROM nagios_servicechecks WHERE service_object_id = '$svsid'
		AND start_time BETWEEN '$oneweekago' AND '$today'");

	$abv_nums = array();
	while ($row = $results->fetch_assoc()) {
		$to_time = strtotime("{$row['end_time']}");
		$from_time = strtotime("{$row['start_time']}");
		$rounded = abs($to_time - $from_time) - 35;
		array_push($abv_nums, $rounded);
	}
	$mysqli->close();

	/*
	 * With the array created and filled, get our needed data.
	 * This produces a float value of course, YMMV, just adjust for
	 * needs/requirements.
	 */
	$atotal = array_sum($abv_nums);
	$aentcount = count($abv_nums);
	$average = $atotal / $aentcount;
	$maxtime = max($abv_nums);
	$mintime = min($abv_nums);

	/* Create XML/JSON/plain text and spit it out. */
	$_format = strtolower($_format);
	if (strcmp($_format, "xml") === 0) {
		/* Print XML output */
		print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		print "<service>\n";
		print "\t<average>$average</average>\n";
		print "\t<maxtime>$maxtime</maxtime>\n";
		print "\t<mintime>$mintime</mintime>\n";
		print "\t<checkcount>$atotal</checkcount>\n";
		print "</service>\n";
	} else if (strcmp($_format, "json") === 0) {
		/* Print out JSON output */
		$jarray = array("average" => $average, "maxtime" => $maxtime,
		    "mintime" => $mintime, "checkcount" => $atotal);
		print json_encode($jarray);
		print "\n";
	} else {
		print "The average over all stored data is: $average ";
		print "The max transaction was $maxtime and low was $mintime\n";
	}
	exit (0);
?>
