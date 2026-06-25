<?php
// ====== ПОДКЛЮЧЕНИЕ К БАЗЕ ======
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'todo_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Ошибка БД: ' . $e->getMessage()]));
}

// ====== API ======
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

if ($method === 'GET' && $path === 'tasks') {
    $stmt = $pdo->query("SELECT id, title, description, due_date, due_time, priority, done FROM tasks ORDER BY id DESC");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tasks);
    exit;
}

if ($method === 'POST' && $path === 'tasks') {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $due_date = $data['due_date'] ?? '';
    $due_time = $data['due_time'] ?? '';
    $priority = $data['priority'] ?? 'Средний';

    if (!$title) {
        echo json_encode(['error' => 'Название обязательно']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, due_time, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $due_date, $due_time, $priority]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'PUT' && preg_match('/tasks\/(\d+)\/done/', $path, $matches)) {
    $id = $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);
    $done = $data['done'] ?? 1;

    $stmt = $pdo->prepare("UPDATE tasks SET done = ? WHERE id = ?");
    $stmt->execute([$done, $id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE' && preg_match('/tasks\/(\d+)/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Неизвестный запрос']);
?>