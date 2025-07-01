<?php
include_once 'config/connect_db.php';
include_once 'models/Chat.php';

class ChatController {
    private $model;

    public function __construct($db) {
        $this->model = new Chat($db);
    }

    // Create message
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_pengirim = $_POST['id_pengirim'];
            $id_penerima = $_POST['id_penerima'];
            $pesan = $_POST['pesan'];
            $tanggal = $_POST['tanggal'];
            $this->model->sendMessage($id_pengirim, $id_penerima, $pesan, $tanggal);
            echo "Message sent successfully!";
        }
    }

    // Get All Messages
    public function index() {
        $chatList = $this->model->getAll();
        include 'views/chat/index.php'; // Show all chat messages
    }

    // Get message by ID
    public function show($id) {
        $chat = $this->model->getById($id);
        include 'views/chat/show.php'; // Show single message
    }

    // Delete message
    public function delete($id) {
        $this->model->delete($id);
        echo "Message deleted successfully!";
    }
}
?>