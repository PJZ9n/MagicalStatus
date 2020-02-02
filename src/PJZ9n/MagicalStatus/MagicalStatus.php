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

namespace PJZ9n\MagicalStatus;

use PJZ9n\MagicalStatus\Parts\Parts;
use PJZ9n\MagicalStatus\Task\SendStatusTask;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

class MagicalStatus extends PluginBase
{
    
    /** @var string[] */
    private $availableParts;
    
    /**
     * @throws ReflectionException
     */
    public function onEnable(): void
    {
        $this->initConfig();
        $this->checkAgree();
        $this->initParts();
        
        $this->getScheduler()->scheduleRepeatingTask(new SendStatusTask($this->availableParts,
            $this->getConfig()),
            (int)$this->getConfig()->get("interval-tick", 20)
        );
    }
    
    private function initConfig(): void
    {
        $this->saveDefaultConfig();
        $this->reloadConfig();
    }
    
    private function checkAgree(): void
    {
        //TODO 既存のライブラリを使う
        if ($this->getConfig()->get("agree", false)) {
            //すでに同意している
            return;
        }
        //同意していない
        $this->getLogger()->notice("このプラグインをご利用頂くにあたって、注意して頂きたい事があります。");
        $this->getLogger()->notice("このプラグインには、簡単に部品を追加できる機能が搭載されています。");
        $this->getLogger()->notice("この機能は便利な反面使い方を誤ると危険な面も持ち合わせています。");
        $this->getLogger()->notice("特に、信頼されない場所からダウンロードされた部品や、見知らぬ人から貰った部品などを無闇に入れることはとても危険です。");
        $this->getLogger()->notice("理解して使用する場合は、config.ymlにあるagreeの値をtrueに変更してください。");
        $this->getLogger()->notice("例: agree: false => agree: true");
        //RuntimeExceptionの使い方としてよろしくないかも
        throw new RuntimeException("注意事項に同意していません");
    }
    
    /**
     * @throws ReflectionException
     */
    private function initParts(): void
    {
        //TODO 全体的に仕様を見直す必要がある。特に名前空間など
        
        //フォルダチェック
        $partsFolder = $this->getDataFolder() . "parts/";
        if (!file_exists($partsFolder)) mkdir($partsFolder);
        //デフォルト部品を全部保存
        foreach ($this->getResources() as $resourcePath => $resource) {
            if (strpos($resourcePath, "parts/") === 0 && $resource->getExtension() === "parts") {
                $this->saveResource($resourcePath, false);
                $this->getLogger()->debug("部品 " . $resourcePath . " がセーブされました。");
            }
        }
        //インクルード
        foreach (glob($this->getDataFolder() . "parts/*.parts") as $partsFile) {
            $this->getLogger()->debug("部品 " . strstr($partsFile, "parts/") . " を読み込みます。");
            try {
                include $partsFile;
            } catch (Throwable $throwable) {
                $this->getLogger()->warning("部品 " . strstr($partsFile, "parts/") . " を読み込むことが出来ませんでした。");
            }
        }
        //検証
        $this->availableParts = [];
        foreach (get_declared_classes() as $declaredClass) {
            if (strpos($declaredClass, 'PJZ9n\\MagicalStatus\\Parts\\Defined') === 0/*先頭*/) {
                //パーツを見つける
                $refrectionClass = new ReflectionClass($declaredClass);
                if ($refrectionClass->getNamespaceName() === "PJZ9n\\MagicalStatus\\Parts\\Defined") {
                    //名前空間が合っている
                    $className = $refrectionClass->getName();
                    $parts = new $className;
                    if ($parts instanceof Parts) {
                        //ちゃんとPartsを実装していたら
                        $this->availableParts[$parts->getName()] = $parts;
                        $this->getLogger()->debug("部品 " . $parts->getName() . " を読み込みました。");
                    } else {
                        $this->getLogger()->warning("部品 " . strstr($refrectionClass->getFileName(), "parts/") . " は、正常に実装されていないため読み込めませんでした。");
                    }
                } else {
                    $this->getLogger()->warning("部品 " . strstr($refrectionClass->getFileName(), "parts/") . " の名前空間が不正なため読み込めませんでした。");
                }
            }
        }
        //結果
        $this->getLogger()->info(count($this->availableParts) . "個の部品が読み込まれています。");
    }
    
}