<?php
// Simple API to manage featured courses (file-based)
header('Content-Type: application/json');
session_start();
// Enforce admin-only access for mutating operations
// Allow GET for public viewing; require admin for POST (create/delete)
function json_error($msg, $code = 400){ http_response_code($code); echo json_encode(['success'=>false,'error'=>$msg]); exit; }

$dataFile = __DIR__ . '/../data/featured_courses.json';
$imageDir = __DIR__ . '/../assets/images/featured';

if(!file_exists(dirname($dataFile))) mkdir(dirname($dataFile), 0777, true);
if(!file_exists($imageDir)) mkdir($imageDir, 0777, true);

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(!file_exists($dataFile)) file_put_contents($dataFile, json_encode([]));
    $json = file_get_contents($dataFile);
    $arr = json_decode($json, true) ?: [];
    echo json_encode(['success'=>true,'data'=>$arr]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Require admin for POST actions
    if(empty($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')){
        json_error('unauthorized', 403);
    }

    // Support delete action via POST with action=delete and id
    if(isset($_POST['action']) && $_POST['action'] === 'delete'){
        $delId = $_POST['id'] ?? null;
        if(!$delId){ http_response_code(400); echo json_encode(['error'=>'missing_id']); exit; }

        $items = [];
        if(file_exists($dataFile)) $items = json_decode(file_get_contents($dataFile), true) ?: [];
        $found = false;
        $newItems = [];
        foreach($items as $it){
            if((string)$it['id'] === (string)$delId){
                $found = true;
                // remove image file if exists
                if(!empty($it['image'])){
                    $imgPath = __DIR__ . '/../' . $it['image'];
                    if(file_exists($imgPath)) @unlink($imgPath);
                }
                continue; // skip this item
            }
            $newItems[] = $it;
        }
        if($found){
            file_put_contents($dataFile, json_encode($newItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success'=>true,'data'=>$newItems]);
        } else {
            http_response_code(404); echo json_encode(['error'=>'not_found']);
        }
        exit;
    }
    // Accept multipart/form-data
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $hours = trim($_POST['hours'] ?? '');
    $class_size = trim($_POST['class_size'] ?? '');
    $sessions = trim($_POST['sessions'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $badge = trim($_POST['badge'] ?? '');

    $imageUrl = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
        $tmp = $_FILES['image']['tmp_name'];
        if(!is_uploaded_file($tmp)) json_error('invalid_upload');

        // basic size and type checks
        $maxBytes = 3 * 1024 * 1024; // 3MB
        if($_FILES['image']['size'] > $maxBytes) json_error('image_too_large');

        $orig = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if(!in_array($ext, $allowed)) json_error('invalid_image_type');

        // verify image actually is an image
        $check = @getimagesize($tmp);
        if($check === false) json_error('file_not_image');

        $safe = preg_replace('/[^a-zA-Z0-9_-]/','_',pathinfo($orig, PATHINFO_FILENAME));
        $name = $safe . '-' . time() . '.' . $ext;
        $destDir = realpath(__DIR__ . '/../assets/images/featured');
        if($destDir === false){ if(!mkdir(__DIR__ . '/../assets/images/featured', 0777, true)) json_error('mkdir_failed'); $destDir = realpath(__DIR__ . '/../assets/images/featured'); }
        $dest = $destDir . DIRECTORY_SEPARATOR . $name;
        if(!@move_uploaded_file($tmp, $dest)) json_error('move_failed');

        // build URL relative path used by app
        $imageUrl = 'assets/images/featured/' . $name;
    }

    // load existing
    $items = [];
    if(file_exists($dataFile)) $items = json_decode(file_get_contents($dataFile), true) ?: [];

    $id = time() . rand(100,999);
    $new = [
        'id' => $id,
        'title' => $title,
        'description' => $desc,
        'hours' => $hours,
        'class_size' => $class_size,
        'sessions' => $sessions,
        'level' => $level,
        'badge' => $badge,
        'image' => $imageUrl,
        'created_at' => date('c')
    ];

    array_unshift($items, $new); // newest first
    file_put_contents($dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode(['success'=>true,'item'=>$new,'data'=>$items]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'method_not_allowed']);

?>
