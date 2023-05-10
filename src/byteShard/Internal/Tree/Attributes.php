<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Internal\Tree;

/**
 * Class Attributes
 * @package byteShard\Internal\Tree
 */
class Attributes
{
    public ?int $select = null;

    public function __construct(
        public string           $id,
        public readonly string  $text,
        public ?bool            $open = null,
        public readonly ?string $im0 = null,
        public readonly ?string $im1 = null,
        public readonly ?string $im2 = null,
        public readonly ?string $style = null)
    {
    }
}
