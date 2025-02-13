<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Internal\Tree;

/**
 * Class Item
 * @package byteShard\Internal\Tree
 */
class Item
{
    /** @var int */
    public int $level;
    /** @var Attributes */
    public Attributes $attributes;
    /** @var string|null */
    public ?string $parentId;

    /**
     * Item constructor.
     * @param int         $level
     * @param Attributes  $attributes
     * @param string|null $parentId
     */
    public function __construct(int $level, Attributes $attributes, ?string $parentId = null) {
        $this->level = $level;
        $this->attributes = $attributes;
        $this->parentId = $parentId;
    }
}
