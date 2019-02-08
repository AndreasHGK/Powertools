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
use pocketmine\utils\TextFormat;

class Powertools extends PluginBase implements Listener{

    public $cooldown = [];

    /**
     * @return Powertools $Powertools
     */
    public function getInstance(){
        return $this;
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

    }

    public function onInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        if ($player->hasPermission("powertools.use")) {

            if (isset($this->cooldown[$player->getName()]) && $this->cooldown[$player->getName()] > microtime(true)) {
                $event->setCancelled();
                return;
            }

            $item = $player->getInventory()->getItemInHand();
            if ($this->isPowertool($item) && !in_array($player, $this->cooldown)) {
                $this->getServer()->dispatchCommand($player, $this->checkCommand($item));
                $player->addActionBarMessage(TextFormat::colorize("&e&lPT: &r&7command executed"));
                $this->cooldown[$player->getName()] = microtime(true) + 1;
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
                    $sender->sendMessage(TextFormat::colorize("&c&lError: &r&7you don't have permission to create/remove powertools"));
                    return true;
                    break;
                }

               $item = $sender->getInventory()->getItemInHand();

			    if(!isset($args[0]) && $this->isPowertool($item)){
                    $disabledItem = $this->disablePowertool($item);
                    $sender->getInventory()->setItemInHand($disabledItem);
                    $sender->sendMessage(TextFormat::colorize("&e&lPowertool: &r&7unset this powertool"));
                    return true;
                    break;
                }elseif(isset($args[0]) && !$this->isPowertool($item)){
			        $powertool = $this->enablePowertool($item, implode(" ", $args));
                    $sender->getInventory()->setItemInHand($powertool);
                    $sender->sendMessage(TextFormat::colorize("&e&lPowertool: &r&7set command for this item to: &8").implode(" ", $args));
                }elseif($this->isPowertool($item)){
                    $sender->sendMessage(TextFormat::colorize("&c&lError: &r&7this already is a powertool"));
                }else{
                    $sender->sendMessage(TextFormat::colorize("&c&lError: &r&7you need to enter a command to assign"));
                }


				return true;
			default:
				return false;
		}
	}

}
