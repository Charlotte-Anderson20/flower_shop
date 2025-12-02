<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Flower Shop - Elegant Floral Arrangements</title>
<!-- Add these to your head section if not already present -->
<!-- Slick Carousel CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<!-- Slick Carousel JS -->
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

   <style>
        :root {
            --primary: #ffb6c1; /* Light pink */
            --primary-dark: #ff8fab;
            --primary-light: #ffdfea;
            --secondary: #fff0f5; /* Very light pink */
            --light: #fff9fb; /* Almost white with pink tint */
            --dark: #5a4a4f; /* Soft dark brown */
            --accent: #ffc0cb; /* Medium pink */
            --text: #5a4a4f; /* Soft dark for text */
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--text);
            line-height: 1.7;
            overflow-x: hidden;
        }
        
       

        /* Logo Image Styles */
.logo-image {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    /* transition: var(--transition); */
}

.logo-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(1) invert(0); 
}

nav.scrolled .logo-image img {
    filter: brightness(1) invert(0); /* Reverts to original colors when scrolled */
}

/* Adjust the logo size when scrolled */
nav.scrolled .logo-image {
    width: 30px;
    height: 30px;
}
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 5%;
            background-color: rgba(221, 87, 87, 0.2);
            position: fixed;
            width: 100%;
            z-index: 100;
            transition: var(--transition);
        }
        
        nav.scrolled {
            background-color: rgba(255, 182, 193, 0.95);
            padding: 1rem 5%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .logo i {
            color: var(--secondary);
            font-size: 2rem;
            transition: var(--transition);
        }
        
        .logo span {
            color: var(--secondary);
            font-weight: 300;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            padding: 5px 0;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary);
            transition: var(--transition);
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .nav-links a:hover {
            color: var(--secondary);
        }
        
        .user-actions {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            margin-left: 2rem;
        }
        
        .user-btn {
            background-color: transparent;
            border: 2px solid var(--secondary);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .user-btn:hover {
            background-color: var(--secondary);
            color: var(--primary-dark);
        }
        
        .user-btn.login {
            background-color: var(--secondary);
            color: var(--primary-dark);
        }
        
        .user-btn.login:hover {
            background-color: transparent;
            color: white;
        }
        
        .cart-btn {
            position: relative;
            color: white;
            font-size: 1.3rem;
            transition: var(--transition);
        }
        
        .cart-btn:hover {
            transform: scale(1.1);
            color: var(--secondary);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary);
            color: var(--primary-dark);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
      
        /* Featured Products */
        .featured {
            padding: 8rem 10% 6rem;
            position: relative;
        }
        
        .featured::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, var(--primary), transparent);
            opacity: 0.1;
            z-index: -1;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-title h2 {
            font-size: 2.8rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent);
        }
        
        .section-title p {
            color: var(--text);
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2.5rem;
        }
        
        .product-card {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(255, 182, 193, 0.1));
            z-index: 1;
            pointer-events: none;
        }
        
        .product-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .product-img {
            height: 280px;
            overflow: hidden;
            position: relative;
        }
        
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        .product-card:hover .product-img img {
            transform: scale(1.1);
        }
        
        .product-info {
            padding: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .product-info h3 {
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .product-info .price {
            font-size: 1.3rem;
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 1rem;
            display: block;
        }
        
        .product-info .size {
            display: inline-block;
            background-color: var(--secondary);
            color: var(--primary-dark);
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .product-info p {
            margin-bottom: 1.5rem;
            color: var(--text);
        }
        
        .product-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .add-to-cart {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-to-cart:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .wishlist {
            color: var(--text);
            font-size: 1.3rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .wishlist:hover {
            color: var(--accent);
            transform: scale(1.2);
        }
        
        /* Arrangement Types */
        .arrangements {
            padding: 6rem 10%;
            background-color: var(--secondary);
            color: var(--dark);
            position: relative;
            overflow: hidden;
        }
        
        .arrangements::before {
            content: '';
            position: absolute;
            top: -100px;
            left: 0;
            width: 100%;
            height: 200px;
            background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="%23fff0f5" opacity=".25"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="%23fff0f5" opacity=".5"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23fff0f5"/></svg>');
            background-size: cover;
            z-index: 1;
        }
        
        .arrangement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
            position: relative;
            z-index: 2;
        }
        
        .arrangement-card {
            background-color: white;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .arrangement-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .arrangement-card i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            transition: var(--transition);
        }
        
        .arrangement-card:hover i {
            transform: rotate(15deg) scale(1.1);
        }
        
        .arrangement-card h3 {
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
            position: relative;
            display: inline-block;
        }
        
        .arrangement-card h3::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background-color: var(--accent);
            transition: var(--transition);
        }
        
        .arrangement-card:hover h3::after {
            width: 80px;
        }
        
        .arrangement-card p {
            color: var(--text);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 8rem 10%;
            background-color: white;
            position: relative;
        }
        
        .testimonials::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, var(--secondary), transparent);
            opacity: 0.5;
            z-index: 1;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
            position: relative;
            z-index: 2;
        }
        
        .testimonial-card {
            background-color: var(--light);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .testimonial-card::before {
            content: '\201C';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 8rem;
            color: rgba(255, 182, 193, 0.1);
            font-family: serif;
            line-height: 1;
            z-index: 1;
        }
        
        .testimonial-card .rating {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            position: relative;
            z-index: 2;
        }
        
        .testimonial-card p {
            font-style: italic;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        .testimonial-card .customer {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }
        
        .customer-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .customer-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .customer-info h4 {
            font-size: 1.1rem;
            color: var(--primary-dark);
        }
        
        .customer-info p {
            font-size: 0.9rem;
            color: var(--text);
            font-style: normal;
            margin: 0;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 8rem 10% 3rem;
            position: relative;
            overflow: hidden;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, var(--primary), transparent);
            opacity: 0.1;
            z-index: 1;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 4rem;
            position: relative;
            z-index: 2;
        }
        
        .footer-col h3 {
            font-size: 1.4rem;
            margin-bottom: 2rem;
            color: var(--secondary);
            position: relative;
            display: inline-block;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--accent);
        }
        
        .footer-col p, .footer-col a {
            color: #ddd;
            margin-bottom: 1.2rem;
            display: block;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-col a:hover {
            color: var(--secondary);
            padding-left: 5px;
        }
        
        .footer-col i {
            margin-right: 10px;
            color: var(--accent);
            width: 20px;
            text-align: center;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: var(--transition);
            color: white;
            font-size: 1.1rem;
        }
        
        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-5px);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border-radius: 50px;
            border: none;
            background-color: rgba(255,255,255,0.1);
            color: black;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-group input:focus {
            outline: none;
            background-color: rgba(255,255,255,0.2);
            box-shadow: 0 0 0 2px var(--accent);
        }
        
        .form-group input::placeholder {
            color: #333;
        }
        
        .form-submit {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            width: 100%;
        }
        
        .form-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 0.9rem;
            position: relative;
            z-index: 2;
        }
        
        /* Floating flower animation */
        @keyframes float-up {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
/* Modern Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(90, 74, 79, 0.85);
    z-index: 2000;
    display: none;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-out;
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    padding: 2.5rem;
    border-radius: 18px;
    max-width: 420px;
    width: 90%;
    box-shadow: 0 15px 40px rgba(90, 74, 79, 0.2);
    animation: slideUp 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 182, 193, 0.4);
}

.modal-header {
    text-align: center;
    margin-bottom: 1.5rem;
    position: relative;
}

.logo-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 1rem;
}

.logo-wrapper .logo-image {
    width: 36px;
    height: 36px;
}

.logo-wrapper .logo-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.modal-header h2 {
    color: var(--primary-dark);
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
    font-family: 'Montserrat', sans-serif;
}

.subtitle {
    color: var(--text);
    font-size: 0.95rem;
    margin: 0.5rem 0 0;
    opacity: 0.8;
}

.modal-form-container {
    padding: 0.5rem 0;
    margin: 0.5rem 0;
}

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 1.2rem;
}

.input-with-icon i {
    position: absolute;
    left: 18px;
    color: var(--primary);
    font-size: 1rem;
}

.input-with-icon input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 1px solid rgba(255, 182, 193, 0.5);
    border-radius: 10px;
    font-size: 0.95rem;
    transition: var(--transition);
    background: rgba(255, 182, 193, 0.05);
}

.input-with-icon input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 182, 193, 0.2);
}

.show-password {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    color: var(--text);
    opacity: 0.5;
    cursor: pointer;
    transition: var(--transition);
}

.show-password:hover {
    opacity: 1;
    color: var(--primary);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0.8rem 0 1.5rem;
    font-size: 0.9rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.remember-me input {
    accent-color: var(--primary);
}

.forgot-password {
    color: var(--primary-dark);
    text-decoration: none;
    transition: var(--transition);
}

.forgot-password:hover {
    color: var(--primary);
    text-decoration: underline;
}

.modal-btn {
    background: linear-gradient(to right, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    width: 100%;
    margin: 1.5rem 0;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
}

.modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 182, 193, 0.4);
}

.social-login-container {
    margin: 1.5rem 0;
}

.divider {
    display: flex;
    align-items: center;
    color: var(--text);
    font-size: 0.85rem;
    opacity: 0.7;
    margin: 1.5rem 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid rgba(255, 182, 193, 0.3);
}

.divider span {
    padding: 0 1rem;
}

.social-login {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.social-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 1px solid rgba(255, 182, 193, 0.3);
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    transition: var(--transition);
}

.social-btn.google {
    color: #DB4437;
}

.social-btn.facebook {
    color: #4267B2;
}

.social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.modal-footer {
    text-align: center;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 182, 193, 0.2);
    font-size: 0.95rem;
    color: var(--text);
}

.modal-footer a {
    color: var(--primary-dark);
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}

.modal-footer a:hover {
    color: var(--primary);
    text-decoration: underline;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--primary-dark);
    transition: var(--transition);
    background: rgba(255, 182, 193, 0.1);
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-modal:hover {
    color: white;
    background: var(--primary);
    transform: rotate(90deg);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .modal-content {
        padding: 2rem 1.5rem;
        border-radius: 16px;
    }
    
    .modal-header h2 {
        font-size: 1.6rem;
    }
    
    .input-with-icon input {
        padding: 0.9rem 0.9rem 0.9rem 2.8rem;
    }
    
    .social-btn {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
}
   /* Enhanced Register Modal Styles */
.modal-scroll-container {
    max-height: 60vh;
    overflow-y: auto;
    margin: 0 -1rem;
    padding: 0 1rem;
}

/* Custom Scrollbar */
.modal-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.modal-scroll-container::-webkit-scrollbar-track {
    background: rgba(255, 182, 193, 0.1);
    border-radius: 3px;
}

.modal-scroll-container::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 3px;
}

