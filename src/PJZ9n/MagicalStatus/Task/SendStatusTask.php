<?php

/**
 * Copyright (c) 2020 PJZ9n.
 *
 * This file is part of MagicalStatus.
 *
 * MagicalStatus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * MagicalStatus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MagicalStatus.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PJZ9n\MagicalStatus\Task;

use PJZ9n\MagicalStatus\Parts\Parts;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class SendStatusTask extends Task
{
    
    /** @var Parts[] */
    private $availableParts;
    
    /** @var string */
    private $message;
    
    public function __construct(array $availableParts, Config $config)
    {
        $this->availableParts = $availableParts;
        $this->message = "";
        $xMargin = str_repeat(" ", $config->get("x-margin", 0));
        $yMargin = str_repeat("\n", $config->get("y-margin", 0));
        $message = $config->get("message", []);
        foreach ($message as $index => $text) {
            $this->message .= $xMargin . $text;
            if (count($message) - 1 === $index) {
                $this->message .= $yMargin;
            } else {
                $this->message .= "\n";
            }
        }
    }
    
    public function onRun(int $currentTick): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            $replacedMessage = $this->replaceString($onlinePlayer);
            $onlinePlayer->sendTip($replacedMessage);
        }
    }
    
    private function replaceString(Player $player): string
    {
        $replaceMessage = $this->message;
        foreach ($this->availableParts as $partsName => $parts) {
            $replaceMessage = str_replace("{" . $partsName . "}", $parts->getString($player), $replaceMessage);
        }
        return $replaceMessage;
    }
    
}