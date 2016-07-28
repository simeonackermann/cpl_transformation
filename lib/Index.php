<?php
class Index extends AbstractIndex
{
	public static $resources = array();
	//public static $classTypesMatchLower;


	function createResult()
	{	

		//$classTypesMatchLower = array_map('strtolower', array_keys(parent::$classTypesMatch))
		/*foreach (parent::$classTypesMatch as $key => $value) {
			self::$classTypesMatchLower[ strtolower($key) ] = $key;
		}*/
		//var_dump( self::$classTypesMatchLower );

		$start=isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
		$limit=isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 999999;

		// may clear unique resource uris table to reinit all uris
		if ( isset($_REQUEST['clearInit']) ) {
			parent::$db->rawQuery('TRUNCATE _trans_uris');
		}


		//$query='SELECT * FROM statements GROUP BY subject LIMIT '.$start.','.$limit;
		
		//$query='SELECT DISTINCT subject FROM statements LIMIT '.$start.','.$limit;
		$query='SELECT DISTINCT s1.subject FROM statements s1';
		
		//$query='SELECT DISTINCT subject FROM statements WHERE subject = "http://www.uni-leipzig.de/unigeschichte/professorenkatalog/leipzig/Meyn_503"';

		if ( isset($_REQUEST['type']) ) {
			$query .= ' WHERE s1.object = "http://www.uni-leipzig.de/unigeschichte/professorenkatalog/'.$_REQUEST['type'].'"';
		}

		//$query='SELECT DISTINCT subject FROM statements WHERE object LIKE "%Person" LIMIT '.$start.','.$limit;

		if ( isset($_REQUEST['predicate']) ) {
			$query .= ' AND s1.subject IN (SELECT s2.subject FROM statements s2 WHERE s2.predicate = "http://www.uni-leipzig.de/unigeschichte/professorenkatalog/'.$_REQUEST['predicate'].'")';
		}

		$query .= ' LIMIT '.$start.','.$limit;

		$resources = parent::$db->rawQuery($query);

		foreach ($resources as $key => $resource) {
			$newResource = new Resource();
			$newResource = $newResource->initResourceFromSubjUri($resource['subject']);

			if ( empty($newResource) ) {
				continue;
			}

			self::$resources[] = $newResource;
		}

		

		
		
		//printf( 'all: ' . count(self::$resources) );
		
	}

	function addResource($resource)
	{		
		self::$resources[] = $resource;
		
	}

	function getPrefixes()
	{
		return parent::$prefixes;	
	}

	function getResources()
	{
		return self::$resources;
	}
}
?>