.modal input[type="text"],
.modal input[type="email"],
.modal input[type="password"],
.modal input[type="tel"],
.modal input[type="file"],
.modal input[type="hidden"] {
    color: black;
}


/* Form Layout Improvements */
.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.half-width {
    flex: 1;
    min-width: 0;
}

/* File Upload Styling */
.file-upload {
    position: relative;
}

.file-upload-label {
    display: flex;
    align-items: center;
    padding: 0.9rem 1rem 0.9rem 3rem;
    border: 1px solid rgba(255, 182, 193, 0.5);
    border-radius: 10px;
    background: rgba(255, 182, 193, 0.05);
    cursor: pointer;
    position: relative;
    height: 48px;
    box-sizing: border-box;
}

.file-upload-label i {
    position: absolute;
    left: 18px;
    color: var(--primary);
}

.file-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: calc(100% - 20px);
}

.file-upload input[type="file"] {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.file-hint {
    font-size: 0.75rem;
    color: var(--text);
    opacity: 0.7;
    margin-top: 0.3rem;
    padding-left: 1rem;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .half-width {
        width: 100%;
    }
    
    .modal-scroll-container {
        max-height: 55vh;
    }
}     
        /* Animation classes */
        .animate-delay-1 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.4s;
        }
        
        .animate-delay-3 {
            animation-delay: 0.6s;
        }
        
        /* Floating flowers */
        .floating-flower {
            position: absolute;
            opacity: 0.7;
            animation: float-up 15s linear infinite;
            z-index: 1;
            pointer-events: none;
        }

        .user-profile {
    display: flex;
    align-items: center;
}

