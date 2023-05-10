<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Tree\Event;

use byteShard\Internal\Event\EventMigrationInterface;
use byteShard\Internal\Event\TreeEvent;

/**
 * Class OnSelect
 * @package byteShard\Tree\Event
 */
class OnSelect extends TreeEvent implements EventMigrationInterface
{
    protected static string $event = 'onSelect';

    public function getClientArray(string $cellNonce): array
    {
        return ['onSelect' => ['doOnSelect']];
    }
}
