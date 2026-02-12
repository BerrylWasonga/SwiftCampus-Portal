<?php
require_once 'config.php';
$sql = file_get_contents('update_schema.sql');
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Schema updated successfully";
} else {
    echo "Error: " . $conn->error;
}
?>