.profile-dropdown {
    position: relative;
    display: inline-block;
}

.profile-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.profile-image:hover {
    transform: scale(1.1);
}

.profile-name {
    margin-left: 10px;
    color: #333;
    font-weight: 500;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    z-index: 1;
    border-radius: 4px;
    overflow: hidden;
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.profile-dropdown:hover .dropdown-content {
    display: block;
}

/* Products Dropdown Styles */
.nav-links .dropdown {
    position: relative;
    display: inline-block;
}

.nav-links .dropdown-content {
    display: none;
    position: absolute;
    background-color: rgba(255, 255, 255, 0.95);
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-radius: 8px;
    z-index: 1;
    top: 100%;
    left: 0;
    padding: 10px 0;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}

.nav-links .dropdown:hover .dropdown-content {
    display: block;
    opacity: 1;
    visibility: visible;
}

.nav-links .dropdown-content a {
    color: var(--dark);
    padding: 10px 20px;
    text-decoration: none;
    display: block;
    font-size: 0.95rem;
    transition: var(--transition);
}

.nav-links .dropdown-content a:hover {
    background-color: var(--primary-light);
    color: var(--primary-dark);
    padding-left: 25px;
}

.nav-links .dropdown-content a i {
    margin-right: 8px;
    width: 18px;
    text-align: center;
}

/* Add arrow indicator */
.nav-links .dropdown > a::after {
    content: '\f078';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 6px;
    font-size: 0.8rem;
    transition: var(--transition);
}

.nav-links .dropdown:hover > a::after {
    transform: rotate(180deg);
}

/* =======================
   RESPONSIVE ADJUSTMENTS
   ======================= */

/* Tablets (<= 992px) */
@media (max-width: 992px) {
    nav {
        padding: 1.5rem 4%;
    }

    .nav-links {
        gap: 1.2rem;
    }

    .section-title h2 {
        font-size: 2.2rem;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }

    .testimonial-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

/* Mobile (<= 768px) */
@media (max-width: 768px) {
    nav {
        flex-wrap: wrap;
        padding: 1rem 5%;
    }

    .nav-links {
        display: none; /* Hide by default for mobile */
        flex-direction: column;
        gap: 1rem;
        /* background: var(--primary-dark); */
        width: 100%;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .nav-links.active {
        display: flex; /* Show when toggled */
    }

    .user-actions {
        margin-left: 0;
        gap: 1rem;
    }

    .section-title h2 {
        font-size: 2rem;
    }

    .products-grid {
        grid-template-columns: 1fr;
    }

    .arrangement-grid {
        grid-template-columns: 1fr;
    }

    .testimonial-grid {
        grid-template-columns: 1fr;
    }

    .footer-grid {
        grid-template-columns: 1fr;
    }
}

/* Small mobile (<= 576px) */
@media (max-width: 576px) {
    .logo {
        font-size: 1.4rem;
    }

    nav {
        padding: 0.8rem 4%;
    }

    .logo-image {
        width: 30px;
        height: 30px;
    }

    .section-title h2 {
        font-size: 1.6rem;
    }

    .section-title p {
        font-size: 0.95rem;
    }

    .product-info h3 {
        font-size: 1.1rem;
    }

    .product-info .price {
        font-size: 1.1rem;
    }

    .add-to-cart {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }

    footer {
        padding: 4rem 6% 2rem;
    }
}

 /* Mobile Navigation Toggle Button */
    .mobile-menu-toggle {
        display: none; /* Hidden by default on desktop */
        background: transparent;
        border: none;
        color: white;
        font-size: 1.8rem;
        cursor: pointer;
        padding: 0.5rem;
        transition: var(--transition);
        z-index: 1001;
    }

    .mobile-menu-toggle:hover {
        color: var(--secondary);
    }

    /* Mobile Navigation Styles */
    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: block; /* Show on mobile */
            order: 1; /* Position to the right */
        }

        nav {
            padding: 1rem 5%;
            flex-wrap: nowrap;
        }

        .logo {
            order: 0; /* Logo on the left */
            flex-grow: 1;
        }

        .user-actions {
            order: 2; /* User actions on the right */
            margin-left: auto;
        }

        .nav-links {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 320px;
            height: 100vh;
            background: var(--primary-dark);
            flex-direction: column;
            justify-content: flex-start;
            padding: 5rem 2rem 2rem;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .nav-links.active {
            right: 0;
        }

        .nav-links a {
            width: 100%;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 1.1rem;
        }

        .nav-links a:hover {
            color: var(--secondary);
            padding-left: 1rem;
        }

        .nav-links a:hover::after {
            display: none;
        }

        /* Dropdown adjustments for mobile */
        .nav-links .dropdown-content {
            position: static;
            box-shadow: none;
            background: rgba(255,255,255,0.1);
            margin: 0.5rem 0;
            display: none;
        }

        .nav-links .dropdown.active .dropdown-content {
            display: block;
        }

        .nav-links .dropdown > a::after {
            transition: transform 0.3s ease;
        }

        .nav-links .dropdown.active > a::after {
            transform: rotate(180deg);
        }

        /* Close button for mobile menu */
        .mobile-menu-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.8rem;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
        }

        /* Overlay when menu is open */
        .nav-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .nav-overlay.active {
            opacity: 1;
            visibility: visible;
        }
    }

    .user-profile {
    position: relative;
    display: inline-block;
}

.profile-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    gap: 8px;
}

