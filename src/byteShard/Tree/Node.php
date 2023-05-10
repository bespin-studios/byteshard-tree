<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Tree;

use byteShard\Enum;
use byteShard\Settings;

/**
 * Class Node
 * @package byteShard\Tree
 */
class Node
{
    private bool                $expand;
    private string              $field;
    private string              $idField;
    private ?string             $imageClosed;
    private ?string             $imageLeaf;
    private ?string             $imageOpen;
    private string              $sortByField;
    private Enum\Sort\Direction $sortDirection = Enum\Sort\Direction::ASC;
    private Enum\Sort\Type      $sortType      = Enum\Sort\Type::STRING;
    private bool                $visible       = true;

    /**
     * Node constructor.
     * @param string $field
     * @param bool $expandTo
     * @param string $allImages
     */
    public function __construct(string $field, bool $expandTo = false, string $allImages = '')
    {
        $this->field   = $field;
        $this->idField = $field.Settings::getIDSuffix();
        $this->expand  = $expandTo;
        if ($allImages !== '') {
            $this->imageClosed = $allImages;
            $this->imageOpen   = $allImages;
            $this->imageLeaf   = $allImages;
        }
    }

    /**
     * @param string $filename
     * @return $this
     * @API
     */
    public function setAllImages(string $filename): self
    {
        $this->imageClosed = $filename;
        $this->imageOpen   = $filename;
        $this->imageLeaf   = $filename;
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     * @API
     */
    public function setClosedBranchImage(string $filename): self
    {
        $this->imageClosed = $filename;
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     * @API
     */
    public function setIdField(string $field): self
    {
        $this->idField = $field;
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     * @API
     */
    public function setLeafImage(string $filename): self
    {
        $this->imageLeaf = $filename;
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     * @API
     */
    public function setOpenBranchImage(string $filename): self
    {
        $this->imageOpen = $filename;
        return $this;
    }

    /**
     * @param Enum\Sort\Direction $direction
     * @return $this
     * @API
     */
    public function setSortDirection(Enum\Sort\Direction $direction): self
    {
        $this->sortDirection = $direction;
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     * @API
     */
    public function setSortField(string $field): self
    {
        $this->sortByField = $field;
        return $this;
    }

    /**
     * @param Enum\Sort\Type $type
     * @return $this
     * @API
     */
    public function setSortType(Enum\Sort\Type $type): self
    {
        $this->sortType = $type;
        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     * @API
     */
    public function setVisible(bool $bool = true): self
    {
        $this->visible = $bool;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getIdField(): string
    {
        return $this->idField;
    }

    /**
     * @return string|null
     */
    public function getImageOpen(): ?string
    {
        return $this->imageOpen ?? null;
    }

    /**
     * @return string|null
     */
    public function getImageClosed(): ?string
    {
        return $this->imageClosed ?? null;
    }

    /**
     * @return string|null
     */
    public function getImageLeaf(): ?string
    {
        return $this->imageLeaf ?? null;
    }

    /**
     * @return int
     */
    public function getSortDirection(): int
    {
        return $this->sortDirection->value;
    }

    /**
     * @return string
     */
    public function getSortField(): string
    {
        return $this->sortByField ?? $this->field;
    }

    /**
     * @return int
     */
    public function getSortType(): int
    {
        return $this->sortType->value;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return bool
     */
    public function isExpand(): bool
    {
        return $this->expand;
    }
}
