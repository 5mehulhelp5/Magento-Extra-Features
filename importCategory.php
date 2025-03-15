<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get(\Magento\Framework\App\State::class);
$state->setAreaCode('adminhtml');

$categoryFactory = $objectManager->get(\Magento\Catalog\Model\CategoryFactory::class);
$categoryRepository = $objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
$categoryCollection = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);

// Define the root category details
$rootCategoryId = 41;
$rootCategoryName = "Kalyan – US"; // Exact name as in Magento

$inputFile = BP . '/var/export/categories_export.csv';

if (!file_exists($inputFile)) {
    die("CSV file not found at $inputFile!\n");
}

$handle = fopen($inputFile, 'r');
fgetcsv($handle); // Skip header row

$categories = [];

while (($data = fgetcsv($handle)) !== false) {
    list($name, $urlKey, $parentPath) = $data;
    $categories[] = [
        'name' => trim($name),
        'url_key' => trim($urlKey),
        'parent_path' => trim($parentPath)
    ];
}

fclose($handle);

/**
 * Get category ID by path
 */
function getCategoryIdByPath($path) {
    global $categoryCollection, $rootCategoryId, $rootCategoryName;

    $pathParts = explode('/', trim($path));

    // If the first part of the path is the root category, ignore it
    if (!empty($pathParts) && trim($pathParts[0]) === $rootCategoryName) {
        array_shift($pathParts); // Remove root category from path
    }

    $parentId = $rootCategoryId; // Start from the root category ID

    foreach ($pathParts as $part) {
        $part = trim($part); // Remove spaces
        $collection = $categoryCollection->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('parent_id', $parentId)
            ->addAttributeToFilter('name', ['eq' => $part]) // Exact match
            ->getFirstItem();

        if ($collection->getId()) {
            $parentId = $collection->getId();
        } else {
            echo "Warning: Category '$part' not found under parent ID $parentId\n";
            return false;
        }
    }

    return $parentId;
}

/**
 * Create category
 */
function createCategory($name, $urlKey, $parentPath) {
    global $categoryFactory, $categoryRepository;

    $parentId = getCategoryIdByPath($parentPath);
    if (!$parentId) {
        echo "Error: Parent category '$parentPath' not found for '$name'. Skipping...\n";
        return;
    }

    $category = $categoryFactory->create();
    $category->setName($name);
    $category->setIsActive(true);
    $category->setParentId($parentId);
    $category->setUrlKey($urlKey);
    $category->setIncludeInMenu(true);

    try {
        $categoryRepository->save($category);
        echo "Category '$name' imported successfully under '$parentPath'.\n";
    } catch (\Exception $e) {
        echo "Error importing category '$name': " . $e->getMessage() . "\n";
    }
}

// Import categories
foreach ($categories as $cat) {
    createCategory($cat['name'], $cat['url_key'], $cat['parent_path']);
}

echo "Import completed.\n";
