<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard;

use byteShard\Enum\ContentType;
use byteShard\ID\RowID;
use byteShard\Internal\CellContent;
use byteShard\Internal\SimpleXML;
use byteShard\Internal\Struct\ClientCell;
use byteShard\Internal\Struct\ClientCellEvent;
use byteShard\Internal\Struct\ClientCellProperties;
use byteShard\Internal\Struct\ContentComponent;
use byteShard\Internal\Tree\Attributes;
use byteShard\Internal\Tree\Item;
use byteShard\Tree\Node;
use byteShard\Tree\TreeInterface;
use byteShard\Utils\Strings;
use DateTime;
use SimpleXMLElement;

/**
 * Class Tree
 * @package byteShard
 */
abstract class Tree extends CellContent implements TreeInterface
{
    /** @var Node[] */
    private array    $nodes           = [];
    protected string $cellContentType = 'DHTMLXTree';
    private array    $dataArray       = [];
    private array    $outputArray     = [];
    private string   $query           = '';
    private bool     $sortDataArray   = true;
    private array    $images          = [];

    // Parameters
    private bool $saveOpenState   = true;
    private bool $smartRendering  = false;
    private bool $smartXMLParsing = true;

    /**
     * TODO: early session close
     * @throws Exception
     * @internal
     */
    public function getCellContent(bool $resetNonce = true): ?ClientCell
    {
        parent::getCellContent($resetNonce);
        $this->defineCellContent();
        if ($this->hasFallbackContent()) {
            return $this->getFallbackContent()->getCellContent(false);
        }
        $components = parent::getComponents($resetNonce);
        $nonce      = $this->cell->getNonce();
        $this->defineDataBinding();
        $this->queryData();
        $this->sortArray();
        $this->buildTree($nonce);
        $this->selectLastSelectedRow();
        $components[] = new ContentComponent(
            type   : ContentType::DhtmlxTree,
            content: $this->getXML(),
            events : $this->getCellEvents(),
            setup  : $this->getSetupParameters($nonce),
            update : $this->getUpdateParameters()
        );
        return new ClientCell(
            new ClientCellProperties(
                nonce: $nonce,
            ),
            ...$components
        );
    }

    /**
     * @param array $array
     * @return $this
     */
    protected function setData(array $array): self
    {
        $this->dataArray = $array;
        return $this;
    }

    /**
     * @API
     * @param Node ...$nodes
     * @return $this
     */
    protected function setNodes(Tree\Node ...$nodes): self
    {
        foreach ($nodes as $node) {
            $this->nodes[] = $node;
        }
        return $this;
    }

    /**
     * @API
     * @param string $query
     * @return $this
     */
    protected function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @API
     * @param string $leaf
     * @param string $closed
     * @param string $open
     * @return $this
     */
    protected function setTreeImages(string $leaf, string $closed, string $open): self
    {
        $this->images = [
            $leaf,
            $closed,
            $open
        ];
        return $this;
    }

