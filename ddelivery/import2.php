<?php
/*
$config = mainConfiguration::getInstance();

// Name of the file
$filename = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'tmp','ps_dd_cities2.sql'));
// MySQL host
$mysql_host = $config->get('connections', 'core.host'); //'localhost';
// MySQL username
$mysql_username = $config->get('connections', 'core.login'); //'';
// MySQL password
$mysql_password = $config->get('connections', 'core.password'); //'';
// Database name
$mysql_database = $config->get('connections', 'core.dbname'); //'';


// Connect to MySQL server
$conn = mysql_connect($mysql_host, $mysql_username, $mysql_password) or die('Error connecting to MySQL server: ' . mysql_error());
// Select database
$db = mysql_select_db($mysql_database) or die('Error selecting MySQL database: ' . mysql_error());


// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file($filename);


// Loop through each line
foreach ($lines as $line) {


    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '')
        continue;

    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) == ';') {
        // Perform the query
        mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
        // Reset temp variable to empty
        $templine = '';
    }
}
echo "Tables imported successfully";
*/