.profile-image {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #5DADE2;
}

.dropdown-contents {
    display: none;
    position: absolute;
    right: 0;
    background: rgb(244, 204, 204);
    min-width: 180px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 999;
}

.dropdown-contents a {
    display: block;
    padding: 12px 15px;
    text-decoration: none;
    color: #333;
    transition: background 0.2s ease;
    font-size: 14px;
}

.dropdown-contents a:hover {
    background:rgb(241, 218, 218);
    color: #333;

}

/* Show dropdown when active */
.profile-dropdown.active .dropdown-contents {
    display: block;
}

    </style>
  


    <nav id="navbar">
    <a href="index.php" class="logo">
        <div class="logo-image">
            <img src="images/flowerb.png" alt="TinyFlower Logo">
        </div>
        Tinny<span>Flower</span>
    </a>

    <button class="mobile-menu-toggle" id="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="nav-overlay" id="nav-overlay"></div>

    <div class="nav-links" id="nav-links">
        <!-- <button class="mobile-menu-close" id="mobile-menu-close">
            <i class="fas fa-times"></i>
        </button> -->
        
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <div class="dropdown">
            <a href="shop.php"><i class="fas fa-store"></i> Products</a>
            <div class="dropdown-content">
                <a href="shop.php"><i class="fas fa-bars"></i> All Products</a>
                <a href="accessories.php"><i class="fas fa-gem"></i> Accessories</a>
            </div>
        </div>
        <a href="review.php"><i class="fas fa-heart"></i> Customer Love & Review</a>
        <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>

       <div class="user-actions">
    <?php if(isset($_SESSION['customer_id'])): ?>
        <div class="user-profile">
            <div class="profile-dropdown">
                <button class="profile-btn">
                    <img src="<?php echo !empty($_SESSION['customer_image']) ? $_SESSION['customer_image'] : 'uploads/customers/default-profile.jpg'; ?>" 
                        alt="Profile Image" class="profile-image">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                    <i class="fas fa-caret-down"></i>
                </button>
                <div class="dropdown-contents">
                    <a href="user/dashboard.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="user/my_orders.php"><i class="fas fa-box"></i> My Orders</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    <?php else: ?>
                <button class="user-btn register" id="register-btn"><i class="fas fa-user-plus"></i> Register</button>
                <button class="user-btn login" id="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
            <?php endif; ?>

            <a href="cart.php" id="cart-btn">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cart-count">0</span>
            </a>
        </div>
    </div>
