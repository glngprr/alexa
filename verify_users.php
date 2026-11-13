<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=alexa', 'root', '');
$stmt = $pdo->query("SELECT id, name, email, is_admin FROM users WHERE email IN ('admin@alexa.test','customer@alexa.test') ORDER BY id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID={$r['id']} name={$r['name']} email={$r['email']} is_admin={$r['is_admin']}\n";
}

