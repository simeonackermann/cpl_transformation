<?php
class Resource extends Index
{
	public $uri = '';
	public $orgUri = NULL;
	public $type = '';
	public $properties = array();
	
	function __construct() { }

	function initResourceFromSubjUri($subjectUri="")
	{
		$oldtype = null;
		$newtype = null;
		$newUri = null;

		//$this->orgUri = $subjectUri;
		$this->properties[] = $this->createProperty( array(
			'predicate' => 'dc:source', 
			'object' => $subjectUri
		) );

		$resourceData = parent::$db->rawQuery('SELECT * FROM statements WHERE subject = "'.$subjectUri.'"');

		foreach ($resourceData as $row) {
			if ( $row['predicate'] == parent::$prefixes['rdf'] . 'type' ) { $oldtype = $row['object']; break; }
		}
		$oldtype = basename($oldtype);
		//if ( ! in_array($oldtype, parent::$classTypesMatch) ) {
		if ( ! isset(parent::$classTypesMatch[$oldtype]) ) {
			return null;
		}

		if ( isset( parent::$classTypesMatch[$oldtype]['urifunc'] ) ) {
			$func = parent::$classTypesMatch[$oldtype]['urifunc'];
			$newUri = $func($subjectUri);
		} else {
			$newUri = 'cpl:' . $this->strToUri($oldtype . '-' . basename($subjectUri));
		}
		
		$newType = parent::$classTypesMatch[$oldtype]['type'];

		$this->setType( $newType );
		$newUri = $this->setUri( $newUri );		
		$this->setProperties( $resourceData );

		//echo "<br />" . $subjectUri;

		/*if ( $newType == "cpd:QualificationDocument" ) {
			//var_dump($subjectUri);
			$qdPeriods = parent::$db->rawQuery('SELECT * FROM statements WHERE object = "'.$subjectUri.'"');
			var_dump($qdPeriods);
		}*/
	
		return $this;
	}

	function setType($type='')
	{
		$this->type = $type;
		$this->properties[] = $this->createProperty( array( 'predicate' => 'rdf:type', 'object' => $type ) );
	}

	function setUri($uri='')
	{
		if ( $this->type == '' ) {
			throw new Exception("No resource type given. Set it before setting the uri!", 1);	
		}

		// test if uri exists in db
		//$q = parent::$db->rawQuery('SELECT * FROM _trans_uris WHERE uri="'.$uri.'"');
		$testUri = addslashes($uri);

		if ( ! in_array($this->type, parent::$distinctClass) ) {
			$i=1;
			while ( count(parent::$db->rawQuery('SELECT * FROM _trans_uris WHERE uri="'.addslashes($testUri).'"')) > 0 ) {
				$testUri = $uri."-".$i;
				$i++;
			}
		}

		$uri=$testUri;

		//$this->uri = $this->strToUri( $uri );
		$this->uri = $uri;

		// create it in db
		parent::$db->insert ('_trans_uris', array("uri" => $this->uri ));
		
		/*
		echo "<hr />";
		var_dump( $this->uri );	
		*/

		return $this->uri;
	}

	function strToUri($str='')
	{
		//var_dump( dirname($str) . basename($str) );		
		$str = mb_strtolower($str, 'UTF-8');
		$str = trim($str);
		$str = str_replace(' ', '-', $str);
		
		$str = preg_replace(array_keys(parent::$uriReplacements), array_values(parent::$uriReplacements), $str);
		//$str = preg_replace("/[^a-zA-Z0-9:\/\.\(\)\-\_#]/", "", $str);
		$str = preg_replace("/[^a-zA-Z0-9\-\_]/", "", $str);
		
		// remove: remove not allowed signs!!!
		$str = substr($str, 0, 180); // baseuri (max length 75) will prefixed 
		return $str;
	}

	function strRemovePrefix($str='')
	{
		return preg_replace('/^[a-zA-Z]*:/', '', $str);
		//return preg_replace(array_keys(parent::$prefixes), '', $str);
	}
	
