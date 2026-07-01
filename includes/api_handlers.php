<?php
// API Request Handlers

function handleUsers($db, $method) {
    if ($method === 'GET') {
        $stmt = $db->query("SELECT id, username FROM users ORDER BY username");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        return;
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['username'])) {
            sendError('Missing username');
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO users (username) VALUES (:username)");
            $stmt->execute([':username' => $input['username']]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                sendError('Username already exists');
            }
            throw $e;
        }
        return;
    }
    
    sendError('Method not allowed for users', 405);
}

function handleHexes($db, $method) {
    if ($method === 'GET') {
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        if (!$userId) {
            sendError('Missing user_id parameter');
        }

        // Fetch hexes for specific user
        $stmt = $db->prepare("SELECT h3_index, res, knowledge_level, added_at FROM visited_hexes WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        return;
    } 
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Basic validation
        if (!isset($input['h3_index']) || !isset($input['res']) || !isset($input['user_id'])) {
            sendError('Missing h3_index, res, or user_id');
        }

        $h3Index = $input['h3_index'];
        $res = (int)$input['res'];
        $userId = (int)$input['user_id'];
        $level = isset($input['knowledge_level']) ? (int)$input['knowledge_level'] : 2;
        $addedAt = isset($input['added_at']) ? $input['added_at'] : null;

        if ($addedAt) {
            $stmt = $db->prepare("INSERT OR REPLACE INTO visited_hexes (h3_index, user_id, res, knowledge_level, added_at) VALUES (:h3_index, :user_id, :res, :level, :added_at)");
            $stmt->execute([
                ':h3_index' => $h3Index,
                ':user_id' => $userId,
                ':res' => $res,
                ':level' => $level,
                ':added_at' => $addedAt
            ]);
        } else {
            $stmt = $db->prepare("INSERT OR REPLACE INTO visited_hexes (h3_index, user_id, res, knowledge_level) VALUES (:h3_index, :user_id, :res, :level)");
            $stmt->execute([
                ':h3_index' => $h3Index,
                ':user_id' => $userId,
                ':res' => $res,
                ':level' => $level
            ]);
        }

        // Get added_at
        $stmt = $db->prepare("SELECT added_at FROM visited_hexes WHERE h3_index = :h3_index AND user_id = :user_id");
        $stmt->execute([':h3_index' => $h3Index, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true, 
            'h3_index' => $h3Index, 
            'knowledge_level' => $level,
            'added_at' => $row['added_at']
        ]);
        return;
    } 
    
    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['h3_index']) || !isset($input['user_id'])) {
            sendError('Missing h3_index or user_id');
        }

        $stmt = $db->prepare("DELETE FROM visited_hexes WHERE h3_index = :h3_index AND user_id = :user_id");
        $stmt->execute([
            ':h3_index' => $input['h3_index'],
            ':user_id' => $input['user_id']
        ]);

        echo json_encode(['success' => true, 'removed' => $input['h3_index']]);
        return;
    }
    
    sendError('Method not allowed for hexes', 405);
}
