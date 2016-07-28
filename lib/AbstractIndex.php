<?php
abstract class AbstractIndex
{
	public static $db;

	public static $prefixes = array(
		'cp' => 'http://catalogus-professorum.org/',	
		'cpCity' => 'http://aditus.catalogus-professorum.org/city/',
		'cpCountry' => 'http://aditus.catalogus-professorum.org/country/',
		'cpAdministrativeDistrict' => 'http://aditus.catalogus-professorum.org/administrative-district/',	

		'cpd' => 'http://catalogus-professorum.org/cpd/',		

		'cpl' => 'http://aditus.catalogus-professorum.org/lipsiensium/',
		'cplProfessor' => 'http://aditus.catalogus-professorum.org/lipsiensium/professor/',
		'cplPerson' => 'http://aditus.catalogus-professorum.org/lipsiensium/person/',
		'cplPeriodOfLife' => 'http://aditus.catalogus-professorum.org/lipsiensium/period-of-life/',
		'cplDocument' => 'http://aditus.catalogus-professorum.org/lipsiensium/document/',
		'cplBody' => 'http://aditus.catalogus-professorum.org/lipsiensium/body/',

		'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
		'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
		'shv' => 'http://ns.aksw.org/spatialHierarchy/',
		'owl' => 'http://www.w3.org/2002/07/owl#',
		'xsd' => 'http://www.w3.org/2001/XMLSchema#',
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'geo'=> 'http://www.w3.org/2003/01/geo/wgs84_pos#',
	);

	public static $uriReplacements = array(
			"/ä/" => "ae", "/ö/" => "oe", "/ü/" => "ue",
			"/Ä/" => "Ae", "/Ö/" => "Oe", "/Ü/" => "Ue",
			"/á/" => "a",  "/à/" => "a",  "/â/" => "a", "/ã/" => "a",
			"/é/" => "e",  "/è/" => "e",  "/ê/" => "e",
			"/ú/" => "u",  "/ù/" => "u",  "/û/" => "u",
			"/ó/" => "o",  "/ò/" => "o",  "/ô/" => "o",
			"/Á/" => "A",  "/À/" => "A",  "/Â/" => "A", "/Ã/" => "A",
			"/É/" => "E",  "/È/" => "E",  "/Ê/" => "E",
			"/Ú/" => "U",  "/Ù/" => "U",  "/Û/" => "U",
			"/Ó/" => "O",  "/Ò/" => "O",  "/Ô/" => "O",
			"/ß/" => "ss"
	);

	public static $baseUri = 'http://aditus.catalogus-professorum.org/';

	public static $modelUri = 'http://aditus.catalogus-professorum.org/cpd/';

	public static $oldBaseUri = 'http://www.uni-leipzig.de/unigeschichte/professorenkatalog/';

	//public static $validClassTypes = array();	

	public static $classTypesMatch = array();

	public static $distinctClass = array();

	public static $propertiesMatch = array();

	public static $relationsMatch = array();

	public static $newRelationFromProperties = array();
	
