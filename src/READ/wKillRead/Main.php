<?php //Plugin wKillRead от [S]Svyt - Святослава Дубровского (vk.com/plugin_pe and infomcpe.ru)

namespace READ\wKillRead; //Путь к основному файлу

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Effect;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use onebone\economyapi\EconomyAPI; //Берем экономику
use _64FF00\PurePerms\PurePerms; //Получаем доступ к permissions
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender; //Консоль КМД

	class Main extends PluginBase implements Listener{
		
		public function onEnable() //Включаем плагин при старте сервера
		{
		    $this->saveDefaultConfig();
		    $this->reloadConfig();	
			$this->getServer()->getPluginManager()->registerEvents($this,$this);
			$this->getLogger()->info("[wKillRead] Плагин успешно включен (vk.com/plugin_pe| by [S]vyt)");
			$this->api = EconomyAPI::getInstance();
			if (!$this->api) {
			$this->getLogger()->info(TextFormat::RED."[wKillRead\Error] Не найден плагин EconomyAPI!");
			return true;
			}
		}
		
		public function onCommand(CommandSender $sender, Command $command, $label, array $args)
		{
			$cfg = $this->getConfig();
			$duration = $cfg->get("Duration");
			$particles = $cfg->get("Particles");
			$amplifier = $cfg->get("Amplifier");
			$getx2 = $cfg->get("Teg-x2");
			$getx3 = $cfg->get("Teg-x3");
			$give = $cfg->get("GiveMoney");
			
			$id = $cfg->get("ID-effect");
			$player = strtolower($sender->getName());
			if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null)
			{
				$money = $this->api->myMoney($sender);		
			}
			switch($command->getName())
			{
				case "wkset":
				if(count($args) == 0 && $sender->hasPermission("wkillread.op"))
				{
					$sender->sendMessage("§f[§6INFO§f] Плагин wKillRead v1.0.3 §6by WMaster");
					$sender->sendMessage("§f-> §6/wkmoney §f- Сколько игроки получают за 1 убийство");
					$sender->sendMessage("§f-> §6/wkeffect §f- Какой эффект получают игроки");
					$sender->sendMessage("§f-> §6/wkx2 §f- Какой тэг выводит с правом wkillread.x2");
					$sender->sendMessage("§f-> §6/wkx3 §f- Какой тэг выводит с правом wkillread.x3");
					$sender->sendMessage("§f-> §6/wktg §f- Тэг при убийстве игрока в общий чат");
					$sender->sendMessage("§f-> §6/wkbc §f- Показывать сообщение всем об убийстве игрока?");
					return true;
				}
				else
				{
					$sender->sendMessage("§сВы не имеете доступа к данной команде");
					return true;
				}
			
					case "wkbc":
					if(count($args) == 1)
					{
						if($args[0] == "1")
						{
							$sender->sendMessage("§f[§2Успешно§f] Теперь игроки §2будут§f видеть сообщения об убийстве в чате");
							$cfg->set("BroadCast",1);
							$cfg->save();					
							return true;
						}
						if($args[0] == "0")
						{
							$sender->sendMessage("§f[§2Успешно§f] Теперь игроки §cне будут§f видеть сообщения об убийстве в чате");
							$cfg->set("BroadCast",0);
							$cfg->save();					
							return true;
						}
						else
						{
							$sender->sendMessage("§f-§6 Введите§f: /wkbc <1(Да)/0(Нет)>");
							return true;
						}
					}

					case "wkmoney":
					if(count($args) == 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f[§2Успешно§f] Теперь за убийство игроки будут получать ".$args[0]."");
						$cfg = new Config($this->getDataFolder().'config.yml', Config::YAML);
						//$cfg->setAll($this->users);
						$cfg->set("GiveMoney",$args[0]);
						$cfg->save();					
						return true;
					}
					if(!count($args) == 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f-§6 Введите§f: /wkmoney <Значение>");
						return true;
					}
					else 
					{
						$sender->sendMessage("§сВы не имеете доступа к данной команде");
						return true;
					}
					case "wkeffect":
					if(count($args) == 4 && $sender->hasPermission("wkillread.op"))
					{
						/*if($args[3] < 0 || $args[3] > 1)
						{
							$sender->sendMessage("§f-§4 Ошибка§: Вы не ввели 1(Да) или 0(Нет) в раздел частицы");
							return true;
						}*/
						if($args[0] < 0 || $args[0] > 22)
						{
							$sender->sendMessage("§f-§4 Ошибка§f: ID эффекта может быть не меньше 0 и не больше 23");
							return true;
						}
						if($args[3] > 1 || $args[3] < 0)
						{
							$sender->sendMessage("§f- §4Ошибка§f: Вы поставили неверное значение частиц, 1 это да, 0 это нет.");
							return true;
						}
						if($args[3] == "1")
						{
							$args[3] = "true";
						}
						if($args[3] == "0")
						{
							$args[3] = "false";
						}

								
						$sender->sendMessage("§f[§2Успешно§f] Теперь за убийство игроки будут получать эффект §6".$args[0]);
						$sender->sendMessage("§6-> §fСила эффекта: §6".$args[1]."§f | Длительность(сек): §6".$args[2]." §f| Частицы: §6".$args[3]);
						$args[2] = $args[2] * 20;
						$cfg->set("ID-effect",$args[0]);
						$cfg->set("Amplifier",$args[1]);
						$cfg->set("Duration",$args[2]);
						$cfg->set("Particles",$args[3]);
						$cfg->save();
						
						return true;
					}
					if(count($args)  != 4 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f-§6 Введите§f: /wkeffect <ID-Эффекта> <Сила> <Длительность(Сек)> <Частицы>(§21§f\§c0§f)");
						return true;
					}
					else
					{
						$sender->sendMessage("§сВы не имеете доступа к данной команде");
						return true;
					}
					case "wkx2":
					if(count($args) == 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f[§2Успешно§f] Тэг x2 установлен на ".$args[0]);
						$cfg->set("Teg-x2",$args[0]);
						return true;
					}
					if(count($args) != 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f-§6 Введите§f: /wkx2 <Текст>");
						return true;
					}
					else 
					{
						$sender->sendMessage("§сВы не имеете доступа к данной команде");
						return true;
					}
					case "wkx3":
					if(count($args) == 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f[§2Успешно§f] Тэг x3 установлен на ".$args[0]);
						$cfg->set("Teg-x3",$args[0]);
						return true;
					}
					if(count($args) != 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f-§6 Введите§f: /wk x3 <Текст>");
						return true;
					}
					else 
					{
						$sender->sendMessage("§сВы не имеете доступа к данной команде");
						return true;
					}
					case "wktg":
					if(count($args) == 1 && $sender->hasPermission("wkillread.op"))
					{
						$args[0] = str_replace(".", " ", $args[0]);
						$sender->sendMessage("§f[§2Успешно§f] Тэг общего сообщения установлен на ".$args[0]);
						$cfg->set("tegkill",$args[0]);
						return true;
					}
					if(count($args) != 1 && $sender->hasPermission("wkillread.op"))
					{
						$sender->sendMessage("§f-§6 Введите§f: /wktg <Текст>");
						return true;
					}
					else 
					{
						$sender->sendMessage("§сВы не имеете доступа к данной команде");
						return true;
					}
					case "wkcon":
					if($sender->hasPermission("wkillread.op"))
					{
						$args[0] = str_replace(".", " ", $args[0]);
						$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $args[0]);
						return true;
					}
					else 
					{
						$sender->sendMessage("§сВы не имеете доступа к данной команде");
						return true;
					}
				
			}
		}
		public function onPlayerDeathEvent(PlayerDeathEvent $event) //Основной паблик, убийца и убитый
		{
			$event->setDeathMessage(null);
			$cfg = $this->getConfig();
			$duration = $cfg->get("Duration");
			$particles = $cfg->get("Particles");
			$amplifier = $cfg->get("Amplifier");
			$getx2 = $cfg->get("Teg-x2");
			$getx3 = $cfg->get("Teg-x3");
			$give = $cfg->get("GiveMoney");
			$tegkill = $cfg->get("tegkill");//BroadCast
			$broadcast = $cfg->get("BroadCast");
			
			$id = $cfg->get("ID-effect");
			
			$effect = Effect::getEffect($id);
	                $effect->setVisible($particles);
	                $effect->setAmplifier($amplifier);
	                $effect->setDuration($duration);
			
			$player = $event->getEntity();
			$name = strtolower($player->getName());
		
			if ($player instanceof Player)
			{
				$cause = $player->getLastDamageCause();
		
				if($cause instanceof EntityDamageByEntityEvent)
				{
					
					$damager = $cause->getDamager();
					
					if($damager instanceof Player) 
					{
						$killer = $damager->getName();
						$dead = $player->getName();
						if($damager->hasPermission("skillread.x3") || $damager->hasPermission("wkillread.x3"))
						{
							$give = $give * 3;
							$damager->sendMessage("§f[§6Kill§f] Вы убили игрока ".$dead." и получаете §2$".$give."§f(x3)!");
							$damager->sendMessage($getx3."§f Вы получили §6x3 money§f!");
							$damager->addEffect($effect);
							$this->api->addMoney($damager, $give);
							$player->sendMessage("§f[§6Kill§f] §fТебя убил ".$getx3." §fигрок §4 ".$killer);
							foreach($this->getServer()->getOnlinePlayers() as $p)
							{
								$this->getServer()->broadcastTip("§fИгрок ".$killer." убил игрока ".$dead);
								if($broadcast == 1)
								{
								$this->getServer()->broadcastMessage($tegkill."§fИгрок ".$killer." убил игрока ".$dead);
								return true;
								}
							}
						}						
						else if($damager->hasPermission("skillread.x2") || $damager->hasPermission("wkillread.x2"))
						{
							$give = $give * 2;
							$damager->sendMessage("§f[§6Kill§f] Вы убили игрока ".$dead." и получаете §2$".$give."§f(x2)!");
							$damager->sendMessage($getx2."§f Вы получили §6x2 money§f!");
							$damager->addEffect($effect);
							$this->api->addMoney($damager, $give);
							$player->sendMessage("§f[§6Kill§f] §fТебя убил ".$getx2." §fигрок §4 ".$killer);
							foreach($this->getServer()->getOnlinePlayers() as $p)
							{
								$this->getServer()->broadcastTip("§fИгрок ".$killer." убил игрока ".$dead);
								if($broadcast == 1)
								{
								$this->getServer()->broadcastMessage($tegkill."§fИгрок ".$killer." убил игрока ".$dead);
								return true;
								}
							}
						}
						else if($damager->hasPermission("skillread.x1") || $damager->hasPermission("wkillread.x1"))
						{
							$damager->sendMessage("§f[§6Kill§f] Вы убили игрока ".$dead." и получаете §2$".$give."!");
							$damager->addEffect($effect);
							$this->api->addMoney($damager, $give);
							$player->sendMessage("§f[§6Kill§f] §fТебя убил игрок §4 ".$killer);
							foreach($this->getServer()->getOnlinePlayers() as $p)
							{
								$this->getServer()->broadcastTip("§fИгрок ".$killer." убил игрока ".$dead);
								if($broadcast == 1)
								{
									$this->getServer()->broadcastMessage($tegkill."§fИгрок ".$killer." убил игрока ".$dead);
									return true;
								}
							}
						}
					}
				}
			}
		}
		public function onDisable()
		{
			$this->getLogger()->info("[wKillRead] Плагин отключен");
		}
	}
