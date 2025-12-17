<?php
session_start();
include '../db.php';

if(isset($_POST['name'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        echo "Email already registered!";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO users (name,email,phone,password) VALUES ('$name','$email','$phone','$password')");
        if($insert){
            $_SESSION['user_email'] = $email;  // Session create
            echo "Sign Up Successful!";
        } else {
            echo "Error in Sign Up!";
        }
    }
} else {
    echo "Form not submitted!";
}
?>