</nav>


    

<div class="modal" id="login-modal">
    <div class="modal-content">
        <span class="close-modal" id="close-login">&times;</span>
        
        <div class="modal-header">
            <div class="logo-wrapper">
                <div class="logo-image">
                    <img src="images/cat_logo.png" alt="TinyFlower Logo">
                </div>
                <h2>Welcome Back</h2>
            </div>
            <p class="subtitle">Sign in to your account</p>
        </div>
        
        <div class="modal-form-container">
            <form id="login-form">
                <div class="form-group">
                    <label for="login-email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="login-email" name="customer_email" placeholder="jimin30@gmail.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="login-password" name="customer_password" placeholder="••••••••" required>
                        <!-- <button type="button" class="show-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button> -->
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <!-- <a href="#" class="forgot-password">Forgot password?</a> -->
                    </div>
                </div>
                
                <input type="hidden" name="action" value="login">
                <button type="submit" class="modal-btn">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="social-login-container">
                <!-- <div class="divider">
                    <span>or continue with</span>
                </div>
                
                <div class="social-login">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i>
                    </button>
                    <button type="button" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                </div> -->
            </div>
        </div>
        
        <div class="modal-footer">
            <p>Don't have an account? <a href="#" id="switch-to-register">Sign up</a></p>
        </div>
    </div>
