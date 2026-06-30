<?php
// database connection and initialization/migration

function initDatabase($dbPath = 'database.db') {
    // Connect to SQLite database
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set busy timeout to 5 seconds to prevent locking errors under concurrency
    $db->exec("PRAGMA busy_timeout = 5000;");

    // --- MIGRATION & SCHEMA INITIALIZATION LOGIC ---

    // 1. Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Ensure Default User exists if table was just created or empty
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    $stmt->closeCursor();
    $stmt = null;

    if ($userCount == 0) {
        $db->exec("INSERT INTO users (username) VALUES ('Default User')");
    }

    // 2. Check if visited_hexes needs migration (check if user_id column exists)
    $needsMigration = false;
    $columns = [];
    try {
        $result = $db->query("PRAGMA table_info(visited_hexes)");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
        $result->closeCursor();
        $result = null;

        if (!empty($columns) && !in_array('user_id', $columns)) {
            $needsMigration = true;
        }
    } catch (Exception $e) {
        // Table might not exist yet, which is fine, we will create it normally below
    }

    if ($needsMigration) {
        $db->beginTransaction();
        try {
            // Get ID of default user
            $userStmt = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
            $defaultUserId = $userStmt->fetchColumn();
            $userStmt->closeCursor();
            $userStmt = null;

            // Rename old table
            $db->exec("ALTER TABLE visited_hexes RENAME TO visited_hexes_old");

            // Create new table with composite PK
            $db->exec("CREATE TABLE visited_hexes (
                h3_index TEXT NOT NULL,
                user_id INTEGER NOT NULL,
                res INTEGER NOT NULL,
                knowledge_level INTEGER DEFAULT 2,
                added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (h3_index, user_id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )");

            // Dynamic column selection for migration
            $klField = in_array('knowledge_level', $columns) ? "knowledge_level" : "2"; // Default to 2 if missing
            $aaField = in_array('added_at', $columns) ? "added_at" : "CURRENT_TIMESTAMP"; // Default to now if missing

            // Copy data
            $db->exec("INSERT INTO visited_hexes (h3_index, user_id, res, knowledge_level, added_at)
                       SELECT h3_index, $defaultUserId, res, $klField, $aaField FROM visited_hexes_old");

            // Drop old table
            $db->exec("DROP TABLE visited_hexes_old");
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("Migration failed: " . $e->getMessage());
        }
    } else {
        // Standard create if not exists
        $db->exec("CREATE TABLE IF NOT EXISTS visited_hexes (
            h3_index TEXT NOT NULL,
            user_id INTEGER NOT NULL,
            res INTEGER NOT NULL,
            knowledge_level INTEGER DEFAULT 2,
            added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (h3_index, user_id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
    }

    return $db;
}
