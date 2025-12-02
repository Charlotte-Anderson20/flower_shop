<?php
session_start();
require 'includes/db.php';

// Only assign session variables if $customer is set and is an array
if (isset($customer) && is_array($customer)) {
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_image'] = $customer['customer_image'];
}

$customer_id = $_SESSION['customer_id'] ?? null;

// Get all arrangement types
$query = "SELECT * FROM arrangement_type ORDER BY arrangement_name";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flower Arrangements</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: rgb(240, 164, 194);
            --primary-dark: rgb(196, 44, 107);
            --secondary: rgb(201, 55, 126);
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 60px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1592078615290-033ee584e267?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            opacity: 0.15;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        /* Arrangements Grid */
        .arrangements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .arrangement-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .arrangement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .card-image {
            position: relative;
            padding-top: 100%; /* Square aspect ratio */
            overflow: hidden;
        }

        .card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .arrangement-card:hover .card-image img {
            transform: scale(1.05);
        }

        .card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }

        .card-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .card-category {
            display: inline-block;
            background-color: var(--light-gray);
            color: var(--gray);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* No results */
        .no-results {
            text-align: center;
            padding: 60px 0;
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 3rem;
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 80px 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .arrangements-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>

<?php include 'header.php' ?>
    <!-- Hero Section -->
    <section class="hero animate__animated animate__fadeIn">
        <div class="hero-content">
            <h1>Beautiful Flower Arrangements</h1>
            <p>Explore our collection of stunning floral arrangements for every occasion</p>
        </div>
    </section>

    <div class="container">
        <!-- Arrangements Grid -->
        <section class="arrangements-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($arrangement = $result->fetch_assoc()): ?>
                    <div class="arrangement-card animate__animated animate__fadeInUp">
                        <div class="card-image">
                            <?php if (!empty($arrangement['icon_image'])): ?>
                                <img src="<?php echo htmlspecialchars($arrangement['icon_image']); ?>" alt="<?php echo htmlspecialchars($arrangement['arrangement_name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/500x500?text=No+Image" alt="No image available">
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($arrangement['arrangement_name']); ?></h3>
                            <span class="card-category">Flower Arrangement</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results animate__animated animate__fadeIn">
                    <i class="fas fa-box-open"></i>
                    <h3>No arrangements found</h3>
                    <p>Please check back later</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <?php include 'includes/footer.php' ?>
</body>
</html>