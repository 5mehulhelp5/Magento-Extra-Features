<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();

// Replace with actual "Kalyan – US" category ID
$categoryId = 5249;

// Fetch all categories under "Kalyan – US"
$query = "
    SELECT cce.entity_id, cce.row_id, ccev.value AS name, ccev2.value AS url_key, cce.path
    FROM catalog_category_entity AS cce
    LEFT JOIN catalog_category_entity_varchar AS ccev
        ON cce.row_id = ccev.row_id
        AND ccev.attribute_id = (SELECT attribute_id FROM eav_attribute WHERE entity_type_id = 3 AND attribute_code = 'name')
    LEFT JOIN catalog_category_entity_varchar AS ccev2
        ON cce.row_id = ccev2.row_id
        AND ccev2.attribute_id = (SELECT attribute_id FROM eav_attribute WHERE entity_type_id = 3 AND attribute_code = 'url_key')
    WHERE cce.path LIKE '1/{$categoryId}/%'
    ORDER BY cce.path ASC";

$rows = $connection->fetchAll($query);

if (empty($rows)) {
    die("No categories found under Kalyan – US. Check category ID.");
}

// File path in var/export
$exportDir = BP . "/var/export";
$filePath = $exportDir . "/categories_export1.csv";

// Ensure var/export exists and set permissions
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0777, true);
}
chmod($exportDir, 0777);

$csvFile = fopen($filePath, "w");

if (!$csvFile) {
    die("Error: Unable to create file. Check permissions.");
}

// Set file permissions
chmod($filePath, 0777);

// CSV Header
fputcsv($csvFile, ["Category Name", "URL Key", "Parent Path"]);

// Store category names for quick lookup
$categoryNames = [];
foreach ($rows as $row) {
    $categoryNames[$row['entity_id']] = $row['name'];
}

// Function to build full parent path
function getFullParentPath($categoryPath, $categoryNames)
{
    $pathIds = explode("/", $categoryPath);
    array_shift($pathIds); // Remove root (1)

    if (count($pathIds) <= 1) {
        return "Kalyan – US"; // Root category itself
    }

    array_pop($pathIds); // Remove current category ID

    $parentNames = [];
    foreach ($pathIds as $pathId) {
        if (isset($categoryNames[$pathId])) {
            $parentNames[] = $categoryNames[$pathId];
        }
    }

    return !empty($parentNames) ? implode("/", $parentNames) : "Kalyan – US";
}

// Process each row
foreach ($rows as $row) {
    if (empty($row['name']) || empty($row['url_key'])) {
        continue; // Skip invalid rows
    }

    // Build full hierarchical parent path
    $parentPath = getFullParentPath($row['path'], $categoryNames);

    fputcsv($csvFile, [$row['name'], $row['url_key'], $parentPath]);
}

fclose($csvFile);

echo "Exported Successfully! File path: $filePath";
?>
