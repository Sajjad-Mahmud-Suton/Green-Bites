<?php
session_set_cookie_params(['path' => '/']);
session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            getSlides();
        } elseif ($action === 'get' && isset($_GET['id'])) {
            getSlide($_GET['id']);
        }
        break;
    case 'POST':
        if ($action === 'add') {
            addSlide();
        } elseif ($action === 'update') {
            updateSlide();
        } elseif ($action === 'delete') {
            deleteSlide();
        } elseif ($action === 'toggle') {
            toggleSlide();
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function getSlides() {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM carousel_slides ORDER BY sort_order ASC, id ASC");
    $slides = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $slides[] = $row;
    }
    echo json_encode(['success' => true, 'slides' => $slides]);
}

function getSlide($id) {
    global $conn;
    $id = intval($id);
    $result = mysqli_query($conn, "SELECT * FROM carousel_slides WHERE id = $id");
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'slide' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Slide not found']);
    }
}

function addSlide() {
    global $conn;
    
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url'] ?? '');
    $btn_text = mysqli_real_escape_string($conn, $_POST['btn_text'] ?? 'Order Now');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 1;
    $menu_item_id = !empty($_POST['menu_item_id']) ? intval($_POST['menu_item_id']) : 'NULL';
    
    if (empty($title) || empty($image_url)) {
        echo json_encode(['success' => false, 'message' => 'Title and Image URL are required']);
        return;
    }
    
    $sql = "INSERT INTO carousel_slides (title, description, price, image_url, btn_text, sort_order, is_active, menu_item_id) 
            VALUES ('$title', '$description', $price, '$image_url', '$btn_text', $sort_order, $is_active, $menu_item_id)";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Slide added successfully', 'id' => mysqli_insert_id($conn)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add slide: ' . mysqli_error($conn)]);
    }
}

function updateSlide() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url'] ?? '');
    $btn_text = mysqli_real_escape_string($conn, $_POST['btn_text'] ?? 'Order Now');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
    $menu_item_id = !empty($_POST['menu_item_id']) ? intval($_POST['menu_item_id']) : 'NULL';
    
    if ($id <= 0 || empty($title) || empty($image_url)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }
    
    $sql = "UPDATE carousel_slides SET 
            title = '$title', 
            description = '$description', 
            price = $price, 
            image_url = '$image_url', 
            btn_text = '$btn_text', 
            sort_order = $sort_order, 
            is_active = $is_active,
            menu_item_id = $menu_item_id 
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Slide updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update slide: ' . mysqli_error($conn)]);
    }
}

function deleteSlide() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid slide ID']);
        return;
    }
    
    $sql = "DELETE FROM carousel_slides WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Slide deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete slide: ' . mysqli_error($conn)]);
    }
}

function toggleSlide() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid slide ID']);
        return;
    }
    
    $sql = "UPDATE carousel_slides SET is_active = NOT is_active WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Slide status toggled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to toggle slide']);
    }
}
?>
