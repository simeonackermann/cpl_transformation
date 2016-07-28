<?php

$dbUser = 'root';
$dbPassword = '';
$dbDB = 'cpl_old';

include_once 'lib/MysqliDb.php';
include_once 'lib/AbstractIndex.php';
include_once 'lib/Index.php';
include_once 'lib/Resource.php';
header('content-type: text/plain; charset=utf-8');

$steps = 500;
$part = isset( $_REQUEST['part'] ) ? $_REQUEST['part'] : 1;

if ( $part == 1 ) $_REQUEST['clearInit'] = true;

if ( ! isset($_REQUEST['start']) ) $_REQUEST['start'] = $steps * ($part-1);
if ( ! isset($_REQUEST['limit']) ) $_REQUEST['limit'] = $steps;


$i = new Index($dbUser, $dbPassword, $dbDB);
$i->createResult();

$regexPrefixes = array();

foreach ($i->getPrefixes() as $key => $prefix) {
	echo "@prefix ".$key.": <".$prefix."> .\n";
	$regexPrefixes["/".$key."\:/i"] = $prefix;
}
echo "\n";

foreach ($i->getResources() as $resource) {

	echo $resource->uri . "\n";

	foreach ($resource->properties as $property) {
		if ( $property['predicate'] == "rdf:type" ) {
			continue;
		}
		
		echo "   " . $property['predicate'];

		if ( isset($property['isRelation']) ) {
			echo " " . $property['object'];
		} else {
			if ( strpos($property['object'], '"') === false && strpos($property['object'], "\r\n") === false ) {
				echo " \"".$property['object']."\"";
			} else {
				echo " \"\"\"\n".$property['object']."\n\"\"\"";
			}		
		}		
		if ( isset($property['datatype']) && ! isset($property['language']) ) {
			echo "^^".$property['datatype'];
		}
		if ( isset($property['language']) ) {
			echo "@".$property['language'];
		}
		echo " ;\n";
	}
	echo "   a $resource->type . \n\n";	
}
?>