<?php
// Initialize variables
$name = $email = $password = $confirm_password = '';
$errors = [];
$success_message = '';

// Check if email already exists
function emailExists($email, $users) {
    foreach ($users as $user) {
        if (isset($user['email']) && $user['email'] === $email) {
            return true;
        }
    }
    return false;
}

// Validate form data
function validateForm($name, $email, $password, $confirm_password, $users) {
    $errors = [];
    
    // Validate name
    if (empty(trim($name))) {
        $errors['name'] = 'Name is required.';
    } elseif (strlen(trim($name)) < 2) {
        $errors['name'] = 'Name must be at least 2 characters long.';
    }
    
    // Validate email
    if (empty(trim($email))) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (emailExists($email, $users)) {
        $errors['email'] = 'This email address is already registered.';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one special character.';
    }
    
    // Validate confirm password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    return $errors;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Define JSON file path
    $json_file = __DIR__ . '/users.json';
    
    // Read existing users from JSON file
    $users = [];
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        if ($json_data !== false) {
            $users = json_decode($json_data, true);
            if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
                $errors['general'] = 'Error reading user data. Please try again later.';
                $users = []; // Reset to empty array if JSON is invalid
            }
        } else {
            $errors['general'] = 'Error reading user data file. Please try again later.';
        }
    }
    
    // If no file read errors, proceed with validation
    if (empty($errors['general'])) {
        // Validate form data
        $errors = validateForm($name, $email, $password, $confirm_password, $users);
        
        // If validation passes, save user
        if (empty($errors)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Create user array
            $new_user = [
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password,
                'registered_at' => date('Y-m-d H:i:s')
            ];
            
            // Add new user to users array
            $users[] = $new_user;
            
            // Encode array to JSON with pretty print for readability
            $json_data = json_encode($users, JSON_PRETTY_PRINT);
            
            // Write to JSON file
            if (file_put_contents($json_file, $json_data) !== false) {
                $success_message = 'Registration successful! Your account has been created.';
                // Clear form fields
                $name = $email = $password = $confirm_password = '';
            } else {
                $errors['general'] = 'Error saving user data. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: normal;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 14px;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .error {
            color: #d32f2f;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 15px;
            border: 1px solid #c8e6c9;
        }
        
        .error-general {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 15px;
            border: 1px solid #ffcdd2;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            cursor: pointer;
        }
        
        button[type="submit"]:hover {
            background: #357abd;
        }
        
        small {
            color: #666;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Registration</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['general'])): ?>
            <div class="error-general">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="name">Name:</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="<?php echo htmlspecialchars($name); ?>"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['name']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
                <small style="color: #777; font-size: 12px; display: block; margin-top: 5px;">
                    Password must be at least 8 characters and contain uppercase, lowercase, number, and special character.
                </small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                >
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['confirm_password']); ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>

