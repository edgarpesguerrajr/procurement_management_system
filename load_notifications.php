<?php
include 'db_connect.php';
session_start();
header('Content-Type: application/json');
$data = [];
if (!isset($_SESSION['login_id'])) { echo json_encode($data); exit; }
$uid = intval($_SESSION['login_id']);
// Ensure notifications table exists (safe no-op if already created)
$conn->query("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    actor_id INT NOT NULL,
    project_id INT NOT NULL,
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Fetch latest 10 notifications for this user
$q = $conn->query("SELECT n.id,n.message,n.is_read,n.project_id,n.created_at,u.id as actor_id, concat(u.firstname,' ',u.lastname) as actor_name, p.particulars, p.pr_no FROM notifications n LEFT JOIN users u ON u.id = n.actor_id LEFT JOIN project_list p ON p.id = n.project_id WHERE n.user_id = {$uid} ORDER BY n.id DESC LIMIT 10");
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $r['date_created'] = date("M d, Y H:i", strtotime($r['created_at']));
        $r['is_read'] = intval($r['is_read']);
        $data[] = $r;
    }
}
echo json_encode($data);
exit;
