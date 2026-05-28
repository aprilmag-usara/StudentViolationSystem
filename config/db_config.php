<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

define('DB_HOST', 'localhost');
define('DB_USER', 'SVS');
define('DB_PASS', '151818_StudentViolationSystem');
define('DB_NAME', 'u151818_SVS');

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo "<!DOCTYPE html><html><head><title>MySQL Required</title>";
    echo "<style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .error-box {
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(231,76,60,0.4);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
        }
        h1 { color: #e74c3c; margin-bottom: 20px; }
        .step { margin: 15px 0; text-align: left; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 10px; }
        .step strong { color: #5dade2; }
    </style></head>";
    echo "<body><div class='error-box'>";
    echo "<h1>⚠️ MySQL Not Running</h1>";
    echo "<p style='font-size: 1.1rem; margin-bottom: 30px;'>Please start MySQL in XAMPP Control Panel first.</p>";
    echo "<div class='step'><strong>Step 1:</strong> Open XAMPP Control Panel</div>";
    echo "<div class='step'><strong>Step 2:</strong> Click the <strong>Start</strong> button next to <strong>MySQL</strong></div>";
    echo "<div class='step'><strong>Step 3:</strong> Wait for it to turn <strong>green</strong> (running)</div>";
    echo "<div class='step'><strong>Step 4:</strong> Refresh this page</div>";
    echo "</div></body></html>";
    exit();
}
?>
