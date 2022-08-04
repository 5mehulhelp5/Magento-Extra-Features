<?php

namespace Task\Employee\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface EmployeeSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get Interface
     *
     * @return \Task\Employee\Api\Data\EmployeeInterface[]
     */
    public function getItems();

    /**
     * Set Items
     *
     * @param  \Task\Employee\Api\Data\EmployeeInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
