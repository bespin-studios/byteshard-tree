<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Action\Tree;

use byteShard\Internal\Action\ActionResultInterface;
use byteShard\Internal\Action\CellActionResult;

/**
 * Class CloseAllItems
 * @package byteShard\Action\Tree
 */
class CloseAllItems extends ModifyTree
{
    protected function runAction(): ActionResultInterface
    {
        $result = new CellActionResult('LCell');
        return $result->addCellCommand($this->cells, 'closeAllTreeItems', true);
    }
}