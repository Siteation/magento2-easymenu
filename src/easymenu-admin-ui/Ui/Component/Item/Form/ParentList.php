<?php

declare(strict_types=1);

namespace AMF\EasyMenuAdminUi\Ui\Component\Item\Form;

use AMF\EasyMenuAdminUi\Model\Locator\LocatorInterface;
use AMF\EasyMenuApi\Api\Data\ItemInterface;
use AMF\EasyMenuApi\Model\GetAllItemsInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options for PatenList
 */
class ParentList implements OptionSourceInterface
{
    /**
     * @var GetAllItemsInterface
     */
    private $getAllItems;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var array
     */
    private $menuItems = [];

    /**
     * ParentList constructor.
     *
     * @param LocatorInterface $locator
     * @param GetAllItemsInterface $getAllItems
     */
    public function __construct(
        LocatorInterface $locator,
        GetAllItemsInterface $getAllItems
    ) {
        $this->locator = $locator;
        $this->getAllItems = $getAllItems;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getMenuItems();
    }

    /**
     * Get Menu Edit
     *
     * @return array
     */
    private function getMenuItems(): array
    {
        $this->buildParentTree();

        if (count($this->menuItems) === 1) {
            return [];
        }

        return [0 => $this->menuItems[0]];
    }

    /**
     * Build Parent Item Edit Menu
     *
     * @return void
     */
    private function buildParentTree(): void
    {
        $storeId = (int) $this->locator->getStore()->getId();
        $searchResult = $this->getAllItems->execute($storeId, false);

        $this->menuItems = $this->getTreeRootNode();

        foreach ($searchResult->getItems() as $item) {
            $this->addItemData($item);
        }
    }

    /**
     * Add Item Data
     *
     * @param ItemInterface $item
     */
    private function addItemData(ItemInterface $item): void
    {
        $this->menuItems[$item->getId()] = ['value' => (int) $item->getId()];
        $this->menuItems[$item->getId()]['is_active'] = 1;
        $this->menuItems[$item->getId()]['label'] = $item->getName();

        $this->menuItems[$item->getParentId()]['value'] = (int) $item->getParentId();
        $this->menuItems[$item->getParentId()]['optgroup'][] = &$this->menuItems[$item->getId()];
    }

    /**
     * Get Edit Node Data
     *
     * @return array
     */
    private function getTreeRootNode(): array
    {
        return [
            0 => [
                'value' => 0,
                'label' => __('-- None --'),
                'is_active' => 1,
                'optgroup' => [],
            ],
        ];
    }
}
