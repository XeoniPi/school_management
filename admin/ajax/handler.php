<?php
/**
 * KMA — admin/ajax/handler.php  |  PHP 7.2
 * Centralised AJAX endpoint for admin panel actions.
 * All requests must: be XHR + POST + carry valid CSRF token.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';

/* ── Guard: must be admin + XHR ── */
if (
    !isAdminLoggedIn() ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '') !== 'xmlhttprequest' ||
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
}

/* ── CSRF ── */
if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    jsonResponse(['success' => false, 'message' => 'CSRF validation failed'], 403);
}

$pdo    = getDB();
$action = sanitize(isset($_POST['action']) ? $_POST['action'] : '');

/* ─────────────────────────────────────────────────────────────────────── */
switch ($action) {

    /* ── Toggle notice pin ── */
    case 'toggle_notice_pin': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT is_pinned FROM notices WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { jsonResponse(['success'=>false,'message'=>'Not found'], 404); }
        $newVal = $row['is_pinned'] ? 0 : 1;
        $pdo->prepare('UPDATE notices SET is_pinned=? WHERE id=?')->execute([$newVal, $id]);
        jsonResponse(['success'=>true, 'pinned'=>(bool)$newVal]);
    }

    /* ── Toggle notice active ── */
    case 'toggle_notice_active': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT is_active FROM notices WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { jsonResponse(['success'=>false,'message'=>'Not found'], 404); }
        $newVal = $row['is_active'] ? 0 : 1;
        $pdo->prepare('UPDATE notices SET is_active=? WHERE id=?')->execute([$newVal, $id]);
        jsonResponse(['success'=>true, 'active'=>(bool)$newVal]);
    }

    /* ── Update admission status ── */
    case 'update_admission_status': {
        $id     = (int)(isset($_POST['id'])     ? $_POST['id']     : 0);
        $status = sanitize(isset($_POST['status']) ? $_POST['status'] : '');
        $allowed = ['pending','approved','rejected','enrolled'];
        if (!$id || !in_array($status, $allowed)) {
            jsonResponse(['success'=>false,'message'=>'Invalid parameters']);
        }
        $pdo->prepare('UPDATE admissions SET status=?, updated_at=NOW() WHERE id=?')->execute([$status, $id]);
        jsonResponse(['success'=>true, 'status'=>$status]);
    }

    /* ── Toggle gallery visibility ── */
    case 'toggle_gallery': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT is_active FROM gallery WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { jsonResponse(['success'=>false,'message'=>'Not found'], 404); }
        $newVal = $row['is_active'] ? 0 : 1;
        $pdo->prepare('UPDATE gallery SET is_active=? WHERE id=?')->execute([$newVal, $id]);
        jsonResponse(['success'=>true, 'active'=>(bool)$newVal]);
    }

    /* ── Update gallery sort order ── */
    case 'update_gallery_order': {
        $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
        if (!is_array($ids)) { jsonResponse(['success'=>false,'message'=>'Invalid data']); }
        $stmt = $pdo->prepare('UPDATE gallery SET sort_order=? WHERE id=?');
        foreach ($ids as $order => $gid) {
            $stmt->execute([(int)$order + 1, (int)$gid]);
        }
        jsonResponse(['success'=>true]);
    }

    /* ── Toggle download active ── */
    case 'toggle_download': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT is_active FROM downloads WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { jsonResponse(['success'=>false,'message'=>'Not found'], 404); }
        $newVal = $row['is_active'] ? 0 : 1;
        $pdo->prepare('UPDATE downloads SET is_active=? WHERE id=?')->execute([$newVal, $id]);
        jsonResponse(['success'=>true, 'active'=>(bool)$newVal]);
    }

    /* ── Get dashboard stats (for live refresh) ── */
    case 'get_stats': {
        $stats = [
            'notices'    => (int)$pdo->query('SELECT COUNT(*) FROM notices WHERE is_active=1')->fetchColumn(),
            'admissions' => (int)$pdo->query('SELECT COUNT(*) FROM admissions WHERE status="pending"')->fetchColumn(),
            'messages'   => (int)$pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read=0')->fetchColumn(),
            'gallery'    => (int)$pdo->query('SELECT COUNT(*) FROM gallery WHERE is_active=1')->fetchColumn(),
        ];
        jsonResponse(['success'=>true, 'stats'=>$stats]);
    }

    /* ── Mark contact message as read ── */
    case 'mark_message_read': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $pdo->prepare('UPDATE contact_messages SET is_read=1 WHERE id=?')->execute([$id]);
        jsonResponse(['success'=>true]);
    }

    /* ── Get contact message detail ── */
    case 'get_message': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id=?');
        $stmt->execute([$id]);
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$msg) { jsonResponse(['success'=>false,'message'=>'Not found'], 404); }
        /* Mark as read */
        $pdo->prepare('UPDATE contact_messages SET is_read=1 WHERE id=?')->execute([$id]);
        jsonResponse(['success'=>true, 'message'=>$msg]);
    }

    /* ── Delete gallery item ── */
    case 'delete_gallery': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT image_path FROM gallery WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && !empty($row['image_path'])) {
            $fp = UPLOAD_IMAGES . $row['image_path'];
            if (file_exists($fp)) { @unlink($fp); }
        }
        $pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([$id]);
        jsonResponse(['success'=>true]);
    }

    /* ── Delete download ── */
    case 'delete_download': {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!$id) { jsonResponse(['success'=>false,'message'=>'Invalid ID']); }
        $stmt = $pdo->prepare('SELECT file_path FROM downloads WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && !empty($row['file_path'])) {
            $fp = UPLOAD_PDFS . $row['file_path'];
            if (file_exists($fp)) { @unlink($fp); }
        }
        $pdo->prepare('DELETE FROM downloads WHERE id=?')->execute([$id]);
        jsonResponse(['success'=>true]);
    }

    /* ── Save class subject teacher name inline ── */
    case 'update_teacher': {
        $classId   = (int)(isset($_POST['class_id'])   ? $_POST['class_id']   : 0);
        $subjectId = (int)(isset($_POST['subject_id']) ? $_POST['subject_id'] : 0);
        $teacher   = sanitize(isset($_POST['teacher'])    ? $_POST['teacher']    : '');
        if (!$classId || !$subjectId) { jsonResponse(['success'=>false,'message'=>'Invalid IDs']); }
        $pdo->prepare('UPDATE class_subjects SET teacher_name=? WHERE class_id=? AND subject_id=?')
            ->execute([$teacher, $classId, $subjectId]);
        jsonResponse(['success'=>true]);
    }

    /* ── Search admissions ── */
    case 'search_admissions': {
        $q = sanitize(isset($_POST['q']) ? $_POST['q'] : '');
        if (mb_strlen($q) < 2) { jsonResponse(['success'=>true, 'results'=>[]]); }
        $like = '%' . $q . '%';
        $stmt = $pdo->prepare(
            'SELECT a.id, a.app_no, a.student_name_bn, a.student_name_en,
                    a.guardian_phone, a.status, c.class_name
             FROM admissions a
             LEFT JOIN classes c ON c.id = a.apply_class_id
             WHERE a.student_name_bn LIKE ? OR a.app_no LIKE ? OR a.guardian_phone LIKE ?
             ORDER BY a.created_at DESC LIMIT 10'
        );
        $stmt->execute([$like, $like, $like]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success'=>true, 'results'=>$results]);
    }

    default:
        jsonResponse(['success'=>false,'message'=>'Unknown action'], 400);
}