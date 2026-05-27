<?php
$conn = new mysqli('localhost', 'root', '', 'student_violation_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Checking guards table...</h2>";
$result = $conn->query("DESCRIBE guards");
$hasGuardRank = false;
$hasSchedule = false;
while($row = $result->fetch_assoc()) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    if ($row['Field'] == 'guard_rank') $hasGuardRank = true;
    if ($row['Field'] == 'schedule') $hasSchedule = true;
}

if (!$hasGuardRank || !$hasSchedule) {
    echo "<h2>Applying patch to guards table...</h2>";
    $patchSql = "ALTER TABLE guards 
                 ADD COLUMN IF NOT EXISTS guard_rank VARCHAR(50) DEFAULT 'I',
                 ADD COLUMN IF NOT EXISTS schedule VARCHAR(100) DEFAULT 'Full Time'";
    if ($conn->query($patchSql)) {
        echo "<p style='color: green; font-weight: bold;'>Successfully patched guards table!</p>";
    } else {
        echo "<p style='color: red;'>Error patching guards table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green; font-weight: bold;'>guards table is already up to date!</p>";
}

$conn->close();
?>
