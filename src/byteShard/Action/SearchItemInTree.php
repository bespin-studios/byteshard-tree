<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Action;

use byteShard\Cell;
use byteShard\Exception;
use byteShard\Internal\Action;
use byteShard\Internal\Action\ActionResultInterface;
use byteShard\Internal\Action\CellActionResult;

/**
 * Class SearchItemInTree
 * @package byteShard\Action
 */
class SearchItemInTree extends Action
{
    private string $cell;

    /**
     * SearchItemInTree constructor.
     * @param string $cell
     * @throws Exception
     */
    public function __construct(string $cell)
    {
        parent::__construct();
        $this->cell = Cell::getContentClassName($cell, 'Tree', __METHOD__);
    }

    protected function runAction(): ActionResultInterface
    {
        $id     = $this->getLegacyId();
        $result = new CellActionResult('LCell');
        return $result->addCellCommand([$this->cell], 'findTreeItem', $id['usrInput']);
    }
}
