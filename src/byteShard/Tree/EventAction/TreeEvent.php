<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Tree\EventAction;

use byteShard\Event\EventActionInterface;
use byteShard\Internal\Action;

class TreeEvent implements EventActionInterface
{
    private array $actions;
    public function __construct(Action ...$actions) {
        $this->actions = $actions;
    }

    public function getActions(?string $objectId): array
    {
        return $this->actions;
    }
}