	function setProperties($resourceData=array())
	{
		$forenames = array();
		$firstname = "";

		foreach ($resourceData as $row) {

			if ( $row['predicate'] == self::$oldBaseUri . 'pnd' && $row['object'] == "0" ) { 
				continue; // fix emmpty pnd
			}

			//if ( stristr($newUri, "cpl:forename-http") ) {
			if ( $row['object'] == "http://www.uni-leipzig.de/unigeschichte/professorenkatalog/leipzig/Hartmut-1-1" ) {
				// fix: do not use forename as period 
				continue;				
			}

			// fix: get forenames and firstname from has-forename
			if ( $row['predicate'] == self::$oldBaseUri . 'has-forename' ) {
				//var_dump($row);
				$forenameData = parent::$db->rawQuery('SELECT * FROM statements WHERE subject = "'.addslashes($row['object']).'"');
				$forenameIdx = preg_match_all("/\d/", $row['object'], $matches,  PREG_OFFSET_CAPTURE);
				if ( !empty($matches[0]) ) {
					$forenamePos = $row['object'][ $matches[0][0][1] ];

					//var_dump($forenamePos);
					//$forename = array( "" );
					$name = $this->getProperty( parent::$prefixes['rdfs'] . "label", $forenameData )['object'];
					//var_dump($name);

					$forenames[ intval($forenamePos) ] = $name;

					if ( $row['object'][ $matches[0][1][1] ] == "1" ) {
						$firstname = $name;
					}
				}
			}

			// fix is-tutor link to person/professor
			/*if ( $row['predicate'] ==  self::$oldBaseUri . "is-tutor" ) {
				var_dump("FIXME" .  $row['object'] );
				var_dump( $this->newUriFromOrgResource( $row['object'] ) );
				
				$orgResource = parent::$db->rawQuery('SELECT * FROM statements WHERE subject="'.$row['object'].'" AND predicate="'.parent::$prefixes['rdf'].'type"');
				if ( count( $orgResource ) > 0 ) {
					var_dump($orgResource);
				}
				
			}*/

			// fix has-tutor link to person/prof
			/*if ( $row['predicate'] ==  self::$oldBaseUri . "has-tutor" ) {
					var_dump("FIXME");
			}*/

			// add academical title to prof label
			if ( $this->type == "cpd:Professor" && $row['predicate'] == "http://www.w3.org/2000/01/rdf-schema#label" ) {
				$academicalTitle = $this->getOldPropObj( 'fullAcademicalTitle', $resourceData );
				if ( stristr($academicalTitle, "Dr") !== false ) {
					$row['object'] = "Dr. " . $row['object'];
				}
				if ( stristr($academicalTitle, "Prof") !== false  ) {
					$row['object'] = "Prof. " . $row['object'];
				}
			}
			// add archive label
			if ( $this->type == "cpd:Archive" ) {
				$archivelabel = $this->getOldPropObj( 'pictureArchive', $resourceData );
				if ( $archivelabel != NULL ) {
					$this->properties[] = $this->createProperty( array( "predicate" => "rdfs:label", "object" => basename($archivelabel) ));
				}
			}
			
			// propertiesMatch _all
			if ( isset(parent::$propertiesMatch['_all'][ $row['predicate'] ]) ) {
				$row['predicate'] = parent::$propertiesMatch['_all'][$row['predicate']];
				// we dont need duplicated labels
				/*if ( $row['predicate'] == "rdfs:label" && $this->getProperty("rdfs:label")['object'] == $row['object'] ) {
					continue;
				}*/

				$this->properties[] = $this->createProperty($row);			
			}

			// propertiesMatch type
			if ( isset(parent::$propertiesMatch[$this->type]) &&
				 isset(parent::$propertiesMatch[$this->type][ $row['predicate'] ]) ) {
				$row['predicate'] = parent::$propertiesMatch[$this->type][$row['predicate']];

				// we dont need duplicated labels
				/*if ( $row['predicate'] == "rdfs:label" && $this->getProperty("rdfs:label")['object'] == $row['object'] ) {
					continue;
				}*/

				$this->properties[] = $this->createProperty($row);
			}

			// relationsMatch type
			if ( isset(parent::$relationsMatch[$this->type]) &&
				 isset(parent::$relationsMatch[$this->type][ $row['predicate'] ]) ) {				
				
				$func = parent::$relationsMatch[$this->type][ $row['predicate'] ]['urifunc'];
				$newUri = $func( $row['object'] );
				$newType = parent::$relationsMatch[$this->type][ $row['predicate'] ]['type'];				

				if ( stristr($newUri, "cplDocument:qualification-document-") !== false ) {
					//var_dump($row['object']);
					$newQualiUri = "cplPeriodOfLife:qualification-" . $this->strToUri(basename($newUri));
					// create new resource
					$newQualiResource = new Resource();
					$newQualiResource->setType("cpd:Qualification");
					$newQualiUri = $newQualiResource->setUri($newQualiUri);

					$newQualiData = parent::$db->rawQuery('SELECT * FROM statements WHERE subject = "'.addslashes($row['object']).'"');
					//var_dump($newQualiData);

					$newQualiResource->setProperties($newQualiData);

					$newQualiResource->properties[] = $this->createRelationProperty( array(
						'predicate' => 'cpd:periodDocument', 
						//'object' => $this->strToUri( $this->newUriFromOrgResource($row['object']) )
						'object' => 'cplDocument:qualification-document-' . mb_strtolower(basename($row['object'], 'UTF-8'))
					) );

					// add resource in result
					parent::addResource($newQualiResource);
					$newUri = $newQualiUri;
				}

				if ( ! empty($newUri) ) {
					$this->properties[] = $this->createRelationProperty( array( 
						'predicate' => $newType,
						//'object' => $this->strToUri( $newUri )
						'object' => $newUri
					) );
				}
				
			}

			$newRelation = $this->propertyAsRelation( $row['predicate'], $resourceData );

			if ( ! empty($newRelation) ) {
				$this->properties[] = $newRelation;
			}
		}

		// create fornames and firstname
		if ( !empty($forenames) ) {
			ksort($forenames);
			$this->properties[] = $this->createProperty( array( 
				'predicate' => "cpd:forename",
				'object' => implode(" ", $forenames)
			) );
		}
		if ( !empty($firstname) ) {
			$this->properties[] = $this->createProperty( array( 
				'predicate' => "cpd:firstName",
				'object' => $firstname
			) );
		}

		// create label from orgUri if no label exists
		if ( $this->getProperty("rdfs:label") == NULL ) {
			$orgUri = $this->getProperty("dc:source");
			if ( $orgUri != NULL ) {
				$newLabel = basename($orgUri['object']);
				//$newLabel = preg_replace("/([A-Z])/", " $1", $newLabel);
				$newLabel = str_replace("_", " ", $newLabel);
				$newLabel = preg_replace("/([a-z])([A-Z])/", "$1 $2", $newLabel);
				$newLabel = preg_replace("/([A-Z])([A-Z])([a-z])/", "$1$2 $3", $newLabel);
				$newLabel = preg_replace("/([0-9])([A-Z])/", "$1 $2", $newLabel);
				$newLabel = trim($newLabel);

				//var_dump($newLabel);
				$this->properties[] = $this->createProperty( array( 
					'predicate' => "rdfs:label",
					'object' => $newLabel
				) );
			}
		}

	}

