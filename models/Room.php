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
