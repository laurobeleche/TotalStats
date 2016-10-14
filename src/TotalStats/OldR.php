<?php

namespace TotalStats;

use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\Player;

class OldR extends PluginTask{
    private $plugin;
    private $countable;

    public function __construct(TotalStats $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->countable = 0;
    }

    public function onRun($currentTick){
		$hora = date("H");
		$minuto = date("i");
		$dia = date("d");
		$mes = date("m");
		$ano = date("Y");
		if($hora == "00"){
			if($minuto == "00"){
				$this->plugin->saveOldR();
			}
		}
		if($dia == "15"){
			if($hora == "00"){
				if($minuto == "00"){
					$this->plugin->ranqueadoTag();
				}
			}
		}
		//$this->plugin->logOnConsole($hora . ":" . $minuto);
	}
}
