<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
if (getCurrentUserRole() !== 'instructor' && getCurrentUserRole() !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $categoryId = (int)$_POST['category'];
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    $difficulty = sanitizeInput($_POST['difficulty']);
    $syllabus = sanitizeInput($_POST['syllabus']);
    $requirements = sanitizeInput($_POST['requirements']);
    $learning_outcomes = sanitizeInput($_POST['learning_outcomes']);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $userId = getCurrentUserId();

    if (empty($title) || empty($description) || empty($categoryId)) {
        $error = 'Title, description, and category are required.';
    } else {
        try {
            // Insert course
            $stmt = $pdo->prepare("
                INSERT INTO courses (title, description, category_id, instructor_id, price, 
                duration, difficulty, syllabus, requirements, learning_outcomes, is_published) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([$title, $description, $categoryId, $userId, $price, 
                $duration, $difficulty, $syllabus, $requirements, $learning_outcomes, $isPublished]);

            if ($result) {
                $success = 'Course created successfully!';
                // Redirect to avoid resubmission
                header('Location: my_courses.php');
                exit();
            } else {
                $error = 'Error creating course. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error creating course: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - StudyHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">StudyHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['first_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (getCurrentUserRole() === 'student'): ?>
                                <li><a class="dropdown-item" href="../student/dashboard.php">My Dashboard</a></li>
                                <li><a class="dropdown-item" href="../student/my_courses.php">My Courses</a></li>
                            <?php elseif (getCurrentUserRole() === 'instructor'): ?>
                                <li><a class="dropdown-item" href="dashboard.php">Instructor Dashboard</a></li>
                                <li><a class="dropdown-item" href="my_courses.php">My Courses</a></li>
                            <?php elseif (getCurrentUserRole() === 'admin'): ?>
                                <li><a class="dropdown-item" href="../admin/dashboard.php">Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Create New Course</h1>
                <p class="text-muted">Fill in the details for your new course</p>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">Course Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select a category</option>
                                            <?php 
                                            $categories = getCategories();
                                            foreach ($categories as $category): 
                                            ?>
                                                <option value="<?php echo $category['category_id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="difficulty" class="form-label">Difficulty Level</label>
                                        <select class="form-select" id="difficulty" name="difficulty" required>
                                            <option value="beginner">Beginner</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price ($)</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" id="duration" name="duration" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="learning_outcomes" class="form-label">Learning Outcomes</label>
                                <textarea class="form-control" id="learning_outcomes" name="learning_outcomes" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="syllabus" class="form-label">Syllabus</label>
                                <textarea class="form-control" id="syllabus" name="syllabus" rows="5" placeholder="Enter your course modules/lessons here..."></textarea>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1">
                                <label class="form-check-label" for="is_published">Publish Course</label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="my_courses.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Course</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Course Creation Tips</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Choose a descriptive title</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Write a compelling description</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Set realistic learning outcomes</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Structure your syllabus clearly</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Price competitively</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Course Requirements</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">List any prerequisites students should have before taking your course.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>StudyHub</h5>
                    <p>Your gateway to quality education and skill development.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="../index.php" class="text-white">Home</a></li>
                        <li><a href="../courses.php" class="text-white">Courses</a></li>
                        <li><a href="dashboard.php" class="text-white">Instructor Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p>Email: info@studyhub.com</p>
                    <p>Phone: (123) 456-7890</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2025 StudyHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>