<?php

namespace TotalStats;

use beleche\zLoteria\zLoteria;
use OpMonitor\OpMonitor;
use beleche\zClan\zClan;
use beleche\zItemDroper\zItemDroper;
use beleche\zReparar\zReparar;

use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\Player;

class Tasks extends PluginTask{
    private $plugin;
    private $countable;

    public function __construct(TotalStats $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->countable = 0;
    }

    public function onRun($currentTick){

		$this->plugin->atualizaPontos();
		
		//$this->plugin->saveOldR();

		zLoteria::getInstance()->saveStatus();

		OpMonitor::getInstance()->atualizaOps();

		zClan::getInstance()->saveAll();
		
		zItemDroper::getInstance()->saveAll();
		
		zReparar::getInstance()->saveAll();

	}
}
