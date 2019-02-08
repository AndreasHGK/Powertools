<?php

declare(strict_types=1);

namespace AndreasHGK\Powertools;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Powertools extends PluginBase implements Listener{

    public $cooldown = [];
    public $counter = [];

    public static $instance;

    /** @var Config */
    public $messages;

    /**
     * @return Powertools $Powertools
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @return string $version
     */
    public function getVersion() : string{
        return $this->getDescription()->getVersion();
    }

    /**
     * @param item $item
     *
     * @return $item
     */
    public function disablePowertool(item $item) : item{
        $nbt = $item->getNamedTag();
        $nbt->removeTag("powertool");
        $item->setCompoundTag($nbt);
        return $item;
    }

    /**
     * @param item $item
     * @param string $command
     *
     * @return item $item
     */
    public function enablePowertool(item $item, string $command) : item{
        $nbt = $item->getNamedTag();
        $nbt->setString("powertool", $command, true);
        $item->setCompoundTag($nbt);

        return $item;
    }

    /**
     * @param item $item
     *
     * @return bool $result
     */
    public function isPowertool(item $item) : bool{
        $nbt = $item->getNamedTag();
        return $nbt->hasTag("powertool", StringTag::class);
    }

    /**
     * @param item $item
     *
     * @return string $comand
     */
    public function checkCommand(item $item) : string{
        $nbt = $item->getNamedTag();
        return $nbt->getString("powertool");
    }

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        self::$instance = $this;
    }

    public function onLoad(){
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->messages = new Config($this->getDataFolder()."messages.yml",Config::YAML);;
    }

    public function onInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        if ($player->hasPermission("powertools.use")) {

            if (isset($this->cooldown[$player->getName()]) && $this->cooldown[$player->getName()] > microtime(true)) {
                $event->setCancelled();
                return;
            }elseif(isset($this->cooldown[$player->getName()]) && $this->cooldown[$player->getName()] + 0.5 > microtime(true)){
                if(isset($this->counter[$player->getName()])){
                    $this->counter[$player->getName()]++;
                }else{
                    $this->counter[$player->getName()] = 1;
                }
            }else{
                $this->counter[$player->getName()] = 1;
            }

            $item = $player->getInventory()->getItemInHand();
            if ($this->isPowertool($item)) {
                $this->getServer()->dispatchCommand($player, $this->checkCommand($item));
                $str = str_replace("{COUNT}", $this->counter[$player->getName()], $this->messages->get("pt.use"));
                $player->addActionBarMessage(TextFormat::colorize($str));
                $this->cooldown[$player->getName()] = microtime(true) + 0.05;
                $event->setCancelled();
            }
        }
        return;
    }

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if(!($sender instanceof Player)){
            $sender->sendMessage(TextFormat::colorize("&4Please execute this command ingame"));
            return true;
        }

		switch($command->getName()){
            case "pt":
			case "powertool":

			    if(!$sender->hasPermission("powertools.command")) {
                    $sender->sendMessage(TextFormat::colorize($this->messages->get("error.permission")));
                    return true;
                    break;
                }

               $item = $sender->getInventory()->getItemInHand();

			    if(!isset($args[0]) && $this->isPowertool($item)){
                    $disabledItem = $this->disablePowertool($item);
                    $sender->getInventory()->setItemInHand($disabledItem);
                    $sender->sendMessage(TextFormat::colorize($this->messages->get("pt.unset")));
                    return true;
                    break;
                }elseif(isset($args[0]) && !$this->isPowertool($item)){
			        $powertool = $this->enablePowertool($item, implode(" ", $args));
                    $sender->getInventory()->setItemInHand($powertool);
                    $str = str_replace("{CMD}", implode(" ", $args), $this->messages->get("pt.set"));
                    $sender->sendMessage(TextFormat::colorize($str));
                }elseif($this->isPowertool($item)){
                    $sender->sendMessage(TextFormat::colorize($this->messages->get("error.override")));
                }else{
                    $sender->sendMessage(TextFormat::colorize($this->messages->get("error.argument")));
                }


				return true;
			default:
				return false;
		}
	}

}
