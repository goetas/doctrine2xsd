<?php


namespace Goetas\DoctrineToXsd\Command;

use Goetas\DoctrineToXsd\Convert\ConvertToXsd;

use Goetas\DoctrineToXsd\Mapper\TypeMapper;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console,
    Doctrine\ORM\Tools\Console\MetadataFilter,
    Doctrine\ORM\Tools\EntityRepositoryGenerator;

class GenerateXsd extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('doctrine2xsd:generate-xsd')
        ->setDescription('Generate repository classes from your mapping information.')
        ->setDefinition(array(
        
            new InputArgument(
                'destination', InputArgument::REQUIRED, 'The path where save your XSD.'
            ),
            new InputArgument(
                'target-ns', InputArgument::REQUIRED, 'The target namespace for your XSD'
            ),
            new InputOption(
                'ns-map', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'PHP namespaces - XML namepsaces map Syntax = PHPns:PATH:XMLns'
            ),
        	new InputOption(
        		'allow-map', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        		'allow file location'
        	),
            new InputOption(
                'extension',null, InputOption::VALUE_REQUIRED,
                'Custom extension for Doctine2 mapping files', 'xml'
            ),
            
        ))
        ->setHelp("Generate repository classes from your mapping information.");
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {    	
    	$ext = $input->getOption('extension');
    	
    	$destinationNs = $input->getArgument('target-ns');
    	$destination = $input->getArgument('destination');
    	
    	$allowMap = $input->getOption('allow-map');
		
		if(is_dir($destination)){
			throw new \RuntimeException("Destination could not be a directory.");
		}
		
    	$nsMap = $input->getOption('ns-map');
		if(!$nsMap){
			throw new \RuntimeException(__CLASS__." requires at least one ns-map (for {$destinationNs} namespace).");
		}
		
		$converter = new ConvertToXsd();
				
		$output->writeln("Target namespace: <info>$destinationNs</info>");
		
		$files = array();
		foreach ($nsMap as  $k => $value){
			list($phpNs, $dir, $xmlNs) = explode(":",$value, 3);
			
			$dir = rtrim($dir,"\\//");
			$phpNs = trim(strtr($phpNs, '.','\\'),"\\");
			
			$nsMap[$k]=array(
				"phpNs"=>$phpNs,
				"dir"=>$dir,
				"xmlNs"=>$xmlNs,
			);
			
			$output->writeln("\tDIR: <info>$dir</info>");
			$output->writeln("\tPHP: <comment>$phpNs</comment>");
			$output->writeln("\tXML: <comment>$xmlNs</comment>\n");
			
		}
		$ret = $converter->convert($destination, $destinationNs, $nsMap, $allowMap);
		
		if($ret){
			$output->writeln("Writing schema <info>$destination</info>");
			return 0;
		}
		return 1;
    }
}
