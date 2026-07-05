<?php
// Refactored search.php - Secure Patient Search
require_once 'db_config.php'; // Utilizing a pre-configured $pdo (PHP Data Objects) instance

// Extract user input
$keyword = $_GET['keyword'] ?? '';

if ($keyword !== '') {
    // Structural Memory Isolation: Parameterized PDO Query separates Data from Command
    $sql = "SELECT id, name, illness_history FROM patient_records WHERE name LIKE :keyword";
    $stmt = $pdo->prepare($sql);
    
    // Binding the wildcard parameters safely within the data plane
    $searchTerm = "%" . $keyword . "%";
    $stmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        foreach ($results as $row) {
            // Context-Aware Encoding: Converting control characters to inert HTML entities
            $safeKeyword = htmlspecialchars($keyword, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $safeName    = htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $safeHistory = htmlspecialchars($row['illness_history'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            echo "<div>Result found for keyword: " . $safeKeyword . "<br>";
            echo "Patient: " . $safeName . " | History: " . $safeHistory . "</div><hr>";
        }
    } else {
        // Output encoding applied to the fallback error trace
        $safeKeyword = htmlspecialchars($keyword, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo "No records found for: " . $safeKeyword;
    }
}