	/*function getResourceType($resourceData=array())
	{
		foreach ($resourceData as $res) {
			if ( $res['predicate'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' ) {
				return $res['object'];
			}
		}
		return NULL;
	}*/
	
	// create a property array
	function createProperty($data) 
	{
		$property = array(
			'predicate' => $data['predicate'],
			//'object' => utf8_decode($data['object'])
			'object' => trim($data['object'])
		);

		// may add language
		if (isset($data['l_language']) && !empty($data['l_language'])) {
			$property['language'] = $data['l_language'];
		}

		// may add datatype (not for rdfs:label)
		if (isset($data['l_datatype']) && ! empty($data['l_datatype'])
			&& $data['predicate'] != "rdfs:label" ) {
			
			$datatype = $data['l_datatype'];

			// fix date datatyps
			if ( preg_match("/^[0-9]{4}$/", $property['object']) ) {
				$datatype = 'xsd:gYear';
			} elseif ( preg_match("/^[0-9]{4}-[0-9]{2}$/", $property['object']) ) {
				$datatype = 'xsd:gYearMonth';
			} elseif ( preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $property['object']) ) {
				$datatype = 'xsd:date';
			} else {
				if ( strstr($datatype, parent::$prefixes['xsd']) ) {
					$datatype = str_replace(parent::$prefixes['xsd'], "xsd:", $datatype);
				} else {
					$datatype = "xsd:" . lcfirst($datatype);
				}
			}

			if ( $datatype == "xsd:float" && ! is_float($property['object']) ) {
				$datatype = "xsd:string";
			}
			if ( $datatype == "xsd:integer" && ! is_integer($property['object']) ) {
				$datatype = "xsd:string";
			}
			if ( $datatype == "xsd:double" && ! is_double($property['object']) ) {
				$datatype = "xsd:string";
			}

			/*else {
				if ( $datatype != "String" && preg_match("/\w/", $property['object']) ) {
					$datatype = 'String';
				}
			}
			if ( $datatype == "String" ) {
				$datatype = 'xsd:string';
			}*/			

			//$property['datatype'] = $data['l_datatype'];
			$property['datatype'] = $datatype;
		}

