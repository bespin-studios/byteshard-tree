<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Tree\Event;
use byteShard\Internal\Event\EventMigrationInterface;
use byteShard\Internal\Event\TreeEvent;

/**
 * Class OnDoubleClick
 * @package byteShard\Tree\Event
 */
class OnDoubleClick extends TreeEvent implements EventMigrationInterface
{
    protected static string $event = 'onDoubleClick';

    public function getClientArray(string $cellNonce): array
    {
        return ['onDblClick' => ['doOnDblClick']];
    }
}
