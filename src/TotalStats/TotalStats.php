<?php

namespace TotalStats;

use onebone\economyapi\EconomyAPI;
use beleche\zShopStats\zShopStats;
use beleche\zBattleLogger\zBattleLogger;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\utils\TextFormat;
use pocketmine\tile\Chest;
use pocketmine\block\Block;
use pocketmine\tile\Hopper;

/*
-------------------------------------------------------------------------------------------------------------
-------------------------------------------------------------------------------------------------------------
------------------------------------		████	 ███	████		-------------------------------------
------------------------------------		█	█	█	█	█	█		-------------------------------------
------------------------------------		████	█	█	████		-------------------------------------
------------------------------------		█		█	█	█  █		-------------------------------------
------------------------------------		█		 ███	█	█		-------------------------------------
-------------------------------------------------------------------------------------------------------------
--------█		 ███	█	█	████	 ███	████	█████	█		█████	 ████	█	█	█████--------
--------█		█	█	█	█	█	█	█	█	█	█	█		█		█		█		█	█	█------------
--------█		█████	█	█	████	█	█	████	████	█		████	█		█████	████---------
--------█		█	█	█	█	█  █	█	█	█	█	█		█		█		█		█	█	█------------
--------█████	█	█	█████	█	█	 ███	████	█████	█████	█████	 ████	█	█	█████--------
-------------------------------------------------------------------------------------------------------------
-------------------------------------------------------------------------------------------------------------
*/

class TotalStats extends PluginBase implements Listener{

    /** @var Config */
    protected $config;
    /** @var \mysqli */
    protected $db;

	public $drops = array();
	
	public $varPontos = array();
	public $pontuacao;
	private static $instance = null;
	
	public $multiplicador;
	public $pt_construir;
	public $pt_quebrar;
	//pontuações do minerador---------------------------------
	public $pt_miner;
	public $pt_miner_carvao;
	public $pt_miner_ferro;
	public $pt_miner_ouro;
	public $pt_miner_diamante;
	public $pt_miner_esmeralda;
	public $pt_miner_lapislazuli;
	public $pt_miner_redstone;
	//--------------------------------------------------------------
	//pontuações do lenhador----------------------------------------
	public $pt_lenhador;
	public $pt_lenhador_carvalho;
	public $pt_lenhador_pinheiro;
	public $pt_lenhador_eucalipito;
	public $pt_lenhador_selva;
	public $pt_lenhador_acacia;
	public $pt_lenhador_carv_escuro;
	public $pt_kills;
	//--------------------------------------------------------------
	//pontuações do crafter-----------------------------------------
	public $pt_crafts;
	public $pt_crafts_couro_capuz;
	public $pt_crafts_ferro_capuz;
	public $pt_crafts_ouro_capuz;
	public $pt_crafts_diamante_capuz;
		 
	public $pt_crafts_couro_tunica;
	public $pt_crafts_ferro_tunica;
	public $pt_crafts_ouro_tunica;
	public $pt_crafts_diamante_tunica;
		 
	public $pt_crafts_couro_calca;
	public $pt_crafts_ferro_calca;
	public $pt_crafts_ouro_calca;
	public $pt_crafts_diamante_calca;
		 
	public $pt_crafts_couro_botas;
	public $pt_crafts_ferro_botas;
	public $pt_crafts_ouro_botas;
	public $pt_crafts_diamante_botas;
		 
	public $pt_crafts_madeira_espada;
	public $pt_crafts_pedra_espada;
	public $pt_crafts_ferro_espada;
	public $pt_crafts_ouro_espada;
	public $pt_crafts_diamante_espada;
		 
	public $pt_crafts_madeira_picareta;
	public $pt_crafts_pedra_picareta;
	public $pt_crafts_ferro_picareta;
	public $pt_crafts_ouro_picareta;
	public $pt_crafts_diamante_picareta;
		 
	public $pt_crafts_madeira_pa;
	public $pt_crafts_pedra_pa;
	public $pt_crafts_ferro_pa;
	public $pt_crafts_ouro_pa;
	public $pt_crafts_diamante_pa;
		 
	public $pt_crafts_madeira_machado;
	public $pt_crafts_pedra_machado;
	public $pt_crafts_ferro_machado;
	public $pt_crafts_ouro_machado;
	public $pt_crafts_diamante_machado;
		 
	public $pt_crafts_madeira_enchada;
	public $pt_crafts_pedra_enchada;
	public $pt_crafts_ferro_enchada;
	public $pt_crafts_ouro_enchada;
	public $pt_crafts_diamante_enchada;
		 
	//--------------------------------------------------------------
	public $money = array();
	
