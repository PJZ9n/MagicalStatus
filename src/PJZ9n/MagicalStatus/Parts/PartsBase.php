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

namespace PJZ9n\MagicalStatus\Parts;

use ReflectionClass;
use ReflectionException;

/**
 * Class PartsBase
 * @package PJZ9n\MagicalStatus\Parts
 *
 * 部品を追加するための基本的なクラス。
 */
abstract class PartsBase implements Parts
{
    
    /** @var string */
    private $name;
    
    /**
     * PartsBase constructor.
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->name = (new ReflectionClass($this))->getShortName();
    }
    
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
}