</div>
    
    <div class="modal" id="register-modal">
    <div class="modal-content">
        <span class="close-modal" id="close-register">&times;</span>
        
        <div class="modal-header">
            <div class="logo-wrapper">
                <div class="logo-image">
                    <img src="images/cat_logo.png" alt="TinyFlower Logo">
                </div>
                <h2>Create Your Account</h2>
            </div>
            <p class="subtitle">Join our floral community</p>
        </div>
        
        <div class="modal-scroll-container">
            <div class="modal-form-container">
                <form id="register-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="register">
                    <!-- Compact Two-Column Layout for Basic Info -->
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="register-name">Full Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="register-name" name="customer_name" placeholder="Jimin Park" required>
                            </div>
                        </div>
                        <div class="form-group half-width">
                            <label for="register-email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="register-email" name="customer_email" placeholder="jimin30@gmail.com" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="register-phone">Phone</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="register-phone" name="customer_phone" placeholder="+1234567890">
                            </div>
                        </div>
                        <div class="form-group half-width">
                            <label for="register-image">Profile Photo</label>
                            <div class="file-upload">
                                <label for="register-image" class="file-upload-label">
                                    <i class="fas fa-camera"></i>
                                    <span class="file-name">Choose image</span>
                                </label>
                                <input type="file" id="register-image" name="customer_image" accept="image/*">
                                <div class="file-hint">JPG/PNG (max 2MB)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-address">Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="register-address" name="customer_address" placeholder="Your full address">
                        </div>
                    </div>
                    
                    <!-- Password Section -->
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="register-password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="register-password" name="customer_password" placeholder="••••••••" required>
                                <!-- <button type="button" class="show-password" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button> -->
                            </div>
                        </div>
                        <div class="form-group half-width">
                            <label for="register-confirm">Confirm Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="register-confirm" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="modal-btn">
                        <span>Create Account</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                
                <div class="social-login-container">
                    <div class="divider">
                        <!-- <span>or sign up with</span> -->
                    </div>
                    
                    <!-- <div class="social-login">
                        <button type="button" class="social-btn google">
                            <i class="fab fa-google"></i>
                        </button>
                        <button type="button" class="social-btn facebook">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                    </div> -->
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <p>Already have an account? <a href="#" id="switch-to-login">Sign in</a></p>
        </div>
    </div>
</div>
 <!-- Move these to the head or right after opening body tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const slides = document.querySelectorAll(".slide");
        slides.forEach((slide, index) => {
            if (index === 0) {
                slide.classList.add("active");
            } else {
                slide.classList.remove("active");
            }
        });
    });
</script>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    
    // Navbar scroll effect for the new header
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });
});
    
    // Modal functionality
    const loginBtn = document.getElementById('login-btn');
    const registerBtn = document.getElementById('register-btn');
    const adminLoginBtn = document.getElementById('admin-login');
    const cartBtn = document.getElementById('cart-btn');
    
    const loginModal = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');
    const adminModal = document.getElementById('admin-modal');
    const cartModal = document.getElementById('cart-modal');
    
    // Function to open modal
    function openModal(modal) {
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Function to close modal
    function closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
    
    // Event listeners for buttons
    if (loginBtn && loginModal) {
        loginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(loginModal);
        });
    }
    
    if (registerBtn && registerModal) {
        registerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(registerModal);
        });
    }
    
    if (adminLoginBtn && adminModal) {
        adminLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(adminModal);
        });
    }
    
   
    
    // Close buttons
    document.getElementById('close-login')?.addEventListener('click', function() {
        closeModal(loginModal);
    });
    
    document.getElementById('close-register')?.addEventListener('click', function() {
        closeModal(registerModal);
    });
    
    document.getElementById('close-admin')?.addEventListener('click', function() {
        closeModal(adminModal);
    });
    
    document.getElementById('close-cart')?.addEventListener('click', function() {
        closeModal(cartModal);
    });
    
    // Switch between login and register
    document.getElementById('switch-to-register')?.addEventListener('click', function(e) {
        e.preventDefault();
        closeModal(loginModal);
        openModal(registerModal);
    });
    
    document.getElementById('switch-to-login')?.addEventListener('click', function(e) {
        e.preventDefault();
        closeModal(registerModal);
        openModal(loginModal);
    });
    
    // Close when clicking outside modal
    window.addEventListener('click', function(e) {
        if (e.target === loginModal) closeModal(loginModal);
        if (e.target === registerModal) closeModal(registerModal);
        if (e.target === adminModal) closeModal(adminModal);
        
    });


    
    
    // Rest of your code...