		if (isset($data['isRelation']) ) {
			$property['isRelation'] = true;
		}

		return $property;
	}

	function createRelationProperty($data=array())
	{
		$data['isRelation'] = true;
		return $this->createProperty( $data );
	}

	function hasProperty( $propertyUri='' )
	{
		foreach ($this->properties as $property) {
			if ( $property['predicate'] == $propertyUri ) {
				return true;
			}
		}
		return false;
	}

	function getProperty( $propertyUri='', $properties=array() ) 
	{
		$properties = !empty($properties) ? $properties : $this->properties;
		foreach ($properties as $property) {
			if ( $property['predicate'] == $propertyUri ) {
				return $property;
			}
		}
		return NULL;
	}

	function getOldPropObj( $propertyUri='', $properties=array() ) 
	{
		return Resource::getProperty( self::$oldBaseUri . $propertyUri, $properties )['object'];
	}

	// return our new uri from an resource in DB (depends mainly on its type)
	function newUriFromOrgResource( $orgUri )
	{
		$uri = '';
		$orgUri = preg_replace(array_keys(parent::$uriReplacements), array_values(parent::$uriReplacements), $orgUri);
		$orgResource = parent::$db->rawQuery('SELECT * FROM statements WHERE subject="'.addslashes($orgUri).'" AND predicate="'.parent::$prefixes['rdf'].'type"');
		if ( count( $orgResource ) > 0 ) {
			$orgType = basename($orgResource[0]['object']);

			if ( isset( parent::$classTypesMatch[$orgType] ) && 
				 isset( parent::$classTypesMatch[$orgType]['urifunc'] ) ) {
			//if ( in_array(strtolower( basename($orgType) ), parent::$classTypesMatchLower ) ) {
			//if ( in_array(strtolower( basename($orgType) ), array_map('strtolower', array_keys(parent::$classTypesMatch)) ) ) { 
				$func = parent::$classTypesMatch[$orgType]['urifunc'];
				$uri = $func( $orgUri );
				//$uri = 'cpl:NEW' . $orgType . '-' . $orgUri;
			} else {
				$uri = 'cpl:' . mb_strtolower($orgType . '-' . $orgUri, 'UTF-8');
			}			
		}
		return $uri;
	}
	

	function propertyAsRelation( $predicate, $data=array() )
	{
		if ( isset(parent::$newRelationFromProperties[$this->type]) ) {

			foreach (parent::$newRelationFromProperties[$this->type] as $newRelationClass => $newRelationValues) {
					
				if ( ! in_array($predicate, $newRelationValues['properties']) ) {
					continue;
				}

				// don't recreate unique relations, such as family
				if ( isset($newRelationValues['unique']) ) {
					$uniqueVar = 'hasRelation_' . $newRelationValues['type'] . '_' . $newRelationClass;
					if ( isset($this->$uniqueVar ) ) {
						continue;
					} else {
						$this->$uniqueVar = true;
					}
				}

				if ( $newRelationClass == "cpd:Person-1" ) {
					$newRelationClass = "cpd:Person";
				}

				// get new data from old data and given properties or newdata in newRelationFromProperties array
				$newData = array();
				if ( isset($newRelationValues['newdata']) ) {
					foreach ($data as $set) {
						if ( in_array($set['predicate'], $newRelationValues['newdata']) ) {
							$newData[] = $set;
						}
					}
				} else {
					foreach ($data as $set) {
						if ( in_array($set['predicate'], $newRelationValues['properties']) ) {
							$newData[] = $set;
						}
					}
				}


				/*
				// TODO: maybe fix non existing image (person/betreuer_theodorlipps -> picture/x)
				if ( $newRelationClass == 'cpd:Picture' ) {
					var_dump($data);
				}*/
				// if is multiple prop, remove others than current property from data and newData
				if ( isset($newRelationValues['multiprop']) ) {
					
					if ( ! isset( parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropcounter'] ) ) {
						parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropcounter'] = 0;
					}
					if ( ! isset( parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropuri'] ) ) {
						parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropuri'] = $this->uri;
					}

					if ( parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropuri'] != $this->uri ) {
						parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropuri'] = $this->uri;
						parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropcounter'] = 0;
					}


					$i=0;
					foreach ($data as $key => $set) {
						if ( in_array($set['predicate'], $newRelationValues['properties']) ) {
							if ( $i != parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropcounter'] ) {
								unset( $data[$key] );
							}
							$i++;
						}
					}
					$i=0;
					foreach ($newData as $key => $set) {
						if ( in_array($set['predicate'], $newRelationValues['properties']) ) {
							if ( $i != parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropcounter'] ) {
								unset( $newData[$key] );
							}
							$i++;
						}
					}
					parent::$newRelationFromProperties[$this->type][$newRelationClass]['multipropcounter']++;
				}

				// create new resource uri from func or as sub-uri from current resource: http://current -uri/new-class-type
				$newResUri = '';
				if ( isset($newRelationValues['urifunc']) ) {
					$func = $newRelationValues['urifunc'];
					//$newResUri  = $this->strToUri( $func($data) );
					$newResUri  = $func($data);
				} 				
				// new uri from fitted property for administric
				elseif ( $newRelationClass == 'shv:AdministrativeDistrict' ) {
					$newResUri = 'cpAdministrativeDistrict:' . $this->strToUri( $this->getProperty($predicate, $data)['object'] );
				}
				elseif ( $newRelationClass == 'shv:Country' ) {
					$newResUri = 'cpCountry:' . $this->strToUri( $this->getProperty($predicate, $data)['object'] );
				}
				else {
					$newResUri = $this->uri . '-' . $this->strToUri( $this->strRemovePrefix($newRelationClass) );
				}

				// dont recreate city/state/country etc if it already exists, just return as new relation property
				/*if ( in_array($newRelationClass, parent::$distinctClass) ) {
					if ( count(parent::$db->rawQuery('SELECT * FROM _trans_uris WHERE uri="'.addslashes($newResUri).'"') ) > 0 ) {
						return $this->createRelationProperty( array(
							'predicate' => $newRelationValues['type'], 
							'object' => $newResUri
						) );
					}
				}*/				

				// create new resource
				$newResource = new Resource();
				$newResource->setType($newRelationClass);
				$newResUri = $newResource->setUri($newResUri);				
				$newResource->setProperties($newData);

				// add some specific properties 
				if ( $newRelationClass == 'cpd:Family' ) {
					// add this resource as familyChild for a family
					$newResource->properties[] = $this->createRelationProperty( array(
						'predicate' => 'cpd:familyChild', 
						'object' => $this->uri
					) );
				}

				if ( $this->type == 'cpd:Family' ) {
					// add hasPeriod for a family father/mother
					$newResource->properties[] = $this->createRelationProperty( array(
						'predicate' => 'cpd:hasPeriod', 
						'object' => $this->uri
					) );
					// add fathers label
					$fName = $this->getProperty(parent::$oldBaseUri . 'fatherForename', $newData)['object'];					
					$sName = $this->getProperty(parent::$oldBaseUri . 'fatherSurname', $newData)['object'];
					if ( isset($fName) || isset($sName) ) {
						$label = isset($fName) ? $fName . " " : "";
						$label .= isset($sName) ? $sName : "";
						$newResource->properties[] = $this->createProperty( array(
							'predicate' => 'rdfs:label', 
							'object' => $label
						) );
					}
					$sName = $this->getProperty(parent::$oldBaseUri . 'motherSurname', $newData)['object'];
					if ( isset($sName) ) {
						$newResource->properties[] = $this->createProperty( array(
							'predicate' => 'rdfs:label', 
							'object' => $sName
						) );
					}

					// add father/mother profession
					$fProfession = $this->getProperty(parent::$oldBaseUri . 'fatherProfession', $newData)['object'];
					if ( ! empty($fProfession) ) {
						$newResource->properties[] = $this->createProperty( array(
							'predicate' => 'cpd:profession', 
							'object' => $fProfession
						) );	
					}
					$mProfession = $this->getProperty(parent::$oldBaseUri . 'motherProfession', $newData)['object'];
					if ( ! empty($mProfession) ) {
						$newResource->properties[] = $this->createProperty( array(
							'predicate' => 'cpd:profession', 
							'object' => $mProfession
						) );	
					}
				}

				// some resources. e.g. a city, has an empty uri
				if ( empty( $this->strRemovePrefix($newResUri) ) ) {
					return NULL;
				}
				
				// add resource in result
				parent::addResource($newResource);

				// return new relation property
				return $this->createRelationProperty( array(
					'predicate' => $newRelationValues['type'], 
					'object' => $newResUri
				) );
			}
		}
		return null;
	}
}
?>