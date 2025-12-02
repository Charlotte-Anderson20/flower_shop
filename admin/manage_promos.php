<?php
session_start();
require_once '../includes/db.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $discount = $_POST['discount'];
    $gift = $_POST['gift'];
    $min_amount = $_POST['min_amount'];
    $status = $_POST['status'];

    if (isset($_POST['promo_id']) && $_POST['promo_id'] != '') {
        // Update
        $id = $_POST['promo_id'];
        $stmt = $con->prepare("UPDATE admin_promos SET title=?, discount_percent=?, gift_description=?, min_order_amount=?, status=? WHERE promo_id=?");
        $stmt->bind_param("sdsdsi", $title, $discount, $gift, $min_amount, $status, $id);
    } else {
        // Insert
        $stmt = $con->prepare("INSERT INTO admin_promos (title, discount_percent, gift_description, min_order_amount, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsds", $title, $discount, $gift, $min_amount, $status);
    }
    $stmt->execute();
    header("Location: manage_promos.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $con->query("DELETE FROM admin_promos WHERE promo_id = $id");
    header("Location: manage_promos.php");
    exit();
}

// Fetch all promos with a display ID that's not the real database ID
$promos = $con->query("SELECT *, CONCAT('PROMO', LPAD(promo_id, 4, '0')) as display_id FROM admin_promos ORDER BY promo_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Promotions - Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #00b894;
            --danger-color: #d63031;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .main-content {
            padding: 2rem;
            animation: fadeIn 0.5s ease;
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: rgba(108, 92, 231, 0.05);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        input, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #5a4bd4;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #e84393;
            transform: translateY(-2px);
        }
        
        .status-active {
            color: var(--success-color);
            font-weight: 500;
        }
        
        .status-inactive {
            color: var(--danger-color);
            font-weight: 500;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .currency {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .no-promos {
            text-align: center;
            padding: 2rem;
            color: #777;
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="dashboard-container">
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <h2>Manage Promotions</h2>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Promo ID</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Gift</th>
                            <th>Min Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($promos->num_rows > 0): ?>
                            <?php while ($row = $promos->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['display_id'] ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= $row['discount_percent'] ?>%</td>
                                    <td><?= htmlspecialchars($row['gift_description']) ?: '—' ?></td>
                                    <td><span class="currency">ks</span><?= number_format($row['min_order_amount'], 2) ?></td>
                                    <td><span class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                                    <td class="actions">
                                        <a href="manage_promos.php?edit=<?= $row['promo_id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="manage_promos.php?delete=<?= $row['promo_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this promotion?')"><i class="fas fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-promos">No promotions found. Add your first promotion below.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        // Edit form data if in edit mode
        $edit = null;
        if (isset($_GET['edit'])) {
            $id = $_GET['edit'];
            $edit = $con->query("SELECT * FROM admin_promos WHERE promo_id = $id")->fetch_assoc();
        }
        ?>

        <div class="card">
            <form method="post">
                <h3><?= $edit ? "Edit Promotion" : "Add New Promotion" ?></h3>
                <input type="hidden" name="promo_id" value="<?= $edit['promo_id'] ?? '' ?>">
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required value="<?= $edit['title'] ?? '' ?>" placeholder="Summer Sale">
                </div>
                
                <div class="form-group">
                    <label for="discount">Discount (%)</label>
                    <input type="number" id="discount" name="discount" step="0.01" min="0" max="100" value="<?= $edit['discount_percent'] ?? '0' ?>" placeholder="10.00">
                </div>
                
                <div class="form-group">
                    <label for="gift">Gift Description (Optional)</label>
                    <input type="text" id="gift" name="gift" value="<?= $edit['gift_description'] ?? '' ?>" placeholder="Free shipping">
                </div>
                
                <div class="form-group">
                    <label for="min_amount">Minimum Order Amount (ks)</label>
                    <input type="number" id="min_amount" name="min_amount" step="0.01" min="0" required value="<?= $edit['min_order_amount'] ?? '' ?>" placeholder="5000.00">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" <?= (isset($edit) && $edit['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($edit) && $edit['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-<?= $edit ? 'save' : 'plus' ?>"></i> <?= $edit ? "Update Promotion" : "Add Promotion" ?>
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>