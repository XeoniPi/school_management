<?php
/**
 * KMA — ajax/get_notice.php
 * Returns a single notice as JSON for the modal.
 * PHP 7.2 compatible. AJAX only.
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    jsonResponse(['error' => 'Forbidden'], 403);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    jsonResponse(['error' => 'Invalid ID'], 400);
}

$pdo  = getDB();
$stmt = $pdo->prepare(
    'SELECT id, title, content, category, notice_date, file_path
     FROM notices WHERE id = ? AND is_active = 1 LIMIT 1'
);
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    jsonResponse(['error' => 'Not found'], 404);
}

/* Increment view counter */
$pdo->prepare('UPDATE notices SET views = views + 1 WHERE id = ?')->execute([$id]);

jsonResponse([
    'success' => true,
    'notice'  => [
        'id'             => $row['id'],
        'title'          => $row['title'],
        'content'        => $row['content'],
        'category'       => $row['category'],
        'category_label' => noticeCategoryLabel($row['category']),
        'category_css'   => noticeCategoryClass($row['category']),
        'notice_date'    => $row['notice_date'],
        'file_path'      => $row['file_path'],
    ],
]);