<?php
class RoomController extends Controller {
    private $roomModel;
    
    public function __construct() {
        parent::__construct();
        $this->roomModel = new Room();
    }
    
    public function index() {
        $rooms = $this->roomModel->findAll();
        $this->view('room/index', ['rooms' => $rooms]);
    }
    
    public function show($id) {
        $room = $this->roomModel->findById($id);
        if (!$room) {
            header('Location: /rooms');
            exit;
        }
        $this->view('room/show', ['room' => $room]);
    }
    
    public function reserve() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomId = $_POST['chambre_id'];
            $checkIn = $_POST['checkin_date'];
            $checkOut = $_POST['checkout_date'];
            
            if ($this->roomModel->isAvailable($roomId, $checkIn, $checkOut)) {
                $this->roomModel->createReservation([
                    'room_id' => $roomId,
                    'user_id' => $_SESSION['user']['id'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut
                ]);
                header('Location: /reservations');
                exit;
            }
        }
    }
}
