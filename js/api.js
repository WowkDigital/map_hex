// API Service Layer (Pure fetch requests)

function checkAuth(response) {
    if (response.status === 401) {
        window.location.href = 'index.php?logout=1';
        throw new Error("Unauthorized");
    }
}

export async function fetchUsers() {
    const response = await fetch('api.php?action=users');
    checkAuth(response);
    if (!response.ok) {
        throw new Error("Failed to load users");
    }
    return await response.json();
}

export async function createUser(username) {
    const response = await fetch('api.php?action=users', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username })
    });
    checkAuth(response);
    if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        throw new Error(err.error || "Failed to create user");
    }
    return await response.json();
}

export async function fetchVisitedHexes(userId) {
    const response = await fetch(`api.php?user_id=${userId}`);
    checkAuth(response);
    if (!response.ok) {
        throw new Error('Failed to load visited hexes');
    }
    return await response.json();
}

export async function saveHex(userId, h3Index, res, level) {
    const response = await fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            h3_index: h3Index, 
            res: res,
            knowledge_level: level,
            user_id: userId
        })
    });
    checkAuth(response);
    if (!response.ok) {
        throw new Error('Save failed');
    }
    return await response.json();
}

export async function deleteHex(userId, h3Index) {
    const response = await fetch('api.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            h3_index: h3Index,
            user_id: userId
        })
    });
    checkAuth(response);
    if (!response.ok) {
        throw new Error('Delete failed');
    }
    return await response.json();
}
