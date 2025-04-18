<?php
session_start();
require 'database.php'; // Database connection

$pdo = Database::connect();
$error_message = "";

// Fetch persons for dropdown list
$persons_sql = "SELECT id, fname, lname FROM iss_persons ORDER BY lname ASC";
$persons_stmt = $pdo->query($persons_sql);
$persons = $persons_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle issue operations (Update, Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // echo "this is a test of file uploads" ; print_r($_FILES); exit(); // checkpoint
     if($_FILES['pdf_attachment']['size'] > 0) {

        $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
        $fileName    = $_FILES['pdf_attachment']['name'];
        $fileSize    = $_FILES['pdf_attachment']['size'];
        $fileType    = $_FILES['pdf_attachment']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        if($fileExtension !== 'pdf') {
            die("Only PDF files allowed");
        }
        if($fileSize > 2 * 1024 * 1024) {
            die("File size exceeds 2MB limit");
        }

        $newFileName = MD5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;
        // if uploads directory does not exist, create it
        if(!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $attachmentPath = $dest_path;
        } else {
            die("error moving file");
        }

    } // end pdf attachment
    
    if (isset($_POST['update_issue'])) {
        $id = $_POST['id'];
        $short_description = trim($_POST['short_description']);
        $long_description = trim($_POST['long_description']);
        $open_date = $_POST['open_date'];
        $close_date = $_POST['close_date'];
        $priority = $_POST['priority'];
        $org = trim($_POST['org']);
        $project = trim($_POST['project']);
        $per_id = $_POST['per_id'];

        $sql = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, close_date=?, priority=?, org=?, project=?, per_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id, $id]);

        header("Location: issues_list.php");
        exit();
    }

    if (isset($_POST['delete_issue'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM iss_issues WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: issues_list.php");
        exit();
    }

     if (isset($_POST['add_issue'])) {
        $short_description = trim($_POST['short_description']);
        $long_description = trim($_POST['long_description']);
        $open_date = $_POST['open_date'];
        $close_date = $_POST['close_date'];
        $priority = $_POST['priority'];
        $org = trim($_POST['org']);
        $project = trim($_POST['project']);
        $per_id = $_POST['per_id'];
    
        $sql = "INSERT INTO iss_issues (short_description, long_description, open_date, close_date, priority, org, project, per_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id]);
    
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
}

// Fetch all issues
$sql = "SELECT * FROM iss_issues ORDER BY open_date DESC";
$issues = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List - DSR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

    <!-- Add Issue Modal -->
<div class="modal fade" id="addIssueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add New Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="short_description" class="form-control mb-2" placeholder="Short Description" required>
                    <textarea name="long_description" class="form-control mb-2" placeholder="Long Description"></textarea>
                    <input type="date" name="open_date" class="form-control mb-2" required>
                    <input type="date" name="close_date" class="form-control mb-2">
                    <input type="text" name="priority" class="form-control mb-2" placeholder="Priority">
                    <input type="text" name="org" class="form-control mb-2" placeholder="Organization">
                    <input type="text" name="project" class="form-control mb-2" placeholder="Project">
                    <select name="per_id" class="form-control mb-2" required>
                        <option value="">Assign to...</option>
                        <?php foreach ($persons as $person) : ?>
                            <option value="<?= $person['id']; ?>">
                                <?= htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_issue" class="btn btn-success">Add Issue</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <form>
<label for="pdf_attachment">PDF</label>
    <input type="file" name="pdf_attachment" class="form-control mb-2"
                accept="application/pdf" />

                        </form>
    
<body>
    <div class="container mt-3">
        <h2 class="text-center">Issues List</h2>

        <!-- "+" Button to Add Issue -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <h3>All Issues</h3>
             <div>
        <a href="person.php" class="btn btn-primary me-2">View Persons</a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIssueModal">+ Add Issue</button>
    </div>
        </div>

        <table class="table table-striped table-sm mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Short Description</th>
                    <th>Open Date</th>
                    <th>Close Date</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue) : ?>
                    <tr>
                        <td><?= htmlspecialchars($issue['id']); ?></td>
                        <td><?= htmlspecialchars($issue['short_description']); ?></td>
                        <td><?= htmlspecialchars($issue['open_date']); ?></td>
                        <td><?= htmlspecialchars($issue['close_date']); ?></td>
                        <td><?= htmlspecialchars($issue['priority']); ?></td>
                        <td>
                            <!-- R, U, D Buttons -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readIssue<?= $issue['id']; ?>">R</button>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateIssue<?= $issue['id']; ?>">U</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteIssue<?= $issue['id']; ?>">D</button>

                             <!-- View Comments Button -->
                             <a href="comments.php?issue_id=<?= $issue['id']; ?>" class="btn btn-secondary btn-sm">View Comments</a>
                        </td>
                    </tr>

                    <!-- Read Modal -->
                    <div class="modal fade" id="readIssue<?= $issue['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Issue Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Description:</strong> <?= htmlspecialchars($issue['long_description']); ?></p>
                                    <p><strong>Priority:</strong> <?= htmlspecialchars($issue['priority']); ?></p>
                                    <p><strong>Project:</strong> <?= htmlspecialchars($issue['project']); ?></p>
                                    <p><strong>Organization:</strong> <?= htmlspecialchars($issue['org']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Modal -->
                     <div class="modal fade" id="updateIssue<?= $issue['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h5 class="modal-title">Update Issue</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $issue['id']; ?>">
                    <input type="text" name="short_description" class="form-control mb-2" value="<?= htmlspecialchars($issue['short_description']); ?>" required>
                    <textarea name="long_description" class="form-control mb-2"><?= htmlspecialchars($issue['long_description']); ?></textarea>
                    <input type="date" name="open_date" class="form-control mb-2" value="<?= $issue['open_date']; ?>" required>
                    <input type="date" name="close_date" class="form-control mb-2" value="<?= $issue['close_date']; ?>">

                    <!-- Reordered fields -->
                    <input type="text" name="priority" class="form-control mb-2" value="<?= htmlspecialchars($issue['priority']); ?>" placeholder="Priority">
                    <input type="text" name="org" class="form-control mb-2" value="<?= htmlspecialchars($issue['org']); ?>" placeholder="Organization">
                    <input type="text" name="project" class="form-control mb-2" value="<?= htmlspecialchars($issue['project']); ?>" placeholder="Project">

                    <select name="per_id" class="form-control mb-2" required>
                        <?php foreach ($persons as $person) : ?>
                            <option value="<?= $person['id']; ?>" <?= ($issue['per_id'] == $person['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Save changes button moved to correct position -->
                    <button type="submit" name="update_issue" class="btn btn-primary mt-3">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteIssue<?= $issue['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this issue?</p>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($issue['short_description']); ?></p>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $issue['id']; ?>">
                                        <button type="submit" name="delete_issue" class="btn btn-danger">Delete</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php Database::disconnect(); ?>
