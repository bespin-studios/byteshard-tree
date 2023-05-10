<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Action\Tree;

use byteShard\Cell;
use byteShard\Internal\Action;
use byteShard\Internal\Debug;

abstract class ModifyTree extends Action
{
    /**
     * @var array
     */
    protected array $cells;

    /**
     * @param string ...$cells
     */
    public function __construct(string ...$cells)
    {
        parent::__construct();
        $this->cells = array_map(function ($cell) {
            if (!Cell::isTreeContent($cell)) {
                Debug::error(__METHOD__.' Action can only be used in Tree');
            }
            return Cell::getContentCellName($cell);
        }, array_unique($cells));
        $this->addUniqueID($this->cells);
    }
}