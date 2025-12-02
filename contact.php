<?php
session_start();
require 'includes/db.php'; // should define $con (mysqli connection)

// Only assign session variables if $customer is set and is an array
if (isset($customer) && is_array($customer)) {
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_image'] = $customer['customer_image'];
}

$customer_id = $_SESSION['customer_id'] ?? null;

// If customer is logged in, get their details
$customer_data = null;
if ($customer_id) {
    $stmt = $con->prepare("SELECT * FROM customer WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer_data = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        :root {
            --primary-pink: #ff69b4;
            --light-pink: #ffb6c1;
            --dark-pink: #db7093;
            --white: #fff8f8;
            --gray: #f5f5f5;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--white);
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(255, 105, 180, 0.1);
        }
        
        h1 {
            color: var(--dark-pink);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .contact-form {
            width: 100%;
        }
        
        .contact-form table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .contact-form table tr:not(:last-child) {
            margin-bottom: 20px;
        }
        
        .contact-form table td {
            padding: 15px 0;
        }
        
        .contact-form table td:first-child {
            width: 30%;
            font-weight: 500;
            color: var(--dark-pink);
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-pink);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-pink);
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.2);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: var(--primary-pink);
            color: white;
            border: none;
            padding: 14px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            width: auto;
            display: block;
            margin: 30px auto 0;
        }
        
        .submit-btn:hover {
            background-color: var(--dark-pink);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(219, 112, 147, 0.3);
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #c3e6cb;
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #f5c6cb;
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .required:after {
            content: " *";
            color: var(--primary-pink);
        }

        .customer-info-note {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-pink);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .customer-info-note i {
            color: var(--primary-pink);
            font-size: 1.2rem;
        }

         /* Contact Hero Section */
    .contact-hero {
        position: relative;
        height: 60vh;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        background-image: url('https://images.unsplash.com/photo-1526397751294-331021109fbd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-position: center;
        margin-bottom: 40px;
    }
    
    .contact-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(219, 112, 147, 0.85) 0%, rgba(255, 105, 180, 0.8) 100%);
    }
    
    .contact-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 0 20px;
        max-width: 800px;
        margin: 0 auto;
        animation: fadeInUp 0.8s ease-out;
    }
    
    .contact-hero h1 {
        font-size: 3rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .contact-hero p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        opacity: 0.9;
        line-height: 1.6;
    }
    
    .contact-hero-info {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 40px;
    }
    
    .contact-method {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        padding: 15px 25px;
        border-radius: 50px;
        transition: all 0.3s ease;
    }
    
    .contact-method:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-3px);
    }
    
    .contact-method i {
        font-size: 1.2rem;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .contact-hero {
            height: 70vh;
            min-height: 500px;
        }
        
        .contact-hero h1 {
            font-size: 2.2rem;
        }
        
        .contact-hero p {
            font-size: 1rem;
        }
        
        .contact-hero-info {
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }
        
        .contact-method {
            width: 100%;
            max-width: 250px;
            justify-content: center;
        }
    }
    </style>
</head>
<body>
    <?php include 'header.php' ?>
     <section class="contact-hero">
        <div class="contact-hero-content">
            <h1>We'd Love to Hear From You</h1>
            <p>Our floral experts are here to answer your questions and help create your perfect arrangement</p>
            <div class="contact-hero-info">
                <div class="contact-method">
                    <i class="fas fa-phone-alt"></i>
                    <span>+1 (555) 123-4567</span>
                </div>
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <span>hello@tinyflowershop.com</span>
                </div>
                <div class="contact-method">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Blossom St, Flower City</span>
                </div>
            </div>
        </div>
        <div class="contact-hero-overlay"></div>
    </section>

    <div class="container">
        <h1>Get In Touch</h1>
        
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="success-message" style="display: block;">
                <?php echo $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="error-message" style="display: block;">
                <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if($customer_data): ?>
        <div class="customer-info-note">
            <i class="fas fa-info-circle"></i>
            <span>We'll use your account information: <strong><?php echo htmlspecialchars($customer_data['customer_name']); ?></strong> (<?php echo htmlspecialchars($customer_data['customer_email']); ?>)</span>
        </div>
        <?php endif; ?>
        
        <form class="contact-form" action="process_contact.php" method="POST">
            <?php if($customer_data): ?>
                <!-- Hidden fields for logged-in customers -->
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($customer_data['customer_name']); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($customer_data['customer_email']); ?>">
                <input type="hidden" name="phone" value="<?php echo htmlspecialchars($customer_data['customer_phone'] ?? ''); ?>">
            <?php else: ?>
                <!-- Visible fields for non-logged-in users -->
                <table>
                    <tr>
                        <td class="required">Your Name</td>
                        <td><input type="text" name="name" required></td>
                    </tr>
                    <tr>
                        <td class="required">Email Address</td>
                        <td><input type="email" name="email" required></td>
                    </tr>
                    <tr>
                        <td>Phone Number</td>
                        <td><input type="tel" name="phone"></td>
                    </tr>
            <?php endif; ?>
            
            <table>
                <tr>
                    <td>Subject</td>
                    <td>
                        <select name="subject">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Support">Support</option>
                            <option value="Feedback">Feedback</option>
                            <option value="Other">Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="required">Your Message</td>
                    <td><textarea name="message" required></textarea></td>
                </tr>
            </table>
            <button type="submit" class="submit-btn">Send Message</button>
        </form>
    </div>
    <?php include 'includes/footer.php' ?>
</body>
</html>