<?php

declare(strict_types=1);

namespace AndreasHGK\Powertools;

use AndreasHGK\Powertools\Powertools;
use pocketmine\scheduler\Task;

class ExecuteCooldown extends Task{

    public $plugin;
    public $ticks = 0;
    public $player;


    public function __construct(Powertools $plugin) {
        $this->plugin = $plugin;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function onRun($tick) {
        if($this->ticks === 10) {
            $this->getPlugin()->removeTask($this->getTaskId());
        }
        $this->ticks++;
    }
}