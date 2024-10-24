# PPE_Final
 project web final pour BTS sio du cour de jeudi MR_shapeche
william et yvanna
 _______________________________________________________________________| 
 |-     chaier de charge           ->  william                          |
 |______________________________________________________________________|
 |-     wire frame                 ->  william                          |
 |______________________________________________________________________|
 |-     use case                   ->  Yvanna                           |
 |______________________________________________________________________|
 |-     mcd                        ->  Yvanna                           |
 |______________________________________________________________________|
 |-     beta corne                 ->  Yvanna                           |
 |______________________________________________________________________|
mvc-hotel-structure.php
# Structure des dossiers
/hotel-mvc/
  /config/
    config.php
    database.php
  /controllers/
    UserController.php
    RoomController.php 
    CarController.php
    ReservationController.php
    AuthController.php
  /models/
    User.php
    Room.php
    Car.php
    Reservation.php
  /views/
    /layouts/
      header.php
      footer.php
    /user/
      login.php
      register.php
      profile.php
    /room/
      index.php
      show.php
    /car/
      index.php
      show.php
    /reservation/
      index.php
  /public/
    index.php
    .htaccess
  /assets/
    /css/
      style.css
    /js/
      scripts.js
    /images/
  /core/
    Router.php
    Controller.php
    Model.php
    View.php

# Configuration - /config/database.php
<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=hotel", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}

# Core - /core/Controller.php
<?php
abstract class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        require_once "../views/" . $view . ".php";
    }
}

# Core - /core/Model.php
<?php
abstract class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

# Model Example - /models/Room.php
<?php
class Room extends Model {
    protected $table = 'chambres';
    
    public function getAvailableRooms($checkIn, $checkOut) {
        $sql = "SELECT * FROM chambres WHERE id NOT IN (
            SELECT chambre_id FROM reservations_chambre 
            WHERE (checkin_date <= ? AND checkout_date >= ?)
            OR (checkin_date <= ? AND checkout_date >= ?)
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$checkOut, $checkIn, $checkOut, $checkIn]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

# Controller Example - /controllers/RoomController.php
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

# Router - /core/Router.php
<?php
class Router {
    private $routes = [];
    
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch($method, $uri) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri)) {
                $controller = new $route['controller']();
                call_user_func_array([$controller, $route['action']], $this->getParams($route['path'], $uri));
                return;
            }
        }
        throw new Exception('Route not found');
    }
    
    private function matchPath($routePath, $uri) {
        $routeSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));
        
        if (count($routeSegments) !== count($uriSegments)) {
            return false;
        }
        
        foreach ($routeSegments as $i => $segment) {
            if ($segment[0] === ':') {
                continue;
            }
            if ($segment !== $uriSegments[$i]) {
                return false;
            }
        }
        
        return true;
    }
    
    private function getParams($routePath, $uri) {
        $params = [];
        $routeSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));
        
        foreach ($routeSegments as $i => $segment) {
            if ($segment[0] === ':') {
                $params[] = $uriSegments[$i];
            }
        }
        
        return $params;
    }
}

# Public - /public/index.php
<?php
session_start();
require_once '../config/config.php';
require_once '../core/Router.php';

$router = new Router();

// Define routes
$router->add('GET', '/', 'HomeController', 'index');
$router->add('GET', '/rooms', 'RoomController', 'index');
$router->add('GET', '/rooms/:id', 'RoomController', 'show');
$router->add('GET', '/cars', 'CarController', 'index');
$router->add('GET', '/cars/:id', 'CarController', 'show');
$router->add('GET', '/login', 'AuthController', 'loginForm');
$router->add('POST', '/login', 'AuthController', 'login');
$router->add('GET', '/register', 'AuthController', 'registerForm');
$router->add('POST', '/register', 'AuthController', 'register');
$router->add('GET', '/profile', 'UserController', 'profile');
$router->add('POST', '/profile', 'UserController', 'updateProfile');

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    // Handle 404 or other errors
    header("HTTP/1.0 404 Not Found");
    require '../views/errors/404.php';
}

# View Example - /views/room/show.php
<?php require_once '../views/layouts/header.php'; ?>

<div class="room-details">
    <h1><?php echo htmlspecialchars($room['type']); ?></h1>
    <img src="data:image/jpeg;base64,<?php echo base64_encode($room['image']); ?>" />
    <p class="description"><?php echo htmlspecialchars($room['description']); ?></p>
    <p class="price">Prix par nuit: <?php echo htmlspecialchars($room['prix_nuit']); ?> €</p>
    
    <?php if (isset($_SESSION['user'])): ?>
        <form action="/rooms/reserve" method="POST" class="reservation-form">
            <input type="hidden" name="chambre_id" value="<?php echo $room['id']; ?>">
            <div class="form-group">
                <label for="checkin_date">Date d'arrivée:</label>
                <input type="text" id="checkin_date" name="checkin_date" class="datepicker" required>
            </div>
            <div class="form-group">
                <label for="checkout_date">Date de départ:</label>
                <input type="text" id="checkout_date" name="checkout_date" class="datepicker" required>
            </div>
            <button type="submit" class="btn btn-primary">Réserver</button>
        </form>
    <?php else: ?>
        <p><a href="/login">Connectez-vous pour réserver</a></p>
    <?php endif; ?>
</div>

<?php require_once '../views/layouts/footer.php'; ?>
