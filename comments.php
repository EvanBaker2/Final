<?php
session_start();
require 'database.php'; // Database connection

$pdo = Database::connect();
$error_message = "";

// Fetch comments
$sql = "SELECT * FROM iss_comments ORDER BY posted_date DESC";
$comments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Add comment
    if (isset($_POST['add_comment'])) {
        $short_comment = $_POST['short_comment'];
        $long_comment = $_POST['long_comment'];

        if (!empty($short_comment) && !empty($long_comment)) {
            $stmt = $pdo->prepare("INSERT INTO iss_comments (short_comment, long_comment, posted_date) VALUES (?, ?, NOW())");
            $stmt->execute([$short_comment, $long_comment]);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Both fields are required.";
        }
    }

    // Update comment
    if (isset($_POST['update_comment'])) {
        $id = $_POST['id'];
        $short = $_POST['short_comment'];
        $long = $_POST['long_comment'];

        $sql = "UPDATE iss_comments SET short_comment = ?, long_comment = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$short, $long, $id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Delete comment
    if (isset($_POST['delete_comment'])) {
        $id = $_POST['id'];

        $sql = "DELETE FROM iss_comments WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">Comments List</h2>
        
        <table class="table table-striped table-sm mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Short Comment</th>
                    <th>Posted Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment) : ?>
                    <tr>
    <td><?= htmlspecialchars($comment['id']); ?></td>
    <td><?= htmlspecialchars($comment['short_comment']); ?></td>
    <td><?= htmlspecialchars($comment['posted_date']); ?></td>
    <td>
        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readComment<?= $comment['id']; ?>">R</button>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateComment<?= $comment['id']; ?>">U</button>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteComment<?= $comment['id']; ?>">D</button>
    </td>
</tr>


                    <!-- Read Modal -->
                    <div class="modal fade" id="readComment<?= $comment['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Comment Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Short Comment:</strong> <?= htmlspecialchars($comment['short_comment']); ?></p>
                                    <p><strong>Long Comment:</strong> <?= nl2br(htmlspecialchars($comment['long_comment'])); ?></p>
                                    <p><strong>Posted Date:</strong> <?= htmlspecialchars($comment['posted_date']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updateComment<?= $comment['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $comment['id']; ?>">
                                        <textarea name="short_comment" class="form-control mb-2" required><?= htmlspecialchars($comment['short_comment']); ?></textarea>
                                        <textarea name="long_comment" class="form-control mb-2" required><?= htmlspecialchars($comment['long_comment']); ?></textarea>
                                        <button type="submit" name="update_comment" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteComment<?= $comment['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this comment?</p>
                                    <p><strong>Short Comment:</strong> <?= htmlspecialchars($comment['short_comment']); ?></p>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $comment['id']; ?>">
                                        <button type="submit" name="delete_comment" class="btn btn-danger">Delete</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>

 <a href="issuesList.php" class="btn btn-secondary mb-3">← Back to Issues List</a>

                <!-- Add Comment Button -->
<div class="text-end mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCommentModal">Add Comment</button>
</div>

<!-- Add Comment Modal -->
<div class="modal fade" id="addCommentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="short_comment" class="form-control mb-2" placeholder="Short Comment" required></textarea>
                    <textarea name="long_comment" class="form-control mb-2" placeholder="Long Comment" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_comment" class="btn btn-primary">Post Comment</button>
                </div>
            </form>
        </div>
    </div>
</div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php Database::disconnect(); ?>
