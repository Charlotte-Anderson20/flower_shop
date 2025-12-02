<?php
session_start();
require_once '../includes/db.php'; // Adjust the path as necessary
require_once 'admin_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$occasion = ['occasion_id' => '', 'occasion_name' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_occasion'])) {
        $occasion_name = trim($_POST['occasion_name']);
        $occasion_id = $_POST['occasion_id'] ?? 0;
        
        if (empty($occasion_name)) {
            $message = displayAlert('Occasion name is required', 'danger');
        } else {
            // Check for duplicate occasion name (case-insensitive)
            $check_stmt = $con->prepare("SELECT occasion_id FROM Occasions WHERE LOWER(occasion_name) = LOWER(?) AND occasion_id != ?");
            $check_stmt->bind_param("si", $occasion_name, $occasion_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $message = displayAlert('An occasion with this name already exists!', 'danger');
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                if ($occasion_id > 0) {
                    // Update existing occasion
                    $stmt = $con->prepare("UPDATE Occasions SET occasion_name=? WHERE occasion_id=?");
                    $stmt->bind_param("si", $occasion_name, $occasion_id);
                } else {
                    // Insert new occasion
                    $stmt = $con->prepare("INSERT INTO Occasions (occasion_name) VALUES (?)");
                    $stmt->bind_param("s", $occasion_name);
                }
                
                if ($stmt->execute()) {
                    $message = displayAlert('Occasion saved successfully!', 'success');
                    // Clear the form after successful submission if it was an add operation
                    if ($occasion_id == 0) {
                        $occasion['occasion_name'] = '';
                    }
                } else {
                    $message = displayAlert('Error saving occasion: ' . $stmt->error, 'danger');
                }
                $stmt->close();
            }
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $occasion_id = intval($_GET['edit']);
    $result = $con->query("SELECT * FROM Occasions WHERE occasion_id = $occasion_id");
    if ($result->num_rows > 0) {
        $occasion = $result->fetch_assoc();
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $occasion_id = intval($_GET['delete']);
    
    // First check if this occasion is used in any products
    $check = $con->query("SELECT COUNT(*) as count FROM Product_Occasions WHERE occasion_id = $occasion_id");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = displayAlert('Cannot delete this occasion as it is used in products.', 'danger');
    } else {
        // Delete from database
        if ($con->query("DELETE FROM Occasions WHERE occasion_id = $occasion_id")) {
            $message = displayAlert('Occasion deleted successfully!', 'success');
        } else {
            $message = displayAlert('Error deleting occasion: ' . $con->error, 'danger');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Occasions - Admin Dashboard</title>
  <link rel="shortcut icon" href="../images/flowerb.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="card fade-in">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-calendar-alt"></i> Manage Occasions</h2>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <div class="form-container slide-in">
                        <form method="POST" id="occasionForm">
                            <input type="hidden" name="occasion_id" value="<?php echo $occasion['occasion_id']; ?>">
                            
                            <div class="form-group">
                                <label class="form-label" for="occasion_name">Occasion Name</label>
                                <input type="text" class="form-control" id="occasion_name" name="occasion_name" 
                                       value="<?php echo htmlspecialchars($occasion['occasion_name']); ?>" required
                                       pattern=".{2,100}" title="Occasion name must be between 2 and 100 characters">
                                <small class="form-text text-muted">Must be unique and between 2-100 characters</small>
                            </div>
                            
                            <button type="submit" name="save_occasion" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Occasion
                            </button>
                            
                            <?php if (!empty($occasion['occasion_id'])): ?>
                                <a href="manage_occasions.php" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="table-responsive slide-in" style="animation-delay: 0.2s;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Occasion Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                $result = $con->query("SELECT * FROM Occasions ORDER BY occasion_id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($row['occasion_name']); ?></td>
                                    <td class="table-actions">
                                        <a href="manage_occasions.php?edit=<?php echo $row['occasion_id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="manage_occasions.php?delete=<?php echo $row['occasion_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this occasion? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="admin_scripts.js"></script>
    <script>
        // Client-side validation to prevent form submission if occasion name is invalid
        document.getElementById('occasionForm').addEventListener('submit', function(e) {
            const occasionName = document.getElementById('occasion_name').value.trim();
            
            if (occasionName.length < 2 || occasionName.length > 100) {
                e.preventDefault();
                alert('Occasion name must be between 2 and 100 characters');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>