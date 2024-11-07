<?php
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>User Management</title>
</head>
<body>

    <div class="user-container mt-5">
        <div class="table-user">
            <button class="user-button" data-bs-toggle="modal" data-bs-target="#AddUser">Add Tricycle Operator</button>
            
            <div class="modal fade" id="AddUser" tabindex="-1" role="dialog" aria-labelledby="AddUserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tricycle Operator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="user-back.php">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label> Name: </label>
                            <input type="text" name="user_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label> Assign Location: </label>
                            <select class="form-control" name="location">
                                <option value="BRGY-Caingin">BRGY-Caingin</option>
                                <option value="BRGY-Ulingao">BRGY-Ulingao</option>
                                <option value="BRGY-Tambubong">BRGY-Tambubong</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label> Email: </label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label> Password: </label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label> Contact No: </label>
                            <input type="number" name="number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>



            <!-- Table part -->
            <div class="user-table mt-4">
                <div class="user-wrapper">
                    <table class="table">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Tricycle Operator ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Assigned Location</th>
                                <th scope="col">Tricycle ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Fetch users with the role 'Tricycle Operator' and their associated tricycle details
                            try {
                                $query = "
                                    SELECT u.user_id, u.name, t.location, t.tricycle_id 
                                    FROM user u
                                    JOIN tricycle t ON u.user_id = t.user_id
                                    WHERE u.role = 'Tricycle Operator'
                                ";
                                $stmt = $pdo->query($query);
                                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (PDOException $e) {
                                echo "Error: " . $e->getMessage();
                            } ?>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['location']); ?></td>
                                        <td><?php echo htmlspecialchars($user['tricycle_id']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No tricycle operators found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
