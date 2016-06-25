<?php

namespace JetFire\Framework\Commands;


use JetFire\Framework\App;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Route
 * @package JetFire\Framework\Commands
 */
class Route extends Command{


    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('route:list')
            ->setDescription('List your routes')
            ->addArgument('cells', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'What cells do you want to render ?',['Url','Callback','Name','Method'])
            ->addOption('method', null, InputOption::VALUE_REQUIRED, 'List your routes by method')
            ->addOption('block', null, InputOption::VALUE_REQUIRED, 'List your routes by block')
            ->addOption('controller', null, InputOption::VALUE_REQUIRED, 'List your routes by controller')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cells = $input->getArgument('cells');
        $table = new Table($output);
        $table
            ->setHeaders($cells)
            ->setRows($this->getCells($cells))
        ;
        $table->render();
    }

    /**
     * @param array $cells
     * @return array
     */
    private function getCells($cells = []){
        $collection = App::getInstance()->get('routing')->getCollection();
        $tab = $table = [];
        foreach(['url','callback','name','method'] as $value)
            $table[$value] = (in_array($value,strtolower($cells))) ? true : false;
        for($i = 0;$i < $collection->countRoutes;++$i){
            if($i > 0)$tab[] =  new TableSeparator();
            $tab[] = [new TableCell('<fg=green>Block path : '.$collection->getRoutes()['block_'.$i].'</>', array('colspan' => count($cells)))];
            $tab[] =  new TableSeparator();
            foreach($collection->getRoutes()['routes_'.$i] as $url => $params) {
                $t = [];
                if($table['url']) $t['url'] = $url;
                if(is_array($params) && isset($params['use'])) {
                    if($table['callback']) $t['callback'] = is_callable($params['use']) ? 'closure' : $params['use'];
                    if($table['name']) $t['name'] = (isset($params['name']))? $params['name'] : '';
                    if($table['method']) $t['method'] = (isset($params['method'])) ? is_array($params['method'])?implode(',',$params['method']):$params['method']:'GET';
                }
                elseif(is_callable($params) && $table['callback'])
                    $t['callback'] = 'closure';
                $tab[] = $t;
            }
        }
        return $tab;
    }

} 