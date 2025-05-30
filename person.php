<?php
session_start();
require 'database.php'; // Database connection

$pdo = Database::connect();

// Fetch all persons
$sql = "SELECT * FROM iss_persons ORDER BY lname ASC";
$persons = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persons List - DSR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">Persons List</h2>

        <table class="table table-striped table-sm mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($persons as $person) : ?>
                    <tr>
                        <td><?= htmlspecialchars($person['id']); ?></td>
                        <td><?= htmlspecialchars($person['fname']); ?></td>
                        <td><?= htmlspecialchars($person['lname']); ?></td>
                        <td><?= htmlspecialchars($person['mobile']); ?></td>
                        <td><?= htmlspecialchars($person['email']); ?></td>
                        <td>
                            <!-- R, U, D Buttons -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readPerson<?= $person['id']; ?>">R</button>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updatePerson<?= $person['id']; ?>">U</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deletePerson<?= $person['id']; ?>">D</button>
                        </td>
                    </tr>

                    <!-- Read Modal -->
                    <div class="modal fade" id="readPerson<?= $person['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Person Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Name:</strong> <?= htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?></p>
                                    <p><strong>Mobile:</strong> <?= htmlspecialchars($person['mobile']); ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($person['email']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updatePerson<?= $person['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Person</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $person['id']; ?>">
                                        <input type="text" name="fname" class="form-control mb-2" value="<?= htmlspecialchars($person['fname']); ?>" required>
                                        <input type="text" name="lname" class="form-control mb-2" value="<?= htmlspecialchars($person['lname']); ?>" required>
                                        <input type="text" name="mobile" class="form-control mb-2" value="<?= htmlspecialchars($person['mobile']); ?>" required>
                                        <input type="email" name="email" class="form-control mb-2" value="<?= htmlspecialchars($person['email']); ?>" required>
                                        <button type="submit" name="update_person" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deletePerson<?= $person['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this person?</p>
                                    <p><strong>Name:</strong> <?= htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?></p>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $person['id']; ?>">
                                        <button type="submit" name="delete_person" class="btn btn-danger">Delete</button>
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