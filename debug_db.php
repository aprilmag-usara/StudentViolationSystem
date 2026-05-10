<?php
$conn = new mysqli('localhost', 'root', '', 'StudentViolationSystem');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("DESCRIBE violations");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
$conn->close();
?>