	function __construct($user, $password, $database)	
	{
		self::$db = new MysqliDb ('localhost', $user, $password, $database, null, 'utf-8');

		/*
		Match old Classes with new Classes, set new uri
		olclass => array( type => newclass, urifunc => functions for the uri )
		*/
		self::$classTypesMatch = array(
			'AcademicSociety' => array(
				'type' => 'cpd:AcademicSociety',
				'urifunc' => function($uri) { return'cplBody:academicsociety-' . Resource::strToUri(basename($uri)); } ,
			),
			'Academy' => array(
				'type' => 'cpd:Academy',
				'urifunc' => function($uri) { return'cplBody:academy-' . Resource::strToUri(basename($uri)); } ,
			),
			'Archive' => array(
				'type' => 'cpd:Archive',
				'urifunc' => function($uri) { return'cplBody:archive-' . Resource::strToUri(basename($uri)); } ,
			),
			'Body' => array(
				'type' => 'cpd:Body',
				'urifunc' => function($uri) { return'cplBody:' . Resource::strToUri(basename($uri)); } ,
			),
			'Brother' => array(
				'type' => 'cpd:Person',
				'urifunc' => function($uri) { return'cplPerson:' . Resource::strToUri(basename($uri)); } ,
			),
			'Career' => array(
				'type' => 'cpd:Career',
				'urifunc' => function($uri) { return'cplPeriodOfLife:career-' . Resource::strToUri(basename($uri)); } ,
			),
			'Faculty' => array(
				'type' => 'cpd:Faculty',
				'urifunc' => function($uri) { return'cplBody:faculty-' . Resource::strToUri(basename($uri)); } ,
			),
			/*'Forename' => array(
				'type' => 'cpd:Forename'
			),*/
			'Institute' => array(
				'type' => 'cpd:Institute',
				'urifunc' => function($uri) { return'cplBody:institute-' . Resource::strToUri(basename($uri)); } ,
			),
			'Institution' => array(
				'type' => 'cpd:Institution',
				'urifunc' => function($uri) { return'cplBody:institution-' . Resource::strToUri(basename($uri)); } ,
			),
			'Office' => array(
				'type' => 'cpd:Office',
				'urifunc' => function($uri) { return'cplBody:office-' . Resource::strToUri(basename($uri)); } ,
			),
			'Party' => array(
				'type' => 'cpd:Party',
				'urifunc' => function($uri) { return'cplBody:party-' . Resource::strToUri(basename($uri)); } ,
			),
			'PeriodOfLife' => array(
				'type' => 'cpd:PeriodOfLife',
				'urifunc' => function($uri) { return'cplPeriodOfLife:' . Resource::strToUri(basename($uri)); } ,
			),			
			'Person' => array(
				'type' => 'cpd:Person',
				'urifunc' => function($uri) { return'cplPerson:' . Resource::strToUri(basename($uri)); } ,
			),			
			'PoliticalOrganisation' => array(
				'type' => 'cpd:PoliticalOrganisation',
				'urifunc' => function($uri) { return'cplBody:political-organisation-' . Resource::strToUri(basename($uri)); } ,
			),
			'Professor' => array(
				'type' => 'cpd:Professor',
				'urifunc' => function($uri) { return'cplProfessor:' . Resource::strToUri(basename($uri)); } ,
			),
			'Publication' => array(
				'type' => 'cpd:Publication',
				'urifunc' => function($uri) { return'cplDocument:publication-' . Resource::strToUri(basename($uri)); } ,
			),
			'QualificationPaper' => array(
				'type' => 'cpd:QualificationDocument',
				'urifunc' => function($uri) { return'cplDocument:qualification-document-' . Resource::strToUri(basename($uri)); } ,
			),
			'SocialRole' => array(
				'type' => 'cpd:SocialRole',
				'urifunc' => function($uri) { return'cplPeriodOfLife:social-role-' . Resource::strToUri(basename($uri)); } ,
			),
			'Study' => array(
				'type' => 'cpd:Study',
				'urifunc' => function($uri) { return'cplPeriodOfLife:study-' . Resource::strToUri(basename($uri)); } ,
			),
			
		);

		/*
		This classes will only created once. We test the uri in our DB
		*/
		self::$distinctClass = array(
			'shv:City', 'shv:AdministrativeDistrict', 'shv:Country',
			'cpd:AcademicSociety',
			'cpd:Academy',
			'cpd:Archive',
			'cpd:Body',
			'cpd:Faculty',
			'cpd:Institute',
			'cpd:Institution',
			'cpd:School',
			'cpd:Party',
			'cpd:PoliticalOrganisation',
			'cpd:QualificationDocument'
		);

		/*
		Match old Properties with new Properties
		domain => array( oldproperty => newproperty )
		*/
		self::$propertiesMatch = array(
			'_all' => array(
				self::$prefixes['rdfs'] . 'label' => 'rdfs:label',
				self::$prefixes['rdfs'] . 'comment' => 'rdfs:comment',
				self::$prefixes['owl'] . 'sameAs' => 'owl:sameAs',
				self::$prefixes['owl'] . 'SameAs' => 'owl:sameAs',

				self::$oldBaseUri . 'dissolved' =>  'cpd:dissolved',
				self::$oldBaseUri . 'founded' =>  'cpd:founded',

				self::$oldBaseUri . 'furtherInformation' =>  'cpd:furtherInformation',				
				self::$oldBaseUri . 'webLinks' =>  'cpd:webLinks',
			),
			'cpd:AcademicSociety' => array(
				self::$oldBaseUri . 'organisationName' =>  'rdfs:label',
				self::$oldBaseUri . 'organisationType' =>  'cpd:typeOfBody',
			),
			'cpd:Academy' => array(
				self::$oldBaseUri . 'academyType' =>  'cpd:typeOfBody',
				self::$oldBaseUri . 'institutionName' =>  'rdfs:label',				
			),
			'cpd:Archive' => array(
				self::$oldBaseUri . 'archiveCode' =>  'cpd:archiveCode',
				//self::$oldBaseUri . 'pictureArchive' =>  'rdfs:label',
			),			
			'shv:AdministrativeDistrict' => array(
				self::$oldBaseUri . 'birthLand' => 'rdfs:label',
				self::$oldBaseUri . 'academyLand' => 'rdfs:label',
			),
			'cpd:Birth' => array(
				self::$oldBaseUri . 'birthDate' => 'cpd:date',
			),
			'cpd:Body' => array(
				self::$oldBaseUri . 'organisationName' =>  'rdfs:label',				
				self::$oldBaseUri . 'organisationType' =>  'cpd:typeOfBody',
			),
			'cpd:Career' => array(
				self::$oldBaseUri . 'from' =>  'cpd:from',
				self::$oldBaseUri . 'organisationName' =>  'rdfs:label',
				self::$oldBaseUri . 'reasonForResignin' =>  'cpd:reasonForResigning',
				self::$oldBaseUri . 'reasonForResigning' =>  'cpd:reasonForResigning',
				self::$oldBaseUri . 'subjectsTaught' =>  'cpd:subjectsTaught'		,		
				self::$oldBaseUri . 'to' =>  'cpd:to',
				//self::$oldBaseUri . 'position' =>  'cpd:position',
				//self::$oldBaseUri . 'qualiPaperAcademicalTitle' =>  'cpd:qualiPaperAcademicalTitle',
			),
			'shv:City' => array(
				self::$oldBaseUri . 'birthCity' => 'rdfs:label',
				self::$oldBaseUri . 'deathCity' => 'rdfs:label',
				self::$oldBaseUri . 'graduationCity' => 'rdfs:label',
				self::$oldBaseUri . 'academyCity' => 'rdfs:label',
				self::$oldBaseUri . 'locatedAtCity' => 'rdfs:label',
				self::$oldBaseUri . 'Latitude' => 'geo:latitude',
				self::$oldBaseUri . 'Longitude' => 'geo:longitude',
			),	
			'shv:Country' => array(
				self::$oldBaseUri . 'birthState' => 'rdfs:label',
				self::$oldBaseUri . 'academyState' => 'rdfs:label',
				self::$oldBaseUri . 'locatedAtState' => 'rdfs:label',
			),
			'cpd:Death' => array(
				self::$oldBaseUri . 'deathDate' => 'cpd:date',
			),
			/*'cpd:Forename' => array(
				//self::$oldBaseUri . 'forename' => 'rdfs:label',
				self::$oldBaseUri . 'forenamePosition' => 'cpd:forenamePosition',
				self::$oldBaseUri . 'isFirstName' => 'cpd:isFirstName',
				self::$oldBaseUri . 'position' => 'cpd:forenamePosition',
			),*/
			'cpd:Faculty' => array(
				self::$oldBaseUri . 'institutionName' =>  'rdfs:label',
			),
			'cpd:Graduation' => array(
				self::$oldBaseUri . 'graduationType' => 'cpd:graduationType',
				self::$oldBaseUri . 'graduationYear' => 'cpd:date',
			),		
			'cpd:Institute' => array(
				self::$oldBaseUri . 'from' =>  'cpd:founded',
				self::$oldBaseUri . 'to' =>  'cpd:dissolved',
				self::$oldBaseUri . 'institutionName' =>  'rdfs:label',				
			),
			'cpd:Institution' => array(
				self::$oldBaseUri . 'academyType' =>  'cpd:typeOfBody',
				self::$oldBaseUri . 'institutionName' =>  'rdfs:label',
			),
			'cpd:Office' => array(
				self::$oldBaseUri . 'from' =>  'cpd:from',
				self::$oldBaseUri . 'officeName' =>  'rdfs:label',
				self::$oldBaseUri . 'to' =>  'cpd:to',
			),
			'cpd:Party' => array(
				self::$oldBaseUri . 'founded' =>  'cpd:founded',
				self::$oldBaseUri . 'dissolved' =>  'cpd:dissolved',
				//self::$oldBaseUri . 'organisationName' =>  'rdfs:label', // duplicate if rsdf:label
				self::$oldBaseUri . 'organisationType' =>  'cpd:typeOfBody',
			),
			'cpd:PeriodOfLife' => array(
				self::$oldBaseUri . 'from' =>  'cpd:from',
				self::$oldBaseUri . 'to' =>  'cpd:to',
				self::$oldBaseUri . 'reasonForResigning' =>  'cpd:reasonForResigning',
				self::$oldBaseUri . 'subjectsTaught' =>  'cpd:subjectsTaught',
				self::$oldBaseUri . 'subjectsStudied' =>  'cpd:subjectsStudied',
			),
			'cpd:Person' => array(		
				self::$oldBaseUri . 'denomination' =>  'cpd:denomination',
				self::$oldBaseUri . 'fullAcademicalTitle' =>  'cpd:fullAcademicalTitle',
				self::$oldBaseUri . 'literature' =>  'cpd:literature',
				//self::$oldBaseUri . 'pid' =>  'cpd:pid',
				self::$oldBaseUri . 'pnd' =>  'cpd:gnd',
				self::$oldBaseUri . 'references' =>  'cpd:references',
				self::$oldBaseUri . 'surname' =>  'cpd:surname',
				self::$oldBaseUri . 'profession' => 'cpd:profession',
			),
			'cpd:PoliticalOrganisation' => array(
				self::$oldBaseUri . 'organisationName' =>  'rdfs:label',
				self::$oldBaseUri . 'organisationType' =>  'cpd:typeOfBody',
			),
			'cpd:Picture' => array(
				self::$oldBaseUri . 'picture' =>  'cpd:picture',
				self::$oldBaseUri . 'pictureKey' =>  'cpd:pictureKey',
			),
			'cpd:Professor' => array(
				self::$oldBaseUri . 'additionToSurname' =>  'cpd:additionToSurname',
				self::$oldBaseUri . 'alternativeWritingOfSurname' =>  'cpd:alternativeWritingOfSurname',
				self::$oldBaseUri . 'denomination' =>  'cpd:denomination',
				self::$oldBaseUri . 'fullAcademicalTitle' =>  'cpd:fullAcademicalTitle',
				self::$oldBaseUri . 'literature' =>  'cpd:literature',
				//self::$oldBaseUri . 'pid' =>  'cpd:pid',
				self::$oldBaseUri . 'pnd' =>  'cpd:gnd',
				self::$oldBaseUri . 'reference' =>  'cpd:references',
				self::$oldBaseUri . 'references' =>  'cpd:references',
				self::$oldBaseUri . 'surname' =>  'cpd:surname',
				self::$oldBaseUri . 'forename' =>  'cpd:firstName',
				self::$oldBaseUri . 'note' =>  'cpd:note',
				self::$oldBaseUri . 'lectureLink' =>  'cpd:lectureLink',
				self::$oldBaseUri . 'leipzig/literature' =>  'cpd:literature',
				//self::$oldBaseUri . 'is-related-to' =>  'cpd:is-related-to',
				//self::$oldBaseUri . 'relationship' =>  'cpd:relationship',
				self::$oldBaseUri . 'relativeJob' =>  'cpd:profession',
			),		
			'cpd:Publication' => array(
				self::$oldBaseUri . 'publication' =>  'rdfs:label',
				self::$oldBaseUri . 'publicationReference' =>  'cpd:reference',				
			),
			'cpd:Qualification' => array( // created hardcoded als PeriodOfLife from QualificationDocument
				self::$oldBaseUri . 'from' =>  'cpd:date', // i gues this is what they mean here...
				self::$oldBaseUri . 'to' =>  'cpd:date',
				self::$oldBaseUri . 'subjectsTaught' => 'cpd:subjectsTaught',
				self::$oldBaseUri . 'lectureLink' => 'cpd:lectureLink',
				self::$oldBaseUri . 'position' => 'cpd:position',
				self::$oldBaseUri . 'reasonForResigning' => 'cpd:reasonForResigning',
			),
			'cpd:QualificationDocument' => array(
				self::$oldBaseUri . 'qualiPaperSubject' => 'cpd:subject',
				self::$oldBaseUri . 'qualiPaperAcademicalTitle' => 'cpd:academicalTitle',
				self::$oldBaseUri . 'qualiPaperAdemicalTitleca' => 'cpd:academicalTitle',
				self::$oldBaseUri . 'qualiPaperType' => 'cpd:type',
				self::$oldBaseUri . 'qualiPaperTitle' => 'cpd:title',
				self::$oldBaseUri . 'qualipapertitle' => 'cpd:title',
			),
			'cpd:SocialRole' => array(
				self::$oldBaseUri . 'from' =>  'cpd:from',
				self::$oldBaseUri . 'to' =>  'cpd:to',
				self::$oldBaseUri . 'functionInOrganisation' =>  'cpd:functionInOrganisation',
				self::$oldBaseUri . 'organisationName' =>  'rdfs:label',
				self::$oldBaseUri . 'organisationType' =>  'cpd:organisationType',
				//self::$oldBaseUri . 'membership' =>  'cpd:MEMBERSHIP',
			),
			'cpd:School' => array(
				self::$oldBaseUri . 'graduationSchool' =>  'rdfs:label',
				self::$oldBaseUri . 'graduationType' =>  'cpd:typeOfBody',
			),
			'cpd:Study' => array(
				self::$oldBaseUri . 'from' =>  'cpd:from',
				self::$oldBaseUri . 'to' =>  'cpd:to',
				self::$oldBaseUri . 'subjectStudied' =>  'cpd:subjectStudied',
			),						
		);

		/*
		Match old relations with new relations
		domain => array( oldrelation => array( type => newrelation, urifunc => function for the uri ) )
		*/
		self::$relationsMatch = array(
			//'_all' => array(),

			'cpd:AcademicSociety' => array(
				self::$oldBaseUri . 'relatedBody' =>  array(
					'type' => 'cpd:cognateBody', // alle related bodies in acadSoc sind acadSoc !
					'urifunc' => function($olduri) { return 'cplBody:academic-society-' . Resource::strToUri(basename($olduri)); } ,
				),
			),
			'cpd:Body' => array(
				self::$oldBaseUri . 'relatedBody' =>  array(
					'type' => 'cpd:cognateBody', // 2 relatedBodies in Body sind academicSociety !
					'urifunc' => function($olduri) { return 'cplBody:academic-society-' . Resource::strToUri(basename($olduri)); } ,
				),
			),
			'cpd:Career' => array(
				self::$oldBaseUri . 'prof-has-to-do-with' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:Faculty' => array(
				self::$oldBaseUri . 'consists-of' =>  array(
					'type' => 'cpd:consistsOf',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); },
				),
				self::$oldBaseUri . 'is-part-of' =>  array(
					'type' => 'cpd:isPartOf',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); },
				),				
			),
			'cpd:Institute' => array(
				self::$oldBaseUri . 'is-part-of' =>  array(
					'type' => 'cpd:isPartOf',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); },
				),
			),
			'cpd:Institution' => array(
				self::$oldBaseUri . 'is-part-of' =>  array(
					'type' => 'cpd:isPartOf',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); },
				),
			),
			'cpd:Office' => array(
				self::$oldBaseUri . 'prof-has-to-do-with' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:Membership' => array(
				self::$oldBaseUri . 'prof-has-to-do-with' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:Party' => array(
				self::$oldBaseUri . 'relatedBody' =>  array(
					'type' => 'cpd:cognateBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:PeriodOfLife' => array(
				self::$oldBaseUri . 'membership' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
				self::$oldBaseUri . 'prof-has-to-do-with' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),			
			'cpd:Person' => array(
				// relatedPerson ist in "BruderTheodorLipps" 
				self::$oldBaseUri . 'relatedPerson' =>  array(
					'type' => 'cpd:relatedPerson',
					'urifunc' => function($olduri) { return (( basename($olduri) == "Lipps_1373" ) ? 'cplProfessor:' : 'cplPerson:') . Resource::strToUri(basename($olduri)); } ,
				),
				/* forename is hardcoded!
				self::$oldBaseUri . 'has-forename' => array(
					'type' => 'cpd:hasForename',
					'urifunc' => function($olduri) { return 'cpl:forename/' . basename($olduri); } ,
				),*/
				self::$oldBaseUri . 'has-periods' => array(
					'type' => 'cpd:hasPeriod',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),				
				self::$oldBaseUri . 'is-tutor' => array(
					'type' => 'cpd:isTutor',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:Professor' => array(
				/* forename is hardcoded!
				self::$oldBaseUri . 'has-forename' => array(
					'type' => 'cpd:hasForename',
					'urifunc' => function($olduri) { return 'cpl:forename/' . basename($olduri); } ,
				),*/
				self::$oldBaseUri . 'has-periods' => array(
					'type' => 'cpd:hasPeriod',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
				self::$oldBaseUri . 'is-tutor' => array(
					'type' => 'cpd:isTutor',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
				self::$oldBaseUri . 'published' => array(
					'type' => 'cpd:published',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:Qualification' => array(
				self::$oldBaseUri . 'prof-has-to-do-with' => array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),				
			),
			'cpd:QualificationDocument' => array(
				self::$oldBaseUri . 'has-tutor' => array(
					'type' => 'cpd:hasTutor',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),				
			),
			'cpd:SocialRole' => array(
				self::$oldBaseUri . 'membership' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
				self::$oldBaseUri . 'prof-has-to-do-with' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
			'cpd:Study' => array(
				self::$oldBaseUri . 'prof-has-to-do-with' =>  array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($olduri) { return Resource::newUriFromOrgResource($olduri); } ,
				),
			),
		);

		/*
		Create new Relations and Resources from Properties
		domain => array( 
			newClassType => array( 
				type => String  - the type of the new relation
				properties => array( uris ) - The properties define for which properties the new relation should be created
				[unique] => Boolean - If given, the relation will created just once for this resource, even if more than one property is given, only feasible if more than one property is given
				[urifunc] => Function - Function for new uri with all properties of current resource as argumenz. If no urifunc is given, the uri would be like http://uri-of-the-class/new-class-type, example: http://cpl.org/professor/karl/birth
				[newdata] => array( uris ) - If given, the new resource will have this properties as new data
				[multiprop] => Boolean, If given, it will check, if the given property exists multiple times to create distinvt relations and new resources
			)
		)
		*/
		self::$newRelationFromProperties = array(
			//'_all' => array(),

			'cpd:AcademicSociety' => array(
				'shv:City' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri( Resource::getOldPropObj('locatedAtCity', $data) );  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'locatedAtCity',
						self::$oldBaseUri . 'locatedAtState',
						self::$oldBaseUri . 'academyLand',
					),
				),
			),
			'cpd:Academy' => array(
				'shv:City' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri( Resource::getOldPropObj('academyCity', $data));  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'academyCity',
						self::$oldBaseUri . 'academyState',
						self::$oldBaseUri . 'academyLand',
					),
					'newdata' => array(
						self::$oldBaseUri . 'academyCity',
						self::$oldBaseUri . 'academyState',
						self::$oldBaseUri . 'academyLand',
						self::$oldBaseUri . 'Latitude',
						self::$oldBaseUri . 'Longitude',
					)
				),
			),
			'cpd:Body' => array(
				'shv:City' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri( Resource::getOldPropObj('locatedAtCity', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'locatedAtCity',
						self::$oldBaseUri . 'locatedAtState',
						self::$oldBaseUri . 'academyLand',
					),
				),
			),
			'cpd:Career' => array(
				'cpd:QualificationDocument' => array(
					'type' => 'cpd:periodDocument',
					'urifunc' => function($data) { return 'cplDocument:qualification-document-' . Resource::strToUri(Resource::getOldPropObj('qualiPaperAcademicalTitle', $data) . Resource::getOldPropObj('qualiPaperSubject', $data));  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'qualiPaperSubject',
						self::$oldBaseUri . 'qualiPaperType',
					),
					'newdata' => array(
						self::$oldBaseUri . 'qualiPaperSubject',
						self::$oldBaseUri . 'qualiPaperType',
						self::$oldBaseUri . 'qualiPaperAcademicalTitle',
					),
				),
			),
			'cpd:Graduation' => array(
				'shv:City' => array(
					'type' => 'cpd:periodPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri(Resource::getOldPropObj('graduationCity', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'graduationCity',
					),
				),
				'cpd:School' => array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($data) { return 'cplBody:school-' . Resource::strToUri(Resource::getOldPropObj('graduationSchool', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'graduationSchool',
					),
					'newdata' => array(
						self::$oldBaseUri . 'graduationSchool',
						self::$oldBaseUri . 'graduationType',
					),
				),
			),
			'cpd:Institute' => array(
				'shv:City' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri(Resource::getOldPropObj('academyCity', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'academyCity',
						self::$oldBaseUri . 'academyState',
					),
				),
			),
			'cpd:Institution' => array(
				'shv:City' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri(Resource::getOldPropObj('academyCity', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'academyCity',
						self::$oldBaseUri . 'academyLand',
						self::$oldBaseUri . 'academyState',
					),
				),
			),
			'cpd:Party' => array(
				'shv:City' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri(Resource::getOldPropObj('locatedAtCity', $data));  },
					'unique' => true,
					'properties' => array(						
						//self::$oldBaseUri . 'homeCity',
						self::$oldBaseUri . 'locatedAtCity',
						//self::$oldBaseUri . 'locatedAtState',
						//self::$oldBaseUri . 'locatedAtCountry',
					),
				),
			),
			'cpd:PeriodOfLife' => array(
				'cpd:Body' => array(
					'type' => 'cpd:periodBody',
					'urifunc' => function($data) { return 'cplBody:' . Resource::strToUri(Resource::getOldPropObj('organisationName', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'organisationName',
						self::$oldBaseUri . 'organisationType',
					),
				),
				'cpd:QualificationDocument' => array(
					'type' => 'cpd:periodDocument',
					'urifunc' => function($data) { return 'cplDocument:qualification-document-' . Resource::strToUri(Resource::getOldPropObj('qualiPaperAcademicalTitle', $data) . Resource::getOldPropObj('qualiPaperSubject', $data));  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'qualiPaperSubject',
						self::$oldBaseUri . 'qualiPaperType',
						self::$oldBaseUri . 'qualiPaperAcademicalTitle',
					),
				),
			),

			'cpd:Person' => array(
				'cpd:Birth' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'birthCity',
						self::$oldBaseUri . 'birthDate',
						self::$oldBaseUri . 'birthState',
						self::$oldBaseUri . 'birthLand',
					),
				),
				'cpd:Death' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'deathCity',
						self::$oldBaseUri . 'deathDate',
						self::$oldBaseUri . 'deathState',
						self::$oldBaseUri . 'deathLand',
					),
				),
				'cpd:Picture' => array(
					'type' => 'cpd:hasPicture',
					//'urifunc' => function($data) { return 'cpl:picture/' . basename(Resource::getOldPropObj('picture', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'picture',
					),
					'newdata' => array(
						self::$oldBaseUri . 'picture',
						self::$oldBaseUri . 'pictureArchive',
					),
				),
				'cpd:Publication' => array(
					'type' => 'cpd:published',					
					'urifunc' => function($data) { return 'cplDocument:publication-' . Resource::strToUri(Resource::getOldPropObj('publication', $data));  },
					'multiprop' => true,
					'properties' => array(
						self::$oldBaseUri . 'publication',
					),					
				),
				'cpd:Membership' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'prof-has-to-do-with'
					),
				),
				'cpd:Study' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'subjectStudied'
					),
				),
				'cpd:Graduation' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'graduationType'
					),
					'newdata' => array(
						self::$oldBaseUri . 'graduationType',
						self::$oldBaseUri . 'graduationCity',
						self::$oldBaseUri . 'graduationLand',
						self::$oldBaseUri . 'graduationSchool',
						self::$oldBaseUri . 'graduationYear',
					),
				),
			),
			'cpd:PoliticalOrganisation' => array(
				'shv:Country' => array(
					'type' => 'cpd:bodyPlace',
					'urifunc' => function($data) { return 'cpCountry:' . Resource::strToUri(Resource::getOldPropObj('locatedAtState', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'locatedAtState',
					),
				),
			),
			'cpd:Professor' => array(
				'cpd:Membership' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'prof-has-to-do-with'
					),
				),
				'cpd:Birth' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'birthCity',
						self::$oldBaseUri . 'birthDate',
						self::$oldBaseUri . 'birthState',
						self::$oldBaseUri . 'birthLand',
					),
				),
				'cpd:Death' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'deathCity',
						self::$oldBaseUri . 'deathDate',
						self::$oldBaseUri . 'deathState',
						self::$oldBaseUri . 'deathLand',
					),
				),
				'cpd:Family' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'fatherForename',
						self::$oldBaseUri . 'fatherSurname',
						self::$oldBaseUri . 'motherSurname',
					),
					'newdata' => array(
						self::$oldBaseUri . 'fatherForename',
						self::$oldBaseUri . 'fatherSurname',
						self::$oldBaseUri . 'fatherProfession',
						self::$oldBaseUri . 'motherSurname',
						self::$oldBaseUri . 'motherProfession',
					),
				),
				'cpd:Picture' => array(
					'type' => 'cpd:hasPicture',
					//'urifunc' => function($data) { return 'cpl:picture/' . basename(Resource::getOldPropObj('picture', $data));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'picture',
					),
					'newdata' => array(
						self::$oldBaseUri . 'picture',
						self::$oldBaseUri . 'pictureArchive',
						self::$oldBaseUri . 'pictureKey',
					),
				),
				'cpd:Graduation' => array(
					'type' => 'cpd:hasPeriod',
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'graduationType',
						self::$oldBaseUri . 'graduationCity',
					),'newdata' => array(
						self::$oldBaseUri . 'graduationType',
						self::$oldBaseUri . 'graduationCity',
						self::$oldBaseUri . 'graduationLand',
						self::$oldBaseUri . 'graduationSchool',
						self::$oldBaseUri . 'graduationYear',
					),
				),
				'cpd:Publication' => array(
					'type' => 'cpd:published',					
					'urifunc' => function($data) { return 'cplDocument:publication-' . Resource::strToUri(Resource::getOldPropObj('publication', $data));  },
					'multiprop' => true,
					'properties' => array(
						self::$oldBaseUri . 'publication',
					),					
				),
			),



			/* 
			New generated Classes 
			*/
			'cpd:Birth' => array(
				'shv:City' => array(
					'type' => 'cpd:periodPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri(Resource::getOldPropObj('birthCity', $data));  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'birthCity',
						self::$oldBaseUri . 'birthState',
						self::$oldBaseUri . 'birthLand',
					),
				),
			),
			'cpd:Death' => array(
				'shv:City' => array(
					'type' => 'cpd:periodPlace',
					'urifunc' => function($data) { return 'cpCity:' . Resource::strToUri(Resource::getOldPropObj('deathCity', $data));  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'deathCity',
						self::$oldBaseUri . 'deathState',
						self::$oldBaseUri . 'deathLand',
					),
				),
			),
			'cpd:Family' => array(
				'cpd:Person' => array(
					'type' => 'cpd:familyParent',
					'urifunc' => function($data) { $fname = Resource::getOldPropObj('fatherForename', $data); $sname = Resource::getOldPropObj('fatherSurname', $data); return 'cplPerson:' . Resource::strToUri( isset($fname) ? $fname . "-" : "" . $sname );  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'fatherForename',
						self::$oldBaseUri . 'fatherSurname',
					),
					'newdata' => array(
						self::$oldBaseUri . 'fatherForename',
						self::$oldBaseUri . 'fatherSurname',
						self::$oldBaseUri . 'fatherProfession'
					),
				),
				// fixed hardcoded Person-1 to Person
				'cpd:Person-1' => array(
					'type' => 'cpd:familyParent',
					'urifunc' => function($data) { return 'cplPerson:' . Resource::strToUri(Resource::getOldPropObj('motherSurname', $data));  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'motherSurname',
					),
					'newdata' => array(
						self::$oldBaseUri . 'motherSurname',
						self::$oldBaseUri . 'motherProfession'
					),
				),
			),

			// Cities, Districts, Countries -> uris are hardcoded!!!
			'shv:City' => array(
				'shv:AdministrativeDistrict' => array(
					'type' => 'shv:isLocatedIn',
					//'urifunc' => function($data) { return 'cpd:administrative-district/' . Resource::getOldPropObj('birthLand', $data);  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'academyLand',
						self::$oldBaseUri . 'birthLand',
					),
					'newdata' => array(
						self::$oldBaseUri . 'academyLand',
						self::$oldBaseUri . 'birthLand',
						self::$oldBaseUri . 'academyState',
						self::$oldBaseUri . 'birthState',
						self::$oldBaseUri . 'locatedAtState',
					),
				),
				'shv:Country' => array(
					'type' => 'shv:isLocatedIn',
					//'uriprop' => self::$oldBaseUri . 'birthState',
					//'urifunc' => function($data) { return 'cpCountry:' . Resource::getOldPropObj('birthState', $data);  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'academyState',
						self::$oldBaseUri . 'birthState',
						self::$oldBaseUri . 'locatedAtState',
					),
				),
			),
			'shv:AdministrativeDistrict' => array(
				'shv:Country' => array(
					'type' => 'shv:isLocatedIn',
					//'uriprop' => self::$oldBaseUri . 'birthState',
					//'urifunc' => function($data) { return 'cpCountry:' . Resource::getOldPropObj('birthState', $data);  },
					'unique' => true,
					'properties' => array(
						self::$oldBaseUri . 'academyState',
						self::$oldBaseUri . 'birthState',
						self::$oldBaseUri . 'locatedAtState',
					),
				),
			),
			'cpd:Picture' => array(
				'cpd:Archive' => array(
					'type' => 'cpd:pictureArchive',
					'urifunc' => function($data) { return 'cplBody:archive-' . Resource::strToUri(basename(Resource::getOldPropObj('pictureArchive', $data)));  },
					'unique' => true,
					'properties' => array(						
						self::$oldBaseUri . 'pictureArchive',
					),
				),
			),
		);
	}

}
?>