// Handle login form submission
document.getElementById('login-form')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    // Add the remember me checkbox value manually
    const rememberChecked = form.querySelector('input[name="remember"]')?.checked;
    formData.set('remember', rememberChecked ? 'on' : '');

    fetch('auth_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // ✅ Now expecting JSON
    .then(data => {
        console.log(data);
        if (data.status === 'success') {
            // Redirect to dashboard
            window.location.href = data.redirect;
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during login');
    });
});


// After successful login
fetch('get_wishlist.php')
    .then(response => response.json())
    .then(serverWishlist => {
        const localWishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
        // Merge and dedupe
        const mergedWishlist = [...new Set([...localWishlist, ...serverWishlist])];
        localStorage.setItem('wishlist', JSON.stringify(mergedWishlist));
        updateWishlistUI();
    });
    
document.getElementById('register-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate password match
    const password = document.getElementById('register-password').value;
    const confirm = document.getElementById('register-confirm').value;
    
    if (password !== confirm) {
        alert("Passwords don't match!");
        return;
    }
    
    const formData = new FormData(this);
    
    // Add client-side validation for image
    const imageInput = document.getElementById('register-image');
    if (imageInput.files.length > 0) {
        const file = imageInput.files[0];
        if (file.size > 2 * 1024 * 1024) { // 2MB limit
            alert("Image must be less than 2MB");
            return;
        }
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            alert("Only JPG and PNG images are allowed");
            return;
        }
    }
    
    fetch('auth_handler.php', {
        method: 'POST',
        body: formData // Don't set Content-Type header for FormData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        if (data.includes("successful")) {
            alert("Registration successful! Please login.");
            closeModal(registerModal);
            openModal(loginModal);
            this.reset(); // Reset the form
        } else {
            alert(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during registration');
    });
});
        
        // Create floating flowers dynamically
        function createFlowers() {
            const container = document.querySelector('.floating-flowers');
            const flowerIcons = ['fa-leaf', 'fa-spa', 'fa-seedling', 'fa-feather-alt', 'fa-cloud'];
            
            for (let i = 0; i < 10; i++) {
                const flower = document.createElement('i');
                flower.className = `floating-flower fas ${flowerIcons[Math.floor(Math.random() * flowerIcons.length)]}`;
                
                // Random position
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                
                // Random size
                const size = 0.8 + Math.random() * 1.5;
                
                // Random animation duration
                const duration = 15 + Math.random() * 20;
                
                // Random delay
                const delay = Math.random() * 10;
                
                flower.style.cssText = `
                    left: ${left}%;
                    top: ${top}%;
                    font-size: ${size}rem;
                    animation-duration: ${duration}s;
                    animation-delay: ${delay}s;
                `;
                
                container.appendChild(flower);
            }
        }
        
        // Call the function when page loads
        window.addEventListener('load', createFlowers);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.profile-dropdown');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(event.target)) {
                const content = dropdown.querySelector('.dropdown-content');
                if (content) content.style.display = 'none';
            }
        });
    });
});

 // Mobile menu toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('mobile-menu-toggle');
        const closeBtn = document.getElementById('mobile-menu-close');
        const navLinks = document.getElementById('nav-links');
        const overlay = document.getElementById('nav-overlay');
        const dropdowns = document.querySelectorAll('.nav-links .dropdown');

        // Toggle mobile menu
        toggleBtn?.addEventListener('click', function() {
            navLinks.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close mobile menu
        closeBtn?.addEventListener('click', function() {
            navLinks.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        overlay?.addEventListener('click', function() {
            navLinks.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Dropdown toggle for mobile
        dropdowns?.forEach(dropdown => {
            const link = dropdown.querySelector('a');
            link?.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        });

        // Close dropdowns when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                dropdowns?.forEach(dropdown => {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }
        });
    });
    </script>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    fetch('cart_status.php')
        .then(response => response.text())
        .then(count => {
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) {
                cartCountEl.textContent = count;
            }
        })
        .catch(err => {
            console.error("Failed to fetch cart count:", err);
        });
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const dropdown = document.querySelector(".profile-dropdown");
    const btn = document.querySelector(".profile-btn");

    if (btn) {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            dropdown.classList.toggle("active");
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove("active");
        }
    });
});

</script>