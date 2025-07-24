<?php
session_start();

if (isset($_POST['book_id'])) {
    $bookId = (int) $_POST['book_id'];

    if (isset($_SESSION['cart'])) {
        $index = array_search($bookId, $_SESSION['cart']);
        if ($index !== false) {
            unset($_SESSION['cart'][$index]);
            // Reset indeks array agar tidak lompat
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
}

header("Location: cart_view.php");
exit;
