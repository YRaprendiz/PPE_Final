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
