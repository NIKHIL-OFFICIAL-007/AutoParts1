<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same head content as cart.php -->
</head>
<body class="bg-gray-50 font-sans flex flex-col min-h-screen">
    <!-- Same navbar as cart.php -->
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-sm p-8 max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Checkout</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
                    <form>
                        <!-- Address form fields -->
                    </form>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <?php 
                        $cartItems = $_SESSION['cart'] ?? [];
                        $subtotal = 0;
                        
                        foreach ($cartItems as $item) {
                            $itemTotal = $item['price'] * $item['quantity'];
                            $subtotal += $itemTotal;
                        ?>
                        <div class="flex justify-between py-2">
                            <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                            <span>₹<?= number_format($itemTotal, 2) ?></span>
                        </div>
                        <?php } ?>
                        
                        <div class="border-t border-gray-300 pt-4 mt-4">
                            <div class="flex justify-between py-2">
                                <span>Subtotal</span>
                                <span>₹<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span>Shipping</span>
                                <span>₹0.00</span>
                            </div>
                            <div class="flex justify-between py-2 font-bold text-lg">
                                <span>Total</span>
                                <span>₹<?= number_format($subtotal, 2) ?></span>
                            </div>
                        </div>
                        
                        <button class="w-full bg-secondary hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg mt-6 transition-colors">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Same footer as cart.php -->
</body>
</html>