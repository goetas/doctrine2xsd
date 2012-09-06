<?php


namespace Goetas\DoctrineToXsd\Convert;

use Goetas\DoctrineToXsd\Mapper\TypeMapper;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console,
    Doctrine\ORM\Tools\Console\MetadataFilter,
    Doctrine\ORM\Tools\EntityRepositoryGenerator;

class ConvertToXsd
{
    public function convert($destination, $destinationNs, array $nsMap, array $allowMap, $ext = 'xml')
    {

		if(is_dir($destination)){
			throw new \RuntimeException("Destination could not be a directory.");
		}
		if(!$nsMap){
			throw new \RuntimeException(__CLASS__." requires at least one ns-map (for {$destinationNs} namespace).");
		}
				
		$files = array();
		foreach ($nsMap as $value){
			$dir = rtrim($value["dir"],"\\//");
			TypeMapper::addNamespace($value["phpNs"], $value["xmlNs"]);
			$files = array_merge($files, glob("$dir/*.{$ext}"));			
		}
					
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$root = $dom->createElement('root');
		$dom->appendChild($root);
		foreach ($files as $file){
			$mapping = new \DOMDocument('1.0', 'UTF-8');
			$mapping->load($file);
			$newnode = $dom->importNode($mapping->documentElement, true); 
			$root->appendChild($newnode);
		}

		$this->handleAllowMap($allowMap, $dom);
			
			
		$xsd = new \DOMDocument('1.0', 'UTF-8');
		$xsd->load(__DIR__.'/../Resources/xsd/convert.xsl');
		
		$processor = new \XSLTProcessor();
		$processor->registerPHPFunctions();
		$processor->setParameter('','targetNs', $destinationNs);
				
		$processor->importStylesheet($xsd);
		
		$newDom = $processor->transformToDoc($dom);


		$this->fixXsd($newDom, $destinationNs);

		$ret = $newDom->save($destination);
				
		if($ret>0){
			return true;
		}else{
			return false;
		}
    }
    
    protected function handleAllowMap($allowMap, $dom) {
    	$xpDom = new \DOMXPath($dom);
    	$xpDom->registerNamespace("d", "http://doctrine-project.org/schemas/orm/doctrine-mapping");
    	
		foreach ($allowMap as $allowFile){
			
			if(!is_file($allowFile)){
				throw new \RuntimeException("Can't load allow file '{$allowFile}'");
			}
			
			$allowDom = new \DOMDocument('1.0', 'UTF-8');
			$allowDom->load($allowFile);
			
			$allowDom = new \DOMXPath($allowDom);
			$allowDom->registerNamespace("a", "http://www.goetas.com/doctrine2xsd/allow");
			
			
			foreach ($allowDom->query("/a:allow-map/a:entity") as $entityRule){
				$entityAllow = !($entityRule->getAttribute("allow")=="false");
				$entityName = $entityRule->getAttribute("name");
				
				$filedAllows = array();
				foreach ($allowDom->query("a:field", $entityRule) as $fieldRule){
					$filedAllows[$fieldRule->getAttribute("name")]=!($fieldRule->getAttribute("allow")=="false");
				}
							
				
				
				$res = $xpDom->query("//d:entity[@name='{$entityName}']/*[local-name()='field' or contains(local-name(),'-to-')]");
				$nodes = array();
				foreach ($res as $node){
					$nodes[]=$node;
				}
				
				foreach ($nodes as $entityField){
					$nodeName = $entityField->hasAttribute("name")?$entityField->getAttribute("name"):$entityField->getAttribute("field");
					
					
					$fieldAllow = $entityAllow;
					
					if(isset($filedAllows[$nodeName])){
						$fieldAllow = $filedAllows[$nodeName];
					}
					
					if(!$fieldAllow){						
						$entityField->parentNode->removeChild($entityField);
					}
				}
				
				
			}
			
			
		}
    }
    protected function fixXsd(\DOMDocument $newDom, $destinationNs) {
    	
    	
    	$xp = new \DOMXPath($newDom);
		$xp->registerNamespace("xsd", "http://www.w3.org/2001/XMLSchema");
		
		
		$nodes = array();
		foreach ($xp->query("//xsd:sequence[count(*)=0]") as $node){
			$nodes[]=$node;
		}
		
		foreach ($nodes as $node) {
			$node->parentNode->removeChild($node);
		}
		
		$types = array();
		$nodes = array();
		foreach ($xp->query("//xsd:schema/xsd:complexType[contains(@name,'ArrayOf')]") as $node){
			$nodes[]=$node;
		}
		foreach ($nodes as $node) {
			$type = $node->getAttribute("name");
			if (!isset($types[$type])){
				$types[$type] = true;
			}else{
				$node->parentNode->removeChild($node);
			}
		}
		$newDom->formatOutput = true;
		$newDom->preserveWhiteSpace = false;
		
		$newDom->documentElement->setAttribute("xmlns", $destinationNs);
		foreach (TypeMapper::getAllPrefixes() as $prefix => $ns){
			$newDom->documentElement->setAttribute("xmlns:$prefix", $ns);
		}
    }
}