    private function buildTree(string $nonce): void
    {
        // create objects for each node with publicly accessible properties. Otherwise, we would need to call getters in every single iteration
        $nodes = [];
        foreach ($this->nodes as $node) {
            $nodes[] = new class($node->getField(), $node->getIdField(), $node->isVisible(), $node->getImageLeaf(), $node->getImageOpen(), $node->getImageClosed(), $node->isExpand()) {
                public string  $field;
                public string  $idField;
                public bool    $visible;
                public ?string $im0;
                public ?string $im1;
                public ?string $im2;
                public bool    $expand;

                public function __construct(string $field, string $idField, bool $visible, ?string $im0, ?string $im1, ?string $im2, bool $expand)
                {
                    $this->field   = $field;
                    $this->idField = $idField;
                    $this->visible = $visible;
                    $this->im0     = $im0;
                    $this->im1     = $im1;
                    $this->im2     = $im2;
                    $this->expand  = $expand;
                }
            };
        }

        if (!empty($nodes)) {
            $expand = false;
            for ($i = count($nodes) - 1; $i >= 0; $i--) {
                if ($expand === true) {
                    $nodes[$i]->expand = true;
                } elseif ($nodes[$i]->expand === true) {
                    $expand = true;
                }
            }
            foreach ($this->dataArray as $object) {
                $level        = 1;
                $rowId        = [];
                $encodedRowId = null;
                foreach ($nodes as $node) {
                    if (isset($object->{$node->idField}) && (is_numeric($object->{$node->idField}) || (is_string($object->{$node->idField}) && $object->{$node->idField} !== ''))) {
                        if ($node->visible === true) {
                            $parentRowId           = $encodedRowId;
                            $rowId[$node->idField] = $object->{$node->idField};
                            $rowIdObject           = new RowID($rowId);
                            $encodedRowId          = $rowIdObject->getEncodedRowId();
                            if ($level === 1) {
                                if (isset($this->outputArray[$encodedRowId])) {
                                    $level++;
                                    continue;
                                }
                            }
                            $item                             = new Item(
                                $level,
                                new Attributes(
                                    $rowIdObject->getEncryptedRowId($nonce),
                                    Strings::purify($object->{$node->field}),
                                    $node->expand,
                                    (isset($object->treeImageLeaf) && !empty($object->treeImageLeaf) && is_string($object->treeImageLeaf) ? $object->treeImageLeaf : $node->im0),
                                    (isset($object->treeImageOpen) && !empty($object->treeImageOpen) && is_string($object->treeImageOpen) ? $object->treeImageOpen : $node->im1),
                                    (isset($object->treeImageClose) && !empty($object->treeImageClose) && is_string($object->treeImageClose) ? $object->treeImageClose : $node->im2),
                                    (isset($object->Style) && !empty($object->Style) && is_string($object->Style) ? $object->Style : null)
                                ),
                                $parentRowId
                            );
                            $this->outputArray[$encodedRowId] = $item;
                            $level++;
                        } else {
                            if (empty($rowId)) {
                                $rowId[$node->idField] = $object->{$node->idField};
                            } else {
                                $oldRowIdObject                                   = new RowID($rowId);
                                $oldRowId                                         = $oldRowIdObject->getEncodedRowId();
                                $rowId[$node->idField]                            = $object->{$node->idField};
                                $rowIdObject                                      = new RowID($rowId);
                                $encodedRowId                                     = $rowIdObject->getEncodedRowId();
                                $this->outputArray[$encodedRowId]                 = $this->outputArray[$oldRowId];
                                $this->outputArray[$encodedRowId]->attributes->id = $rowIdObject->getEncryptedRowId($nonce);
                                unset($this->outputArray[$oldRowId]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getCellEvents(): array
    {
        //TODO: check if registerContentEvent can be called in CellContent::getParentEventsForClient
        // make sure that all Cell types (Grid, Form etc) support this
        // if yes, rename CellContent::getParentEventsForClient to CellContent::getCellEvents and remove this function where possible
        foreach ($this->getEvents() as $event) {
            $this->cell->registerContentEvent($event);
        }
        $result = [];
        foreach ($this->getParentEventsForClient() as $eventName => $events) {
            foreach ($events as $handler) {
                $result[] = new ClientCellEvent($eventName, $handler);
            }
        }
        return $result;
    }

    /**
     * @param string $nonce
     * @return array
     */
    private function getSetupParameters(string $nonce): array
    {
        $parameters = [];
        if ($this->smartRendering === true) {
            $parameters['beforeDataLoading']['enableSmartRendering'] = true;
        }
        if ($this->smartXMLParsing === true) {
            $parameters['beforeDataLoading']['enableSmartXMLParsing'] = true;
        }
        if (!empty($this->images)) {
            $parameters['beforeDataLoading']['setStdImages'] = $this->images;
        }
        $parameters['cn'] = base64_encode($nonce);
        return $parameters;
    }

    private function getUpdateParameters(): array
    {
        $parameters = [];
        if ($this->saveOpenState === true) {
            $cookieName                          = get_class($this);
            $cookieExpirationDate                = 'expires='.(new DateTime('now'))->modify('+10 years')->format('D, d M Y').' 23:00:00 GMT';
            $parameters['enableOpenStateSaving'] = [$cookieName, $cookieExpirationDate.'; SameSite=Lax'];
        }
        return $parameters;
    }

    /**
     * @return string
     */
    private function getXML(): string
    {
        SimpleXML::initializeDecode();
        try {
            $result = new SimpleXMLElement('<?xml version="1.0" encoding="'.$this->getOutputCharset().'" ?><tree id="0"></tree>');
        } catch (\Exception $e) {
            Debug::error($e->getMessage().' (14910001)');
            return '';
        }
        $lastRow = [];
        foreach ($this->outputArray as $item) {
            if ($item->level === 1) {
                $lastRow[$item->level] = $result->addChild('item');
            } else {
                $lastRow[$item->level] = $lastRow[$item->level - 1]->addChild('item');
            }
            foreach ($item->attributes as $name => $value) {
                if ($value !== null) {
                    SimpleXML::addAttribute($lastRow[$item->level], $name, $value);
                }
            }
        }
        return SimpleXML::asString($result);
    }

    /**
     * @throws Exception
     */
    private function queryData(): void
    {
        if (!empty($this->nodes) && empty($this->dataArray) && $this->query !== '') {
            $this->dataArray = Database::getArray($this->query);
        }
    }

    private function selectLastSelectedRow(): void
    {
        $selectedIdArray       = $this->selectedID?->getIds() ?? [];
        $rowId                 = new RowID($selectedIdArray);
        $selectedTreeElementId = $rowId->getEncodedRowId();
        if (array_key_exists($selectedTreeElementId, $this->outputArray)) {
            $this->outputArray[$selectedTreeElementId]->attributes->select = true;
            $parentId                                                      = $this->outputArray[$selectedTreeElementId]->parentId;
            while (!empty($parentId)) {
                if (array_key_exists($parentId, $this->outputArray)) {
                    $this->outputArray[$parentId]->attributes->open = true;
                    $parentId                                       = $this->outputArray[$parentId]->parentId;
                } else {
                    $parentId = null;
                }
            }
        }
    }

    /**
     * sort the dataArray so the tree can be built correctly
     * TODO: Evaluate better sorting? (measure performance and configurability)
     * usort($array, function($a, $b) {return strcmp($a->text, $b->text);});
     */
    private function sortArray(): void
    {
        if (!empty($this->nodes) && !empty($this->dataArray) && $this->sortDataArray) {
            $nodes  = [];
            $sortBy = [];
            $sort   = [];
            foreach ($this->nodes as $node) {
                $nodes[]  = [
                    'd' => $node->getSortDirection(),
                    't' => $node->getSortType()
                ];
                $sortBy[] = $node->getSortField();
            }
            // generate arrays for array_multisort
            foreach ($this->dataArray as $key => $row) {
                foreach ($sortBy as $nodeIdx => $node) {
                    if (isset($row->{$node}) || property_exists($row, $node)) {
                        $sort[$nodeIdx][$key] = strtolower($row->{$node} ?? '');
                    }
                }
            }
            // generate arguments for array_multisort
            $args = [];
            foreach ($nodes as $nodeIdx => $node) {
                $args[] = &$sort[$nodeIdx];
                $args[] = $node['d'];
                $args[] = $node['t'];
            }
            $args[] = &$this->dataArray;
            array_multisort(...$args);
        }
    }

    /**
     * @param bool $sortDataArray
     * @return $this
     * @API
     */
    public function setDataSorting(bool $sortDataArray = true): Tree
    {
        $this->sortDataArray = $sortDataArray;
        return $this;
    }

    // *** DEPRECATED FUNCTIONS ***

    /**
     * @param array $array
     * @return $this
     * @noinspection PhpUnusedParameterInspection
     * @deprecated
     * @API
     */
    protected function setDataArray(array $array): self
    {
        trigger_error('Method Tree::setDataArray is deprecated.', E_USER_DEPRECATED);
        return $this;
    }

    /**
     * @API
     * @param bool $bool
     * @return $this
     * @noinspection PhpUnusedParameterInspection
     * @deprecated
     */
    protected function setShowOrphans(bool $bool = true): self
    {
        trigger_error('Method Tree::setShowOrphans is deprecated.', E_USER_DEPRECATED);
        return $this;
    }
}