	public $oldr;
	public $ranqueado;
	public $dias = [];
	public $rdias = [];
    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->saveResource("config.yml", false);
		$this->saveResource("oldr.yml", false);
		$this->saveResource("ranqueado.yml", false);
        $this->config = new Config($this->getDataFolder() . "config.yml");
		$this->oldr = new Config($this->getDataFolder() . "oldr.yml");
		$this->ranqueado = new Config($this->getDataFolder() . "ranqueado.yml");
		$this->rdias = $this->ranqueado->get("ranqueado");
		$this->dias = $this->oldr->get("oldr");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $config = $this->config->get("mysql_settings");
        if(!isset($config["host"]) or !isset($config["user"]) or !isset($config["password"]) or !isset($config["database"])){
            $this->logOnConsole("Configurações MYSQL não encontrada!");
            $this->logOnConsole("Por favor verifique o config.yml");
            $this->logOnConsole("PLUGIN: TotalStats");
            return;
        }
        $this->db = new \mysqli($config["host"], $config["user"], $config["password"], $config["database"], isset($config["port"]) ? $config["port"] : 3306);
        if($this->db->connect_error){
            $this->logOnConsole("Não foi possível conectar ao MySQL: ". $this->db->connect_error);
            $this->logOnConsole("Plugin TotalStats desabilitado !");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("TotalStats"));
            return;
        }else{
			
		}
		$time = $this->config->get("time");
		if (!(is_numeric($time))) {
            $time = 12000;
            $this->logOnConsole("Não consegui ler o tempo para atualizar! Cheque o arquivo de configuração! Default: " . F::AQUA . " 10 " . F::WHITE . " minutos");
        } else {
            $time = $time * 1200;
        }
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks($this), $time);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new OldR($this), 20);
		$this->updPontos();
        $this->logOnConsole("Criando consulta no banco de dados...");
        $resource = $this->getResource("mysql.sql");
        $this->db->query(stream_get_contents($resource));
        @fclose($resource);
		$this->varPontos = $this->carregaPontos();
        $this->logOnConsole("Pronto!");
        $this->logOnConsole("Conectado ao servidor MySQL!");
		
	
    }
	//-Salva arquivo do OldR
	public function saveOldR(){
		$dia = date("d");
		$mes = date("m");
		$ano = date("Y");
		if(isset($this->dias[$ano][$mes][$dia])){
			
		}else{
			$this->atualizaPontos();
			$sql = "INSERT INTO `old_player_stats` SELECT * FROM `player_stats`";
			$sql2 = "TRUNCATE TABLE `old_player_stats`";

			$this->db->query($sql2);
			$this->db->query($sql);
			
			$this->dias[$ano][$mes][$dia] = date("H-i-s");
			$this->oldr->set("oldr", $this->dias);
			$this->oldr->save();
			$this->logOnConsole("OLDT");
		}
		
	}
	public function ranqueadoTag(){
		$dia = date("d");
		$mes = date("m");
		$ano = date("Y");
		if(isset($this->rdias[$ano][$mes][$dia])){
			
		}else{
			
			$sql = "SELECT * FROM `players_pvpranqueado` ORDER BY `pontos` DESC";
			$result = $this->db->query($sql);
			$valor = mysqli_fetch_array($result);
			$primeiro = $valor;
			$nome = $primeiro["nome"];

			$sql = "SELECT * FROM `players_rank` WHERE `rank` = 'gladiador'";
			$result = $this->db->query($sql);
			$valor = mysqli_fetch_array($result);
			$glad = $valor;
			$this->logOnConsole($nome . " - " . $glad["nome"]);
			$sql_1 = "UPDATE `players_rank` SET `rank` = 'Membro', `display_rank` = '§2[Membro]' WHERE `nome` = '" . strtolower($glad["nome"]) . "'";
			$sql_2 = "UPDATE `players_rank` SET `rank` = 'gladiador', `display_rank` = '§l§6[§4>§6Gladiador§4<§6]' WHERE `nome` = '" . strtolower($nome) . "'";
			$this->db->query($sql_1);
			$this->db->query($sql_2);
			$this->rdias[$ano][$mes][$dia] = $nome . " - " . $primeiro["pontos"];
			$this->ranqueado->set("ranqueado", $this->rdias);
			$this->ranqueado->save();
			unset($sql);
			unset($sql_1);
			unset($sql_2);
			unset($result);
			unset($valor);
			unset($glad);
			unset($primeiro);
			unset($nome);
			unset($dia);
			unset($mes);
			unset($ano);
		}
	}
	public function updMultiplicador(Player $player){
		//$this->config = new Config($this->getDataFolder() . "config.yml");
		$name = strtolower($player->getName());

		$mplayer = $this->varPontos[$name]['multiplicador'];

		$this->multiplicador = $this->config->get("multiplicador");
		$this->multiplicador = $this->multiplicador + $mplayer;
		unset($mplayer);

	}
	public function reloadConf(){
		$this->config = new Config($this->getDataFolder() . "config.yml");
	}
	public function updPontos(){
		//$this->config = new Config($this->getDataFolder() . "config.yml");
		
		$this->pt_construir = $this->config->get("pt_construir") * $this->multiplicador;
		$this->pt_quebrar = $this->config->get("pt_quebrar") * $this->multiplicador;
		//pontuações do minerador---------------------------------
		$this->pt_miner = $this->config->get("pt_miner") * $this->multiplicador;
		$this->pt_miner_carvao = $this->config->get("pt_miner_carvao") * $this->multiplicador;
		$this->pt_miner_ferro = $this->config->get("pt_miner_ferro") * $this->multiplicador;
		$this->pt_miner_ouro = $this->config->get("pt_miner_ouro") * $this->multiplicador;
		$this->pt_miner_diamante = $this->config->get("pt_miner_diamante") * $this->multiplicador;
		$this->pt_miner_esmeralda = $this->config->get("pt_miner_esmeralda") * $this->multiplicador;
		$this->pt_miner_lapislazuli = $this->config->get("pt_miner_lapislazuli") * $this->multiplicador;
		$this->pt_miner_redstone = $this->config->get("pt_miner_redstone") * $this->multiplicador;
		//--------------------------------------------------------------
		//pontuações do lenhador----------------------------------------
		$this->pt_lenhador = $this->config->get("pt_lenhador") * $this->multiplicador;
		$this->pt_lenhador_carvalho = $this->config->get("pt_lenhador_carvalho") * $this->multiplicador;
		$this->pt_lenhador_pinheiro = $this->config->get("pt_lenhador_pinheiro") * $this->multiplicador;
		$this->pt_lenhador_eucalipito = $this->config->get("pt_lenhador_eucalipito") * $this->multiplicador;
		$this->pt_lenhador_selva = $this->config->get("pt_lenhador_selva") * $this->multiplicador;
		$this->pt_lenhador_acacia = $this->config->get("pt_lenhador_acacia") * $this->multiplicador;
		$this->pt_lenhador_carv_escuro = $this->config->get("pt_lenhador_carv_escuro") * $this->multiplicador;
		$this->pt_kills = $this->config->get("pt_kills") * $this->multiplicador;
		//--------------------------------------------------------------
		//pontuações do crafter-----------------------------------------
		$this->pt_crafts = $this->config->get("pt_crafts") * $this->multiplicador;
		$this->pt_crafts_couro_capuz = $this->config->get("pt_crafts_couro_capuz") * $this->multiplicador;
		$this->pt_crafts_ferro_capuz = $this->config->get("pt_crafts_ferro_capuz") * $this->multiplicador;
		$this->pt_crafts_ouro_capuz = $this->config->get("pt_crafts_ouro_capuz") * $this->multiplicador;
		$this->pt_crafts_diamante_capuz = $this->config->get("pt_crafts_diamante_capuz") * $this->multiplicador;
		 
		$this->pt_crafts_couro_tunica = $this->config->get("pt_crafts_couro_tunica") * $this->multiplicador;
		$this->pt_crafts_ferro_tunica = $this->config->get("pt_crafts_ferro_tunica") * $this->multiplicador;
		$this->pt_crafts_ouro_tunica = $this->config->get("pt_crafts_ouro_tunica") * $this->multiplicador;
		$this->pt_crafts_diamante_tunica = $this->config->get("pt_crafts_diamante_tunica") * $this->multiplicador;
			 
		$this->pt_crafts_couro_calca = $this->config->get("pt_crafts_couro_calca") * $this->multiplicador;
		$this->pt_crafts_ferro_calca = $this->config->get("pt_crafts_ferro_calca") * $this->multiplicador;
		$this->pt_crafts_ouro_calca = $this->config->get("pt_crafts_ouro_calca") * $this->multiplicador;
		$this->pt_crafts_diamante_calca = $this->config->get("pt_crafts_diamante_calca") * $this->multiplicador;
			 
		$this->pt_crafts_couro_botas = $this->config->get("pt_crafts_couro_botas") * $this->multiplicador;
		$this->pt_crafts_ferro_botas = $this->config->get("pt_crafts_ferro_botas") * $this->multiplicador;
		$this->pt_crafts_ouro_botas = $this->config->get("pt_crafts_ouro_botas") * $this->multiplicador;
		$this->pt_crafts_diamante_botas = $this->config->get("pt_crafts_diamante_botas") * $this->multiplicador;
			 
		$this->pt_crafts_madeira_espada = $this->config->get("pt_crafts_madeira_espada") * $this->multiplicador;
		$this->pt_crafts_pedra_espada = $this->config->get("pt_crafts_pedra_espada") * $this->multiplicador;
		$this->pt_crafts_ferro_espada = $this->config->get("pt_crafts_ferro_espada") * $this->multiplicador;
		$this->pt_crafts_ouro_espada = $this->config->get("pt_crafts_ouro_espada") * $this->multiplicador;
		$this->pt_crafts_diamante_espada = $this->config->get("pt_crafts_diamante_espada") * $this->multiplicador;
			 
		$this->pt_crafts_madeira_picareta = $this->config->get("pt_crafts_madeira_picareta") * $this->multiplicador;
		$this->pt_crafts_pedra_picareta = $this->config->get("pt_crafts_pedra_picareta") * $this->multiplicador;
		$this->pt_crafts_ferro_picareta = $this->config->get("pt_crafts_ferro_picareta") * $this->multiplicador;
		$this->pt_crafts_ouro_picareta = $this->config->get("pt_crafts_ouro_picareta") * $this->multiplicador;
		$this->pt_crafts_diamante_picareta = $this->config->get("pt_crafts_diamante_picareta") * $this->multiplicador;
			 
		$this->pt_crafts_madeira_pa = $this->config->get("pt_crafts_madeira_pa") * $this->multiplicador;
		$this->pt_crafts_pedra_pa = $this->config->get("pt_crafts_pedra_pa") * $this->multiplicador;
		$this->pt_crafts_ferro_pa = $this->config->get("pt_crafts_ferro_pa") * $this->multiplicador;
		$this->pt_crafts_ouro_pa = $this->config->get("pt_crafts_ouro_pa") * $this->multiplicador;
		$this->pt_crafts_diamante_pa = $this->config->get("pt_crafts_diamante_pa") * $this->multiplicador;
			 
		$this->pt_crafts_madeira_machado = $this->config->get("pt_crafts_madeira_machado") * $this->multiplicador;
		$this->pt_crafts_pedra_machado = $this->config->get("pt_crafts_pedra_machado") * $this->multiplicador;
		$this->pt_crafts_ferro_machado = $this->config->get("pt_crafts_ferro_machado") * $this->multiplicador;
		$this->pt_crafts_ouro_machado = $this->config->get("pt_crafts_ouro_machado") * $this->multiplicador;
		$this->pt_crafts_diamante_machado = $this->config->get("pt_crafts_diamante_machado") * $this->multiplicador;
			 
		$this->pt_crafts_madeira_enchada = $this->config->get("pt_crafts_madeira_enchada") * $this->multiplicador;
		$this->pt_crafts_pedra_enchada = $this->config->get("pt_crafts_pedra_enchada") * $this->multiplicador;
		$this->pt_crafts_ferro_enchada = $this->config->get("pt_crafts_ferro_enchada") * $this->multiplicador;
		$this->pt_crafts_ouro_enchada = $this->config->get("pt_crafts_ouro_enchada") * $this->multiplicador;
		$this->pt_crafts_diamante_enchada = $this->config->get("pt_crafts_diamante_enchada") * $this->multiplicador;
		
	}
	public function logOnConsole($message){
		$logger = Server::getInstance()->getLogger();
		$logger->info("§9[TotalStats]§b " . $message);
	}
    public function onDisable(){
		$this->atualizaPontos();
        $this->logOnConsole(TextFormat::RED."- TotalStats desabilitado !");
    }
	public static function getInstance(){
		return self::$instance;
	}
	public function onLoad(){
		self::$instance = $this;
	}
    public function onCommand(CommandSender $sender,Command $command,$label,array $args){
        if($sender instanceof Player){
            if($command == "stats"){
                if(isset($args[0])){
                    if($args[0] instanceof Player or $args[0] instanceof OfflinePlayer){ // ???
                        
						$name = strtolower($args[0]);
						
                        if(isset($name)) {
							$stats = $this->varPontos($name);
                            $kills = $stats["kills"]; $deaths = $stats["deaths"];  
                            $breaks = $stats["breaks"]; $places = $stats["places"]; 
                            $kicks = $stats["kicked"]; $joins = $stats["joins"]; $quits = $stats["quits"];
							$crafts = $stats["crafts"];
                            $sender->sendMessage(TextFormat::GREEN . "---- " . $args[0]->getName() . " stats");
                            $sender->sendMessage(TextFormat::GREEN . "Kills: " . $kills);
                            $sender->sendMessage(TextFormat::GREEN . "Deaths: " . $deaths);
                            $sender->sendMessage(TextFormat::GREEN . "Breaks: " . $breaks);
                            $sender->sendMessage(TextFormat::GREEN . "Places: " . $places);
                            $sender->sendMessage(TextFormat::GREEN . "Kicks: " . $kicks);
                            $sender->sendMessage(TextFormat::GREEN . "Joins: " . $joins);
                            $sender->sendMessage(TextFormat::GREEN . "Quits: " . $quits);
							$sender->sendMessage(TextFormat::GREEN . "Crafts: " . $crafts);
							unset($stats);
                        }else{
                            $sender->sendMessage(TextFormat::RED."- Jogador não encontrado !");
                            
                        }
                    }else{
                        $sender->sendMessage(TextFormat::RED."- Player offline !");
                    }
					return true;
                }else{
                    $stats = $this->varPontos[strtolower($sender->getName())];
                    $kills = $stats["kills"]; $deaths = $stats["deaths"]; $breaks = $stats["breaks"];
                    $places = $stats["places"]; $kicks = $stats["kicked"]; $joins = $stats["joins"]; $quits = $stats["quits"];
					$crafts = $stats["crafts"];
					$miner = $stats["miner"];
					$lenhador = $stats["lenhador"];
					$pontos = $stats["pontos"];
                    $sender->sendMessage(TextFormat::GREEN."---- ".$sender->getName()." stats ----");
					$sender->sendMessage(TextFormat::GREEN."Pontos: " .$pontos);
                    $sender->sendMessage(TextFormat::GREEN."Assassinatos: ".$kills);
                    $sender->sendMessage(TextFormat::GREEN."Mortes: ".$deaths);
					$sender->sendMessage(TextFormat::GREEN."Crafts: " .$crafts);
					$sender->sendMessage(TextFormat::GREEN."Miner: " .$miner);
					$sender->sendMessage(TextFormat::GREEN."Lenhador: " .$lenhador);
					return true;
                }
            }
			if($command == "pvpr"){
				$ranking = [];
				$sql = $this->db->query("SELECT * FROM `players_pvpranqueado`");
				$n = 1;
				$n2 = 0;
				$h = [];
				foreach($sql as $r){
					$h[$r["nome"]] = $r["pontos"];
				}
				arsort($h);
				$j = [];
				foreach($sql as $r){
					$stats["pontos"] = $r["pontos"];
					$stats["combates"] = $r["combates"];
					$stats["vitorias"] = $r["vitorias"];
					$stats["derrotas"] = $r["derrotas"];
					$j[$r["nome"]] = $stats;
				}
				unset($stats);
				foreach($h as $key => $r){
					$stats["pontos"] = $j[$key]["pontos"];
					$stats["combates"] = $j[$key]["combates"];
					$stats["vitorias"] = $j[$key]["vitorias"];
					$stats["derrotas"] = $j[$key]["derrotas"];
					$ranking[$n][$key] = $stats;
					$n2++;
					if($n2 == 5){
						$n2 = 0;
						$n++;
					}
				}
				$pg = 1;
				if(isset($args[0])){
					$pg = $args[0];
					
				}else{
					$pg = 1;
				}
				if($pg > count($ranking)){
					$pg = count($ranking);
				}
				if(is_numeric($pg)){
					$nspc = 0;
					foreach($ranking[$pg] as $key => $r){
						if(strlen($key) > $nspc){
							$nspc = strlen($key);
						}
					}
					$rank = 1 + (($pg - 1) * 5);
					$sender->sendMessage(TextFormat::GREEN."--Ranking - PVP Ranqueado--pg " . $pg . "/" . count($ranking));
					$sender->sendMessage(TextFormat::GREEN. sprintf("%-7s",..."Rank") . sprintf("%-".$nspc."s", "Nome") . "- combates - vitorias - derrotas - pontos");
					
					foreach($ranking[$pg] as $key => $r){
						
						if(strtolower($key) == strtolower($sender->getName())){
								//$strR = $rank . ": ";
								$sender->sendMessage(TextFormat::GREEN."§9" . $rank . ":    " . $key . " - " . $r["combates"] . " - " . $r["vitorias"] . " - " . $r["derrotas"] . " - " . $r["pontos"]);
							}else{
								//$strR = $rank . ": ";
								$sender->sendMessage(TextFormat::GREEN."§6" . $rank . ":    " . "§e" . $key . " - " . $r["combates"] . " - " . $r["vitorias"] . " - " . $r["derrotas"] . " - " . $r["pontos"]);
							}
						$rank++;
					}
					return true;
				}else{
					$sim = false;
					$rank = 1;
					foreach($ranking as $key => $r){
						foreach($r as $key2 => $r2){
							if(strtolower($args[0]) == strtolower($key2)){
								$stats = $r2;
								$stats["nome"] = $key2;
								$stats["pg"] = $key;
								$sim = true;
								break;
							}
							
							$rank++;
						}
						if($sim){
							break;
						}
					}
					if($sim){
						$pg = $stats["pg"];
						$rank = 1 + (($pg - 1) * 5);
						$sender->sendMessage(TextFormat::GREEN."--Ranking - PVP Ranqueado--pg " . $pg . "/" . count($ranking));
						$sender->sendMessage(TextFormat::GREEN."Rank - nome - combates - vitorias - derrotas - pontos");
						foreach($ranking[$pg] as $key => $r){
							if(strtolower($key) == strtolower($args[0])){
								$sender->sendMessage(TextFormat::GREEN."§9" . $rank . ": " . $key . " - " . $r["combates"] . " - " . $r["vitorias"] . " - " . $r["derrotas"] . " - " . $r["pontos"]);
							}else{
								$sender->sendMessage(TextFormat::GREEN."§6" . $rank . ": §e" . $key . " - " . $r["combates"] . " - " . $r["vitorias"] . " - " . $r["derrotas"] . " - " . $r["pontos"]);
							}
							
							$rank++;
						}
						return true;
					}else{
						$sender->sendMessage(TextFormat::GREEN."§6Jogador " . $args[0] . " não encontrado.");
						$sender->sendMessage(TextFormat::GREEN."§6/pvpr [pg|nomedojogador]");
						return true;
					}
				}
				
			}
			if($command == "ranking"){
				$ranking = $this->getRanking();
				$linhas = mysqli_num_rows($ranking);
				$paginas = (int) ($linhas / 10);
				if(isset($args[0])){
					if(is_numeric($args[0])){
						$sender->sendMessage(TextFormat::GREEN."---- RANKING PG " . $args[0] . " / " . $paginas . " ----");
						$sender->sendMessage(TextFormat::GREEN."POS: NOME - PONTOS");
						$n = 1;
						$n2 = 0;
						$ini = (($args[0] * 10) - 10);
						$fim = ($args[0] * 10);
						foreach($ranking as $place){
							if($n < $fim){
								if($n < $ini){
									
								}else{
									$sender->sendMessage(TextFormat::GREEN. $n . "º: " . $place['name'] . " - " . $place['pontos']);
								}
								$n++;
							}else{
								break;
							}
						}
					}else{
						$n = 1;
						$achou = true;
						foreach($ranking as $place){
							if($place['name'] == trim(strtolower($args[0]))){
								$sender->sendMessage(TextFormat::GREEN."---- RANKING " . $args[0] . " ----");
								$sender->sendMessage(TextFormat::GREEN. $n . "º: " . $place['name'] . " - " . $place['pontos']);
								$achou = true;
								break;
								
							}else{
								$achou = false;
								$n++;
							}
						}
						if($achou){
							
						}else{
							$sender->sendMessage(TextFormat::RED."Digite uma página ou nome de jogador válido!");
						}
					}
				}else{
					$sender->sendMessage(TextFormat::GREEN."---- RANKING PG 1 / " . $paginas . " ----");
					$sender->sendMessage(TextFormat::GREEN."POS: NOME - PONTOS");
					$n = 1;
					foreach($ranking as $place){
						if($n < 11){
							$sender->sendMessage(TextFormat::GREEN. $n . "º: " . $place['name'] . " - " . $place['pontos']);
							$n++;
						}else{
							break;
						}
					}
				}
				return true;
			}
        }else{
			if($command == "ranking"){
				$ranking = $this->getRanking();
				$linhas = mysqli_num_rows($ranking);
				$paginas = (int) ($linhas / 20);
				if(isset($args[0])){
					
					if(is_numeric($args[0])){
						$sender->sendMessage(TextFormat::GREEN."---- RANKING PG " . $args[0] . " / " . $paginas . " ----");
						$sender->sendMessage(TextFormat::GREEN."POS: NOME - PONTOS");
						$n = 1;
						$n2 = 0;
						$ini = (($args[0] * 20) - 20);
						$fim = ($args[0] * 20);
						foreach($ranking as $place){
							if($n < $fim){
								if($n < $ini){
									
								}else{
									$sender->sendMessage(TextFormat::GREEN. $n . "º: " . $place['name'] . " - " . $place['pontos']);
								}
								$n++;
							}else{
								break;
							}
						}
					}else{
						$n = 1;
						$achou = true;
						foreach($ranking as $place){
							if($place['name'] == trim(strtolower($args[0]))){
								$sender->sendMessage(TextFormat::GREEN."---- RANKING " . $args[0] . " ----");
								$sender->sendMessage(TextFormat::GREEN. $n . "º: " . $place['name'] . " - " . $place['pontos']);
								$achou = true;
								break;
								
							}else{
								$achou = false;
								$n++;
							}
						}
						if($achou){
							
						}else{
							$sender->sendMessage(TextFormat::RED."Digite uma página ou nome de jogador válido!");
						}
					}
				}else{
					$sender->sendMessage(TextFormat::GREEN."---- RANKING PG 1 / " . $paginas . " ----");
					$sender->sendMessage(TextFormat::GREEN."POS: NOME - PONTOS");
					$n = 1;
					foreach($ranking as $place){
						if($n < 21){
							$sender->sendMessage(TextFormat::GREEN. $n . "º: " . $place['name'] . " - " . $place['pontos']);
							$n++;
						}else{
							break;
						}
					}
				}
				return true;
			}
			if($command == "oldt"){
				if(!isset($args[0])){
					
					$this->saveOldR();
					return true;
				}else{
					if($args[0] == "kickall"){
						zBattleLogger::getInstance()->liberarLutadores();
						$players = $this->getServer()->getOnlinePlayers();
						foreach($players as $key => $r){
							$r->kick("O servidor será reiniciado, desculpe pelo inconveniênte.\nEm breve estaremos de volta com a diversão, aproveite esse tempo para votar no servidor e receber prêmios.\nAcesse §6www.brasilcraft-pa.esy.es §f para mais informações", false);
						}
						
					}elseif($args[0] == "funil"){
						$levels = $this->getServer()->getLevels();
						foreach($levels as $level){
							$this->logOnConsole($level->getName());
							
							foreach($level->getTiles() as $tile){
								
								if ($tile->getName() == 'Hopper'){
									$x = $tile->getX();
									$y = $tile->getY();
									$z = $tile->getZ();
									$this->logOnConsole($x . " " . $y . " " . $z);
									
									$level->setBlock($tile, new Block(0, 0), true, true);
								}
							}
						}
					}else{
						$this->removeInativos($args[0]);
					}
					
					return true;
				}
				
			}
			if($command == "rconf"){
				$this->reloadConf();
				$this->logOnConsole("Configurações recarregadas!");
				return true;
			}
			
        }
    }

	public function getAll(){
		return $this->varPontos;
	}
	
    /* ---------------- API PART -------------------*/
	public function getRanking(){
		$result = $this->db->query("SELECT * FROM `player_stats` ORDER BY `pontos` DESC");
		return $result;
	}

	public function getTop(string $f){
		if(!isset($f)){
			$f = "places";
		}
		$result = $this->db->query("SELECT * FROM `player_stats` ORDER BY `" . $f . "` DESC");
		if($result instanceof \mysqli_result){
			return $result;
		}
		return null;
	}

    /* -----------------NON API PART---------------*/


    /* ------------------ EVENTS ------------------*/
	private $idblock;
    /**
     * @param BlockBreakEvent $e
     */
    public function BlockBreakEvent(BlockBreakEvent $e){
        
		$this->updMultiplicador($e->getPlayer());
		$this->updPontos();
		
		$block = $e->getBlock();
		$idblock = $block->getID();
		$block_damage = $block->getDamage();
		$name = strtolower($this->db->escape_string($e->getPlayer()->getName()));
		
	

		if($e->getPlayer()->isCreative()){
			
		}else{
			if(!$e->isCancelled()){
					
					switch($idblock){
						
						case 14:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_ouro"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_ouro + $this->pt_miner);
							
							break;
						case 15:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_ferro"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_ferro + $this->pt_miner);
							
							break;
						case 16:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_carvao"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_carvao + $this->pt_miner);
							
							break;
						case 17:
							switch($block_damage){
								case 0:
								case 4:
									
									$this->varPontos[$name]["breaks"] += 1;
									$this->varPontos[$name]["lenhador"] += 1;
									$this->varPontos[$name]["pontos"] += ($this->pt_lenhador + $this->pt_lenhador_carvalho);
									
									break;
								case 1:
								case 5:
									
									$this->varPontos[$name]["breaks"] += 1;
									$this->varPontos[$name]["lenhador"] += 1;
									$this->varPontos[$name]["pontos"] += ($this->pt_lenhador + $this->pt_lenhador_pinheiro);
									
									break;
								case 2:
								case 10:
									
									$this->varPontos[$name]["breaks"] += 1;
									$this->varPontos[$name]["lenhador"] += 1;
									$this->varPontos[$name]["pontos"] += ($this->pt_lenhador + $this->pt_lenhador_eucalipito);
									
									break;
								case 3:
								case 11:
									
									$this->varPontos[$name]["breaks"] += 1;
									$this->varPontos[$name]["lenhador"] += 1;
									$this->varPontos[$name]["pontos"] += ($this->pt_lenhador + $this->pt_lenhador_selva);
									
									break;
							}
							break;
						case 21:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_lapislazuli"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_lapislazuli + $this->pt_miner);
							
							break;
						case 22:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["pontos"] += $this->pt_quebrar;
							
							break;
						case 41:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["pontos"] += $this->pt_quebrar;
							
							break;
						case 42:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["pontos"] += $this->pt_quebrar;
							
							break;
						case 56:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_diamante"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_diamante + $this->pt_miner);
							
							break;
						case 73:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_redstone"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_redstone + $this->pt_miner);
							
							break;
						case 129:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["m_esmeralda"] += 1;
							$this->varPontos[$name]["miner"] += 1;
							$this->varPontos[$name]["pontos"] += ($this->pt_miner_esmeralda + $this->pt_miner);
							
							break;
						case 162:
							switch($block_damage){
								case 0:
								case 8:
									
									$this->varPontos[$name]["breaks"] += 1;
									$this->varPontos[$name]["lenhador"] += 1;
									$this->varPontos[$name]["pontos"] += ($this->pt_lenhador_acacia + $this->pt_lenhador);
									
									break;
								case 1:
								case 9:
									
									$this->varPontos[$name]["breaks"] += 1;
									$this->varPontos[$name]["lenhador"] += 1;
									$this->varPontos[$name]["pontos"] += ($this->pt_lenhador_carv_escuro + $this->pt_lenhador);
									
									break;
							}
							break;
						default:
							
							$this->varPontos[$name]["breaks"] += 1;
							$this->varPontos[$name]["pontos"] += $this->pt_quebrar;
							
							break;
						
					}
			   
			}
		}
		unset($timestamp);
		
		unset($block);
		unset($idblock);
		unset($block_damage);
		unset($name);
    }
	public function getPontos(){
		return $this->varPontos;
	}
	public function getKills(){
		$rank = [];
		
		foreach($this->varPontos as $key => $r){
			$rank[$key] = $r["kills"];
			
		}
		arsort($rank);
		return $rank;
		
	}
	public function getCrafts(){
		$rank = [];
		
		foreach($this->varPontos as $key => $r){
			$rank[$key] = $r["crafts"];
			
		}
		arsort($rank);
		return $rank;
		
	}
	public function getLenhador(){
		$rank = [];
		
		foreach($this->varPontos as $key => $r){
			$rank[$key] = $r["lenhador"];
			
		}
		arsort($rank);
		return $rank;
		
	}
	public function getMiner(){
		$rank = [];
		
		foreach($this->varPontos as $key => $r){
			$rank[$key] = $r["miner"];
			
		}
		arsort($rank);
		return $rank;
		
	}
    /**
     * @param PlayerDeathEvent $event
     */
    public function DeathEvent(PlayerDeathEvent $event){
		
		foreach($event->getDrops() as $item){
			
			$event->getEntity()->getLevel()->dropItem($event->getEntity(), $item);
		}
		$victim = $event->getEntity();
		
		$this->updMultiplicador($victim);
		$this->updPontos();
		//pontuação de kills--------------------------------------------
		$pt_kills = $this->pt_kills;
		//--------------------------------------------------------------
				
		$causa = $event->getEntity()->getLastDamageCause()->getCause();
		
		$nodrop = [];
		switch($causa){
			
			case 11:
			if($victim->getLevel()->getName() != "sw2"){
				//Parte para não dropar os itens-----------------------------------------------
				$this->drops[$victim->getName()][1] = $victim->getInventory()->getArmorContents();
				$this->drops[$victim->getName()][0] = $victim->getInventory()->getContents();
				$event->setKeepInventory(true);
				//----------------------------------------------------------------------------
			}
				
				break;
		}
		
		
        if($victim instanceof Player){
            
			$name = strtolower($this->db->escape_string($event->getEntity()->getPlayer()->getName()));
			
			$this->varPontos[$name]["deaths"] += 1;
			
			
			//$exp = mt_rand(2, 6);
			//if($exp > 0) $victim->getLevel()->spawnXPOrb($victim, $exp);
        }
		if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $event->getEntity()->getLastDamageCause()->getDamager()->getName();
			$killer2 = $event->getEntity()->getLastDamageCause()->getDamager();
			if($killer2 instanceof Player){
				$this->updMultiplicador($killer2);
			}
			$this->updPontos();
			
			$name = strtolower($this->db->escape_string($killer));
			if($killer2 instanceof Player){
			
			$this->varPontos[$name]["kills"] += 1;
			$this->varPontos[$name]["pontos"] += $pt_kills;
			
			}
			unset($killer);
			unset($killer2);
		}
		
		unset($victim);
    }
	
	public function PlayerRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
		if (isset($this->drops[$player->getName()])) {
			$player->getInventory()->setContents($this->drops[$player->getName()][0]);
			$player->getInventory()->setArmorContents($this->drops[$player->getName()][1]);
			unset($this->drops[$player->getName()]);
		}
    }

	public function removeInativos($action){
		$jogadores = $this->varPontos;
		$money = EconomyAPI::getInstance()->getAllMoney();
		$excluidos = 0;
		$inativos = 0;
		$eco = 0;
		switch($action){
			case "save":
				zShopStats::getInstance()->saveAll();
				break;
			case "indi":
				zShopStats::getInstance()->ecoIndividual();
				break;
			case "limpar":
				foreach($money as $key => $r){
					if(!isset($jogadores[$key])){
						EconomyAPI::getInstance()->removeAccount($key);
						$eco += $r;
					}
				}
				EconomyAPI::getInstance()->saveAll();
				break;
			case "dados":
				foreach($money as $key => $r){
					if(!isset($jogadores[$key])){
						$inativos++;
						$eco += $r;
					}
				}
				$this->logOnConsole("Inativos: " . $inativos);
				$this->logOnConsole("Money: " . number_format($eco, 2, ",", "."));
				break;
			case "save":
				@mkdir($this->getDataFolder() . "money/");
				$data = new Config($this->getDataFolder() . "money/inativos.yml", Config::YAML);
				$inativos = [];
				$j = 0;
				$total = 0;
				foreach($money as $key => $r){
					if(!isset($jogadores[$key])){
						$inativos[$key] = $r;
						$j++;
						$total += $r;
					}
				}
				arsort($inativos);
				$data->set("inativos", $j);
				$data->set("total", $total);
				$data->set("money", $inativos);
				$data->save();
				break;
		}
	}
	public function carregaPontos(){
		$result = $this->db->query("SELECT * FROM `player_stats`");
		$teste = array();
		$teste2 = array();
		foreach($result as $n){

			foreach($n as $key => $i){
				$teste2[$key] = $i;
				
			}
			$teste[$n["name"]] = $teste2;
		}
		unset($result);
		unset($teste2);
		return $teste;
	}
	public $varranqueado = 0;
	public function zeraRanqueado(){
		if($this->varranqueado == 0){
			$result = $this->db->query("SELECT * FROM `players_pvpranqueado`");
		
			foreach($result as $r){
				if($r['pontos'] > 0){
					$this->db->query("UPDATE `players_pvpranqueado` SET `pontos` = 0 WHERE `nome` = '" . $r["nome"] . "'");
				}
				
				
			}
			$this->varranqueado = 1;
		}
		
	}
	public function atualizaPontos(){

		
		
		$atual = $this->carregaPontos();
		foreach($this->varPontos as $key => $n){
				$timestamp = date('Y-m-d G:i:s');
				if($atual[$key]['pontos'] < $n['pontos']){
					
				
					$this->db->query("UPDATE `player_stats` SET 
					`breaks` = " . $n["breaks"] . ",
					`places` = " . $n["places"] . ",
					`miner` = " . $n["miner"] . ",
					`lenhador` = " . $n["lenhador"] . ",
					`crafts` = " . $n["crafts"] . ",
					`kills` = " . $n["kills"] . ",
					`pontos` = " . $n["pontos"] . ",
					`deaths` = " . $n["deaths"] . ",
					`kicked` = " . $n["kicked"] . ",
					`joins` = " . $n["joins"] . ",
					`quits` = " . $n["quits"] . ",
					`m_ouro` = " . $n["m_ouro"] . ",
					`m_ferro` = " . $n["m_ferro"] . ",
					`m_carvao` = " . $n["m_carvao"] . ",
					`m_diamante` = " . $n["m_diamante"] . ",
					`m_lapislazuli` = " . $n["m_lapislazuli"] . ",
					`m_redstone` = " . $n["m_redstone"] . ",
					`m_esmeralda` = " . $n["m_esmeralda"] . ",
					`atualizado` = '" . $timestamp . "',
					`multiplicador` = '" . $n["multiplicador"] . "' WHERE `name` = '" . $key . "'");
					
					//$this->logOnConsole($key . " - Atualizado");
				}else{
					
				}
				
		}
		//$teste = new Config($this->getDataFolder() . "statss.yml");
		//$teste->set("stats", $this->varPontos);
		//$teste->save();
		unset($atual);
		$this->logOnConsole("Banco de dados atualizado");
		//$this->pontuacao->setAll($this->varPontos);
		//$this->pontuacao->save();
	}
	public function getOp(Player $player){
        $name = trim(strtolower($player->getName()));
        $result = $this->db->query("SELECT * FROM `admins` WHERE `nome` = '".$this->db->escape_string($name)."'");
        if($result instanceof \mysqli_result){
            $data = $result->fetch_assoc();
            return $data;
        }
        return null;
    }

    /**
     * @param BlockPlaceEvent $e
     */
    public function BlockPlaceEvent(BlockPlaceEvent $e){
				
		if(!$e->isCancelled()){
			$name = strtolower($this->db->escape_string($e->getPlayer()->getName()));
			
			$this->varPontos[$name]["places"] += 1;
			$this->varPontos[$name]["pontos"] += $this->pt_construir;
			
			unset($name);
		
		}
		
    }

    /**
     * @param PlayerKickEvent $e
     */
    public function KickEvent(PlayerKickEvent $e){
		
        
		$name = strtolower($this->db->escape_string($e->getPlayer()->getName()));
		$this->varPontos[$name]["kicked"] += 1;
        unset($name);
		unset($timestamp);
    }

    /**
     * @param PlayerJoinEvent $e
     */
   public function JoinEvent(PlayerJoinEvent $e){
        $name = strtolower($e->getPlayer()->getName());
		if(isset($this->varPontos[$name])){
			
		}else{
			$teste = $this->db->query("SELECT * FROM `player_stats` WHERE `name` = 'laurobeleche'");
			$this->db->query("INSERT INTO `player_stats` (`name`) VALUES ('" . $name . "')");
			$data = $teste->fetch_assoc();
			
			foreach($data as $key => $n){
				$this->varPontos[$name][$key] = 0;
				
				if($key == "name"){
					$this->varPontos[$name][$key] = $name;
				}
			}
		}
		$this->varPontos[$name]["joins"] += 1;
		
    }
	private $idrecipe;
	public function onCraftItem(CraftItemEvent $e){
		
		$this->updMultiplicador($e->getPlayer());
		$this->updPontos();	
		$name = strtolower($this->db->escape_string($e->getPlayer()->getName()));
		if($e->getPlayer()->isCreative()){
			
			
		}else{
			
			$idrecipe = $e->getRecipe()->getResult()->getID();
			switch($idrecipe){
				
				case 268:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_madeira_espada);
					break;
				case 270:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_madeira_picareta);
					break;
				case 290:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_madeira_enchada);
					break;
				case 271:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_madeira_machado);
					break;
				case 269:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_madeira_pa);
					break;
				case 272:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_pedra_espada);
					break;
				case 267:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_espada);
					break;
				case 283:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_espada);
					break;
				case 276:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_espada);
					break;
				case 273:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_pedra_pa);
					break;
				case 256:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_pa);
					break;
				case 284:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_pa);
					break;
				case 277:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_pa);
					break;
				case 274:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_pedra_picareta);
					break;
				case 257:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_picareta);
					break;
				case 285:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_picareta);
					break;
				case 278:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_picareta);
					break;
				case 275:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_pedra_machado);
					break;
				case 258:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_machado);
					break;
				case 286:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_machado);
					break;
				case 279:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_machado);
					break;
				case 291:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_pedra_enchada);
					break;
				case 292:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_enchada);
					break;
				case 294:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_enchada);
					break;
				case 293:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_enchada);
					break;
				case 298:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_couro_capuz);
					break;
				case 306:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_capuz);
					break;
				case 314:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_capuz);
					break;
				case 310:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_capuz);
					break;
				case 299:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_couro_tunica);
					break;
				case 307:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_tunica);
					break;
				case 315:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_tunica);
					break;
				case 311:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_tunica);
					break;
				case 300:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_couro_calca);
					break;
				case 308:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_calca);
					break;
				case 316:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_calca);
					break;
				case 312:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_calca);
					break;
				case 301:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_couro_botas);
					break;
				case 309:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ferro_botas);
					break;
				case 317:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_ouro_botas);
					break;
				case 313:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += ($this->pt_crafts + $this->pt_crafts_diamante_botas);
					break;
				default:
					$this->varPontos[$name]["crafts"] += 1;
					$this->varPontos[$name]["pontos"] += $this->pt_crafts;
					break;
				
			}
			
		}
		
	}	

}