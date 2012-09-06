<?php

namespace Goetas\DoctrineToXsd\Mapper;

class TypeMapper 
{
	const TARRAY = 'array';
    const BIGINT = 'bigint';
    const BOOLEAN = 'boolean';
    const DATETIME = 'datetime';
    const DATETIMETZ = 'datetimetz';
    const DATE = 'date';
    const TIME = 'time';
    const DECIMAL = 'decimal';
    const INTEGER = 'integer';
    const OBJECT = 'object';
    const SMALLINT = 'smallint';
    const STRING = 'string';
    const TEXT = 'text';
    const BLOB = 'blob';
    const FLOAT = 'float';
    const GUID = 'guid';

    /** The map of supported doctrine mapping types. */
    private static $typesMap = array(
        self::TARRAY => null,
        self::OBJECT => null,
        self::BOOLEAN => 'xsd:boolean',
        self::INTEGER => 'xsd:int',
        self::SMALLINT => 'xsd:short',
        self::BIGINT => 'xsd:long',
        self::STRING => 'xsd:string',
        self::TEXT => 'xsd:string',
        self::DATETIME => 'xsd:dateTime',
        self::DATETIMETZ => 'xsd:dateTime',
        self::DATE => 'xsd:date',
        self::TIME => 'xsd:time',
        self::DECIMAL => 'xsd:decimal',
        self::FLOAT => 'xsd:double',
        self::BLOB => 'xsd:string',
        self::GUID => 'xsd:string',
    );
    private static $namespaces = array();
	public static function getXsdType($type) {
		return self::$typesMap[$type];
	}
	public static function getXsdArrayType($type) {
		$str = substr(self::getXsdType($type), 4);
		return "ArrayOf".ucfirst($str);
	}
	public static function getTypeName($type, $targetNs) {

		$phpTargetNamespace = array_search($targetNs, self::$namespaces);
				
		$typeNs = strtr(dirname(strtr($type,"\\", "/")), "/", "\\");
						
		if($typeNs==$phpTargetNamespace){
			return substr($type, strlen($typeNs)+1);
		}else{
			return self::getPrefixForTypeName($typeNs).":".substr($type, strlen($typeNs)+1);
		}
	}
	public static function addNamespace($phpNamespace, $xmlNamespace) {
		self::$namespaces[$phpNamespace]=$xmlNamespace;
	}
	public static function getTargetNsForType($type) {
		$phpNs = strtr(dirname(strtr($type,"\\", "/")), "/", "\\");
		if (!isset(self::$namespaces[$phpNs])){
			throw new \Exception("Can't find a valid namespace prefix for {$phpNs}. Has been adeded trought ".__CLASS__."::addNamespace?");
		}
		return self::$namespaces[$phpNs];
	}
	public static function getAllPrefixes() {
		$cnt = 0;
		$nss = array();
		foreach (self::$namespaces as  $value) {
			$nss["ns".$cnt++]=$value;
		}
		return $nss;
	}
	public static function getPrefixForTypeName($phpNs) {
		if (!isset(self::$namespaces[$phpNs])){
			throw new \Exception("Can't find a valid namespace prefix for {$phpNs}. Has been adeded trought ".__CLASS__."::addNamespace?");
		}
		return "ns".array_search($phpNs, array_keys(self::$namespaces));
	}
}
