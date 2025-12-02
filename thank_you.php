<?php
session_start();
if (!isset($_SESSION['feedback_success'])) {
    header("Location: index.php");
    exit();
}

unset($_SESSION['feedback_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You | Our Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6d5d6e;
            --secondary: #f4eee0;
            --accent: #a6b1e1;
            --dark: #393646;
            --light: #f8f5f1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }
        
        .thank-you-container {
            text-align: center;
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            transform: scale(0.9);
            opacity: 0;
            animation: fadeIn 0.5s forwards 0.3s;
        }
        
        @keyframes fadeIn {
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .checkmark {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px;
            animation: checkmarkScale 0.5s cubic-bezier(0.42, 0, 0.58, 1.5);
        }
        
        @keyframes checkmarkScale {
            0% { transform: scale(0); }
            70% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .checkmark i {
            color: white;
            font-size: 50px;
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        p {
            margin-bottom: 30px;
            color: var(--dark);
            font-size: 18px;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(109, 93, 110, 0.3);
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--accent);
            opacity: 0;
        }
        
        @keyframes confettiFall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <div class="checkmark">
            <i class="fas fa-check"></i>
        </div>
        <h1>Thank You!</h1>
        <p>We truly appreciate you taking the time to share your feedback with us. Your insights help us improve and serve you better.</p>
        <a href="index.php" class="btn">Return to Home</a>
    </div>

    <script>
        // Create confetti effect
        function createConfetti() {
            const colors = ['#a6b1e1', '#6d5d6e', '#f4eee0', '#d4a5a5'];
            const container = document.body;
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.animation = `confettiFall ${Math.random() * 3 + 2}s linear forwards`;
                confetti.style.animationDelay = Math.random() * 2 + 's';
                container.appendChild(confetti);
                
                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 5000);
        
        // Create confetti on load
        window.onload = function() {
            createConfetti();
        };
    </script>
</body>
</html>