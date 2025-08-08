<?php
include('check-login.php');
function testEmailConfiguration() {
    // Check the file content without including it
    $reminderFunctionsPath = __DIR__ . '/reminder_functions.php';
    if (!file_exists($reminderFunctionsPath)) {
        return [
            'configured' => false,
            'message' => 'Email configuration file not found'
        ];
    }

    // Read file content to check for email configuration
    $content = file_get_contents($reminderFunctionsPath);

    // Look for the email_config array and its required keys
    $hasEmailConfig = preg_match('/\$email_config\s*=\s*\[/', $content);
    $hasSmtpHost = preg_match('/[\'"]smtp_host[\'"][^,]*=>[^,]*[\'"][^\'",]+[\'"]/', $content);
    $hasSmtpUser = preg_match('/[\'"]smtp_username[\'"][^,]*=>[^,]*[\'"][^\'",]+[\'"]/', $content);
    $hasSmtpPass = preg_match('/[\'"]smtp_password[\'"][^,]*=>[^,]*[\'"][^\'",]+[\'"]/', $content);

    if ($hasEmailConfig && $hasSmtpHost && $hasSmtpUser && $hasSmtpPass) {
        // Extract the SMTP host for display
        preg_match('/[\'"]smtp_host[\'"][^,]*=>[^,]*[\'"]([^\'",]+)[\'"]/', $content, $matches);
        $smtpHost = isset($matches[1]) ? $matches[1] : 'configured';

        return [
            'configured' => true,
            'message' => 'Configured ' . $smtpHost
        ];
    }

    return [
        'configured' => false,
        'message' => 'Email configuration incomplete - missing required SMTP settings'
    ];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle AJAX requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
    
        switch ($_POST['action']) {
            case 'add_reminder':
                $title = sanitize($_POST['title']);
                $description = sanitize($_POST['description']);
                $advance_notification = (int)$_POST['advance_notification'];
                $email_frequency = $_POST['email_frequency'];
                $priority = $_POST['priority'];
                $category = sanitize($_POST['category']);
                $reminder_type = $_POST['reminder_type'];
    
                try {
                    $dbh->beginTransaction();
    
                    if ($reminder_type === 'multiple_days') {
                        // Handle multiple days selection
                        $selected_dates = json_decode($_POST['selected_dates'], true);
                        $reminder_time = $_POST['reminder_time']; // This should be set by JavaScript
    
                        if (empty($selected_dates)) {
                            throw new Exception('Please select at least one date');
                        }
    
                        foreach ($selected_dates as $date) {
                            $stmt = $dbh->prepare('INSERT INTO reminders (title, description, reminder_date, reminder_time, advance_notification, email_frequency, priority, category, is_recurring) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)');
                            $stmt->execute([$title, $description, $date, $reminder_time, $advance_notification, $email_frequency, $priority, $category]);
                        }
    
                        $message = 'Reminder added for ' . count($selected_dates) . ' days!';
    
                    } elseif ($reminder_type === 'recurring') {
                        // Handle recurring reminders
                        $reminder_date = $_POST['reminder_date'];
                        $reminder_time = $_POST['reminder_time']; // This should be set by JavaScript
                        $recurring_days = (int)$_POST['recurring_days'];
                        $end_date = $_POST['end_date'] ?: null;
    
                        $stmt = $dbh->prepare('INSERT INTO reminders (title, description, reminder_date, reminder_time, is_recurring, recurring_days, end_date, advance_notification, email_frequency, priority, category) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$title, $description, $reminder_date, $reminder_time, $recurring_days, $end_date, $advance_notification, $email_frequency, $priority, $category]);
    
                        $reminder_id = $dbh->lastInsertId();
    
                        // Generate recurring instances
                        if ($recurring_days > 0) {
                            $current_date = new DateTime($reminder_date);
                            $end_date_obj = $end_date ? new DateTime($end_date) : new DateTime('+1 year');
    
                            while ($current_date <= $end_date_obj) {
                                $stmt = $dbh->prepare('INSERT INTO reminder_instances (reminder_id, instance_date, instance_time) VALUES (?, ?, ?)');
                                $stmt->execute([$reminder_id, $current_date->format('Y-m-d'), $reminder_time]);
                                $current_date->add(new DateInterval('P' . $recurring_days . 'D'));
                            }
                        }
    
                        $message = 'Recurring reminder added!';
    
                    } else {
                        // Handle single day reminder
                        $reminder_date = $_POST['reminder_date'];
                        $reminder_time = $_POST['reminder_time']; // This should be set by JavaScript from single_time field
    
                        $stmt = $dbh->prepare('INSERT INTO reminders (title, description, reminder_date, reminder_time, advance_notification, email_frequency, priority, category, is_recurring) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)');
                        $stmt->execute([$title, $description, $reminder_date, $reminder_time, $advance_notification, $email_frequency, $priority, $category]);
    
                        $message = 'Reminder added!';
                    }
    
                    $dbh->commit();
                    echo json_encode(['success' => true, 'message' => $message]);
    
                } catch (Exception $e) {
                    $dbh->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit;
    
            case 'complete_reminder':
                $id = (int)$_POST['id'];
                $stmt = $dbh->prepare('UPDATE reminders SET is_completed = 1 WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
                exit;
    
            case 'get_reminder':
                $id = (int)$_POST['id'];
                $stmt = $dbh->prepare('SELECT * FROM reminders WHERE id = ?');
                $stmt->execute([$id]);
                $reminder = $stmt->fetch();
    
                // If it's a recurring reminder, get related instances
                $instances = [];
                if ($reminder && $reminder['is_recurring']) {
                    $instanceStmt = $dbh->prepare('SELECT * FROM reminder_instances WHERE reminder_id = ? ORDER BY instance_date ASC');
                    $instanceStmt->execute([$id]);
                    $instances = $instanceStmt->fetchAll();
                }
    
                echo json_encode([
                    'success' => true,
                    'reminder' => $reminder,
                    'instances' => $instances
                ]);
                exit;
    
            case 'check_multiple_days':
                $title = sanitize($_POST['title']);
                $time = $_POST['time'];
                $category = sanitize($_POST['category']);
                $exclude_id = (int)$_POST['exclude_id'];
    
                $stmt = $dbh->prepare('SELECT COUNT(*) FROM reminders WHERE title = ? AND reminder_time = ? AND category = ? AND id != ? AND is_recurring = 0');
                $stmt->execute([$title, $time, $category, $exclude_id]);
                $count = $stmt->fetchColumn();
    
                echo json_encode(['success' => true, 'count' => $count]);
                exit;
    
            case 'update_reminder':
                $id = (int)$_POST['id'];
                $title = sanitize($_POST['title']);
                $description = sanitize($_POST['description']);
                $reminder_date = $_POST['reminder_date'];
                $reminder_time = $_POST['reminder_time'];
                $priority = $_POST['priority'];
                $category = sanitize($_POST['category']);
                $advance_notification = (int)$_POST['advance_notification'];
                $email_frequency = $_POST['email_frequency'];
    
                try {
                    $dbh->beginTransaction();
    
                    // First, get the current reminder to check its type
                    $stmt = $dbh->prepare('SELECT * FROM reminders WHERE id = ?');
                    $stmt->execute([$id]);
                    $current_reminder = $stmt->fetch();
    
                    if (!$current_reminder) {
                        throw new Exception('Reminder not found');
                    }
    
                    // Check if this is a recurring reminder
                    if ($current_reminder['is_recurring']) {
                        $dbh->rollBack();
                        echo json_encode([
                            'success' => false,
                            'message' => 'This is a recurring reminder. Please use the recurring reminder update function.',
                            'reminder_type' => 'recurring'
                        ]);
                        exit;
                    }
    
                    // Check if this is part of a multiple-day reminder set
                    $stmt = $dbh->prepare('
                SELECT COUNT(*) as count FROM reminders 
                WHERE title = ? 
                AND reminder_time = ? 
                AND is_recurring = 0 
                AND ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) <= 60
            ');
                    $stmt->execute([
                        $current_reminder['title'],
                        $current_reminder['reminder_time'],
                        $current_reminder['created_at']
                    ]);
                    $related_count = $stmt->fetchColumn();
    
                    if ($related_count > 1) {
                        $dbh->rollBack();
                        echo json_encode([
                            'success' => false,
                            'message' => 'This is part of a multiple-day reminder set. Please use the multiple-day reminder update function.',
                            'reminder_type' => 'multiple_days'
                        ]);
                        exit;
                    }
    
                    // This is a single reminder, proceed with update
                    $stmt = $dbh->prepare('
                UPDATE reminders 
                SET title = ?, description = ?, reminder_date = ?, reminder_time = ?, 
                    priority = ?, category = ?, advance_notification = ?, email_frequency = ?, 
                    updated_at = NOW() 
                WHERE id = ? AND is_recurring = 0
            ');
    
                    $result = $stmt->execute([
                        $title, $description, $reminder_date, $reminder_time,
                        $priority, $category, $advance_notification, $email_frequency, $id
                    ]);
    
                    if ($stmt->rowCount() === 0) {
                        throw new Exception('No reminder was updated. Please check if the reminder exists and is not recurring.');
                    }
    
                    $dbh->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Single reminder updated!',
                        'reminder_type' => 'single'
                    ]);
    
                } catch (Exception $e) {
                    $dbh->rollBack();
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
                exit;
    
            case 'update_recurring_reminder':
                $id = (int)$_POST['id'];
                $title = sanitize($_POST['title']);
                $description = sanitize($_POST['description']);
                $reminder_date = $_POST['reminder_date'] ?? $_POST['recurring_start_date']; // Handle both field names
                $reminder_time = $_POST['reminder_time'] ?? $_POST['recurring_time']; // Handle both field names
                $priority = $_POST['priority'];
                $category = sanitize($_POST['category']);
                $advance_notification = (int)$_POST['advance_notification'];
                $email_frequency = $_POST['email_frequency'];
                $recurring_days = (int)$_POST['recurring_days'];
                $end_date = $_POST['end_date'] ?: null;
    
                try {
                    $dbh->beginTransaction();
    
                    // Update the main recurring reminder
                    $stmt = $dbh->prepare('UPDATE reminders SET title = ?, description = ?, reminder_date = ?, reminder_time = ?, priority = ?, category = ?, advance_notification = ?, email_frequency = ?, recurring_days = ?, end_date = ? WHERE id = ?');
                    $stmt->execute([$title, $description, $reminder_date, $reminder_time, $priority, $category, $advance_notification, $email_frequency, $recurring_days, $end_date, $id]);
    
                    // Delete existing instances
                    $stmt = $dbh->prepare('DELETE FROM reminder_instances WHERE reminder_id = ?');
                    $stmt->execute([$id]);
    
                    // Regenerate instances with new settings
                    if ($recurring_days > 0) {
                        $current_date = new DateTime($reminder_date);
                        $end_date_obj = $end_date ? new DateTime($end_date) : new DateTime('+1 year');
    
                        while ($current_date <= $end_date_obj) {
                            $stmt = $dbh->prepare('INSERT INTO reminder_instances (reminder_id, instance_date, instance_time) VALUES (?, ?, ?)');
                            $stmt->execute([$id, $current_date->format('Y-m-d'), $reminder_time]);
                            $current_date->add(new DateInterval('P' . $recurring_days . 'D'));
                        }
                    }
    
                    $dbh->commit();
                    echo json_encode(['success' => true, 'message' => 'Recurring reminder updated!']);
                } catch (Exception $e) {
                    $dbh->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit;
    
            case 'update_multiple_days_reminder':
                $id = (int)$_POST['id'];
                $title = sanitize($_POST['title']);
                $description = sanitize($_POST['description']);
                $reminder_time = $_POST['reminder_time'];
                $priority = $_POST['priority'];
                $category = sanitize($_POST['category']);
                $advance_notification = (int)$_POST['advance_notification'];
                $email_frequency = $_POST['email_frequency'];
                $selected_dates = json_decode($_POST['selected_dates'], true);
    
                if (empty($selected_dates)) {
                    echo json_encode(['success' => false, 'message' => 'Please select at least one date']);
                    exit;
                }
    
                try {
                    $dbh->beginTransaction();
    
                    // Get all reminders that belong to this multiple-day group
                    // We'll use the original reminder's title and creation time to identify the group
                    $stmt = $dbh->prepare('SELECT * FROM reminders WHERE id = ?');
                    $stmt->execute([$id]);
                    $original_reminder = $stmt->fetch();
    
                    if (!$original_reminder) {
                        throw new Exception('Reminder not found');
                    }
    
                    // Find all reminders with the same title, time, and similar creation time (within 1 minute)
                    // This helps identify reminders that were created together as a multiple-day set
                    $stmt = $dbh->prepare('
                SELECT id FROM reminders 
                WHERE title = ? 
                AND reminder_time = ? 
                AND is_recurring = 0 
                AND ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) <= 60
            ');
                    $stmt->execute([
                        $original_reminder['title'],
                        $original_reminder['reminder_time'],
                        $original_reminder['created_at']
                    ]);
                    $related_reminders = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
                    // Delete all related reminders
                    if (!empty($related_reminders)) {
                        $placeholders = str_repeat('?,', count($related_reminders) - 1) . '?';
                        $stmt = $dbh->prepare("DELETE FROM reminders WHERE id IN ($placeholders)");
                        $stmt->execute($related_reminders);
                    }
    
                    // Create new reminders for selected dates
                    foreach ($selected_dates as $date) {
                        $stmt = $dbh->prepare('INSERT INTO reminders (title, description, reminder_date, reminder_time, advance_notification, email_frequency, priority, category, is_recurring, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())');
                        $stmt->execute([$title, $description, $date, $reminder_time, $advance_notification, $email_frequency, $priority, $category]);
                    }
    
                    $dbh->commit();
                    echo json_encode(['success' => true, 'message' => 'Multiple-day reminder updated!']);
                } catch (Exception $e) {
                    $dbh->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit;
    
            case 'get_reminder_details':
                $id = (int)$_POST['id'];
    
                try {
                    // Get the main reminder
                    $stmt = $dbh->prepare('SELECT * FROM reminders WHERE id = ?');
                    $stmt->execute([$id]);
                    $reminder = $stmt->fetch();
    
                    if (!$reminder) {
                        echo json_encode(['success' => false, 'message' => 'Reminder not found']);
                        exit;
                    }
    
                    $response = [
                        'success' => true,
                        'reminder' => $reminder,
                        'type' => 'single'
                    ];
    
                    // Check if it's a recurring reminder
                    if ($reminder['is_recurring']) {
                        $response['type'] = 'recurring';
    
                        // Get instances
                        $stmt = $dbh->prepare('SELECT * FROM reminder_instances WHERE reminder_id = ? ORDER BY instance_date ASC');
                        $stmt->execute([$id]);
                        $response['instances'] = $stmt->fetchAll();
                    } else {
                        // Check if it's part of a multiple-day reminder set
                        $stmt = $dbh->prepare('
                    SELECT id, reminder_date FROM reminders 
                    WHERE title = ? 
                    AND reminder_time = ? 
                    AND is_recurring = 0 
                    AND ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) <= 60
                    ORDER BY reminder_date ASC
                ');
                        $stmt->execute([
                            $reminder['title'],
                            $reminder['reminder_time'],
                            $reminder['created_at']
                        ]);
                        $related_reminders = $stmt->fetchAll();
    
                        if (count($related_reminders) > 1) {
                            $response['type'] = 'multiple_days';
                            $response['related_reminders'] = $related_reminders;
                            $response['selected_dates'] = array_column($related_reminders, 'reminder_date');
                        }
                    }
    
                    echo json_encode($response);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit;
    
            case 'check_multiple_days_reminder':
                $id = (int)$_POST['id'];
    
                try {
                    $stmt = $dbh->prepare('SELECT * FROM reminders WHERE id = ?');
                    $stmt->execute([$id]);
                    $reminder = $stmt->fetch();
    
                    if (!$reminder) {
                        echo json_encode(['success' => false, 'message' => 'Reminder not found']);
                        exit;
                    }
    
                    // Check for related reminders (multiple days)
                    $stmt = $dbh->prepare('
                SELECT id, reminder_date FROM reminders 
                WHERE title = ? 
                AND reminder_time = ? 
                AND is_recurring = 0 
                AND ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) <= 60
                ORDER BY reminder_date ASC
            ');
                    $stmt->execute([
                        $reminder['title'],
                        $reminder['reminder_time'],
                        $reminder['created_at']
                    ]);
                    $related_reminders = $stmt->fetchAll();
    
                    echo json_encode([
                        'success' => true,
                        'is_multiple_days' => count($related_reminders) > 1,
                        'related_reminders' => $related_reminders,
                        'selected_dates' => array_column($related_reminders, 'reminder_date')
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit;
    
            case 'dismiss_reminder':
                $id = (int)$_POST['id'];
                $stmt = $dbh->prepare('UPDATE reminders SET is_dismissed = 1 WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
                exit;
    
            case 'dismiss_reminder_instance':
                $instance_id = (int)$_POST['instance_id'];
                $stmt = $dbh->prepare('UPDATE reminder_instances SET is_dismissed = 1 WHERE id = ?');
                $stmt->execute([$instance_id]);
                echo json_encode(['success' => true, 'message' => 'Reminder instance dismissed!']);
                exit;
    
            case 'restore_reminder':
                $id = (int)$_POST['id'];
                $source_type = $_POST['source_type'] ?? 'reminder';
    
                if ($source_type === 'instance') {
                    $instance_id = (int)$_POST['instance_id'];
                    $stmt = $dbh->prepare('UPDATE reminder_instances SET is_dismissed = 0 WHERE id = ?');
                    $stmt->execute([$instance_id]);
                    $message = 'Reminder instance restored!';
                } else {
                    $stmt = $dbh->prepare('UPDATE reminders SET is_dismissed = 0 WHERE id = ?');
                    $stmt->execute([$id]);
                    $message = 'Reminder restored!';
                }
    
                echo json_encode(['success' => true, 'message' => $message]);
                exit;
    
            case 'complete_reminder_instance':
                $instance_id = (int)$_POST['instance_id'];
                $stmt = $dbh->prepare('UPDATE reminder_instances SET is_completed = 1 WHERE id = ?');
                $stmt->execute([$instance_id]);
                echo json_encode(['success' => true, 'message' => 'Reminder instance completed!']);
                exit;
    
            case 'delete_reminder':
                $id = (int)$_POST['id'];
                $stmt = $dbh->prepare('DELETE FROM reminders WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
                exit;
    
            case 'delete_reminder_instance':
                $instance_id = (int)$_POST['id'];
                $stmt = $dbh->prepare('DELETE FROM reminder_instances WHERE id = ?');
                $stmt->execute([$instance_id]);
                echo json_encode(['success' => true]);
                exit;

            case 'get_calendar_reminders':
                try {
                    // Get all reminders and instances for calendar display with category icons
                    $sql = "
            SELECT 
                r.id,
                r.title,
                r.description,
                r.reminder_date,
                r.reminder_time,
                r.priority,
                r.category,
                r.advance_notification,
                r.email_frequency,
                r.is_recurring,
                r.is_completed,
                r.is_dismissed,
                'reminder' as source_type,
                NULL as instance_id,
                NULL as reminder_id,
                rc.icon as category_icon,
                rc.color as category_color
            FROM reminders r
            LEFT JOIN reminder_categories rc ON r.category = rc.name
            WHERE r.is_recurring = 0
            
            UNION ALL
            
            SELECT 
                r.id as id,
                r.title,
                r.description,
                ri.instance_date as reminder_date,
                ri.instance_time as reminder_time,
                r.priority,
                r.category,
                r.advance_notification,
                r.email_frequency,
                r.is_recurring,
                ri.is_completed,
                ri.is_dismissed,
                'instance' as source_type,
                ri.id as instance_id,
                ri.reminder_id,
                rc.icon as category_icon,
                rc.color as category_color
            FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            LEFT JOIN reminder_categories rc ON r.category = rc.name
            
            ORDER BY reminder_date ASC, reminder_time ASC
        ";

                    $stmt = $dbh->prepare($sql);
                    $stmt->execute();
                    $reminders = $stmt->fetchAll();

                    echo json_encode([
                        'success' => true,
                        'reminders' => $reminders
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error fetching calendar reminders: ' . $e->getMessage()
                    ]);
                }
                exit;

            case 'bulk_delete_reminders':
                try {
                    $ids = json_decode($_POST['ids'], true);
                    if (empty($ids) || !is_array($ids)) {
                        throw new Exception('No reminders selected');
                    }

                    $dbh->beginTransaction();

                    // First check if these IDs exist in reminder_instances table
                    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                    $stmt = $dbh->prepare("SELECT id FROM reminder_instances WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $instance_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    // The remaining IDs should be from reminders table
                    $reminder_ids = array_diff($ids, $instance_ids);

                    // Delete recurring instances from reminder_instances table
                    if (!empty($instance_ids)) {
                        $instance_placeholders = str_repeat('?,', count($instance_ids) - 1) . '?';
                        $stmt = $dbh->prepare("DELETE FROM reminder_instances WHERE id IN ($instance_placeholders)");
                        $stmt->execute($instance_ids);
                    }

                    // Delete non-recurring reminders from reminders table
                    if (!empty($reminder_ids)) {
                        $reminder_placeholders = str_repeat('?,', count($reminder_ids) - 1) . '?';
                        $stmt = $dbh->prepare("DELETE FROM reminders WHERE id IN ($reminder_placeholders)");
                        $stmt->execute($reminder_ids);
                    }

                    $dbh->commit();

                    echo json_encode([
                        'success' => true,
                        'message' => count($ids) . ' reminder(s) deleted'
                    ]);
                } catch (Exception $e) {
                    $dbh->rollBack();
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
                exit;

            case 'save_reminder_settings':
                try {
                    $settings = [
                        'morning_summary_enabled' => $_POST['morning_summary_enabled'],
                        'evening_progress_enabled' => $_POST['evening_progress_enabled'],
                        'due_reminders_enabled' => $_POST['due_reminders_enabled'],
                        'send_empty_summaries' => $_POST['send_empty_summaries'],
                        'notification_email' => sanitize($_POST['notification_email']),
                        'email_format' => $_POST['email_format']
                    ];

                    foreach ($settings as $key => $value) {
                        $stmt = $dbh->prepare('INSERT INTO reminder_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
                        $stmt->execute([$key, $value, $value]);
                    }

                    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error saving settings: ' . $e->getMessage()]);
                }
                exit;

            case 'get_reminder_settings':
                try {
                    $stmt = $dbh->query('SELECT setting_key, setting_value FROM reminder_settings');
                    $settings = [];
                    while ($row = $stmt->fetch()) {
                        $settings[$row['setting_key']] = $row['setting_value'];
                    }

                    echo json_encode(['success' => true, 'settings' => $settings]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error loading settings']);
                }
                exit;

            case 'reset_reminder_settings':
                try {
                    $defaultSettings = [
                        'morning_summary_enabled' => '1',
                        'evening_progress_enabled' => '1',
                        'due_reminders_enabled' => '1',
                        'send_empty_summaries' => '1',
                        'notification_email' => 'bryo4419@gmail.com',
                        'email_format' => 'html'
                    ];

                    foreach ($defaultSettings as $key => $value) {
                        $stmt = $dbh->prepare('INSERT INTO reminder_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
                        $stmt->execute([$key, $value, $value]);
                    }

                    echo json_encode(['success' => true, 'message' => 'Settings reset to defaults']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error resetting settings']);
                }
                exit;

            case 'get_system_status':
                try {
                    $status = [];

                    // Check database connection
                    $status['database'] = [
                        'status' => 'online',
                        'message' => 'Active',
                        'last_check' => date('Y-m-d H:i:s')
                    ];

                    // Check email service configuration without including the file
                    $emailTest = testEmailConfiguration();
                    $status['email_service'] = [
                        'status' => $emailTest['configured'] ? 'configured' : 'warning',
                        'message' => $emailTest['message'],
                        'last_check' => date('Y-m-d H:i:s')
                    ];

                    // Get last email sent information from reminders table
                    $stmt = $dbh->prepare("
            SELECT last_email_sent, title
            FROM reminders
            WHERE last_email_sent IS NOT NULL
            ORDER BY last_email_sent DESC
            LIMIT 1
        ");
                    $stmt->execute();
                    $lastEmail = $stmt->fetch();

                    if ($lastEmail) {
                        $status['last_email'] = [
                            'status' => 'success',
                            'message' => 'Last email sent for: ' . htmlspecialchars($lastEmail['title']),
                            'timestamp' => $lastEmail['last_email_sent']
                        ];
                    } else {
                        $status['last_email'] = [
                            'status' => 'warning',
                            'message' => 'No emails sent yet',
                            'timestamp' => null
                        ];
                    }

                    // Get active reminders count
                    $stmt = $dbh->prepare("
            SELECT COUNT(*) as count FROM (
                SELECT id FROM reminders
                WHERE is_recurring = 0 AND is_completed = 0 AND is_dismissed = 0
                UNION ALL
                SELECT ri.id FROM reminder_instances ri
                JOIN reminders r ON ri.reminder_id = r.id
                WHERE ri.is_completed = 0 AND ri.is_dismissed = 0
            ) as active_count
        ");
                    $stmt->execute();
                    $activeCount = $stmt->fetchColumn();

                    $status['active_reminders'] = [
                        'count' => $activeCount,
                        'message' => $activeCount > 0 ? "$activeCount active reminders" : "No active reminders"
                    ];

                    // Calculate system health score
                    $healthScore = 100;
                    $healthMessage = 'System running optimally';

                    if (!$emailTest['configured']) {
                        $healthScore -= 20;
                        $healthMessage = 'Email configuration needs attention';
                    }

                    if ($activeCount === 0) {
                        $healthScore -= 10;
                    }

                    if (!$lastEmail) {
                        $healthScore -= 15;
                    }

                    $status['system_health'] = [
                        'score' => $healthScore,
                        'status' => $healthScore >= 75 ? 'good' : ($healthScore >= 50 ? 'warning' : 'critical'),
                        'message' => $healthMessage
                    ];

                    echo json_encode(['success' => true, 'status' => $status]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error getting system status: ' . $e->getMessage()]);
                }
                exit;

            case 'test_email_service':
                try {
                    $emailTest = testEmailConfiguration();

                    echo json_encode([
                        'success' => true,
                        'status' => $emailTest['configured'] ? 'working' : 'failed',
                        'message' => $emailTest['message']
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Email service test failed: ' . $e->getMessage()
                    ]);
                }
                exit;
        }
    }
    
    // Get reminders
    $filter = $_GET['filter'] ?? 'today';
    $category = $_GET['category'] ?? '';
    
    // Build where conditions for main query (combining filter and category)
    $where_conditions = ['1=1'];
    $instance_where_conditions = ['1=1'];
    $params = [];
    
    // Add category filtering
    if (!empty($category)) {
        $where_conditions[] = "category = :category";
        $instance_where_conditions[] = "r.category = :category";
        $params['category'] = $category;
    }
    
    // Add filter conditions
    switch ($filter) {
        case 'today':
            $where_conditions[] = 'reminder_date = CURDATE() AND is_completed = 0 AND is_dismissed = 0';
            $instance_where_conditions[] = 'ri.instance_date = CURDATE() AND ri.is_completed = 0 AND ri.is_dismissed = 0';
            break;
        case 'upcoming':
            $where_conditions[] = 'reminder_date >= CURDATE() AND is_completed = 0 AND is_dismissed = 0';
            $instance_where_conditions[] = 'ri.instance_date >= CURDATE() AND ri.is_completed = 0 AND ri.is_dismissed = 0';
            break;
        case 'overdue':
            $where_conditions[] = 'CONCAT(reminder_date, " ", reminder_time) <= NOW() AND is_completed = 0 AND is_dismissed = 0';
            $instance_where_conditions[] = 'CONCAT(ri.instance_date, " ", ri.instance_time) <= NOW() AND ri.is_completed = 0 AND ri.is_dismissed = 0';
            break;
        case 'completed':
            $where_conditions[] = 'is_completed = 1 AND is_dismissed = 0';
            $instance_where_conditions[] = 'ri.is_completed = 1 AND ri.is_dismissed = 0';
            break;
        case 'dismissed':
            $where_conditions[] = 'is_dismissed = 1';
            $instance_where_conditions[] = '(ri.is_dismissed = 1)';
            break;
        case 'all':
        default:
            $where_conditions[] = 'is_completed = 0';
            $instance_where_conditions[] = 'ri.is_completed = 0';
            break;
    }
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = isset($_GET['per_page']) ? max(12, min(100, (int)$_GET['per_page'])) : 12; // Default 12, min 12, max 100
    $offset = ($page - 1) * $per_page;
    
    // Get total count (including instances)
    $count_sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders WHERE is_recurring = 0 AND " . implode(' AND ', $where_conditions) . "
            UNION ALL
            SELECT ri.id 
            FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE " . implode(' AND ', $instance_where_conditions) . "
        ) as combined_count
    ";
    
    $count_stmt = $dbh->prepare($count_sql);
    $count_stmt->execute($params);
    $total_reminders = $count_stmt->fetchColumn();
    $total_pages = ceil($total_reminders / $per_page);
    
    // Get combined reminders and instances
    $sql = "
        SELECT 
            id,
            title,
            description,
            reminder_date,
            reminder_time,
            advance_notification,
            email_frequency,
            priority,
            category,
            is_recurring,
            recurring_days,
            end_date,
            is_completed,
            is_dismissed,
            created_at,
            'reminder' as source_type,
            NULL as instance_id,
            NULL as reminder_id
        FROM reminders 
        WHERE is_recurring = 0 AND " . implode(' AND ', $where_conditions) . "
        
        UNION ALL
        
        SELECT 
            r.id as id,
            r.title,
            r.description,
            ri.instance_date as reminder_date,
            ri.instance_time as reminder_time,
            r.advance_notification,
            r.email_frequency,
            r.priority,
            r.category,
            r.is_recurring,
            r.recurring_days,
            r.end_date,
            ri.is_completed,
            ri.is_dismissed,
            r.created_at,
            'instance' as source_type,
            ri.id as instance_id,
            ri.reminder_id
        FROM reminder_instances ri 
        JOIN reminders r ON ri.reminder_id = r.id 
        WHERE " . implode(' AND ', $instance_where_conditions) . "
        
        ORDER BY reminder_date ASC, reminder_time ASC 
        LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);
    $reminders = $stmt->fetchAll();
    
    // Update statistics to include instances with category filtering
    $category_condition = !empty($category) ? " AND category = ?" : "";
    $category_condition_instances = !empty($category) ? " AND r.category = ?" : "";
    
    $stats = [];
    
    // Total count
    $sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders WHERE is_recurring = 0 AND is_completed = 0" . $category_condition . "
            UNION ALL
            SELECT ri.id FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE ri.is_dismissed = 0" . $category_condition_instances . "
        ) as total_count
    ";
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) {
        $stmt->execute([$category, $category]);
    } else {
        $stmt->execute();
    }
    $stats['total'] = $stmt->fetchColumn();
    
    // Today count
    $sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders WHERE is_recurring = 0 AND is_completed = 0 AND reminder_date = CURDATE() AND is_dismissed = 0" . $category_condition . "
            UNION ALL
            SELECT ri.id FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE ri.instance_date = CURDATE() AND ri.is_dismissed = 0" . $category_condition_instances . "
        ) as today_count
    ";
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) {
        $stmt->execute([$category, $category]);
    } else {
        $stmt->execute();
    }
    $stats['today'] = $stmt->fetchColumn();
    
    // Overdue count
    $sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders 
            WHERE is_recurring = 0 
            AND CONCAT(reminder_date, ' ', reminder_time) <= NOW() 
            AND is_completed = 0 
            AND is_dismissed = 0" . $category_condition . "
            UNION ALL
            SELECT ri.id FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE CONCAT(ri.instance_date, ' ', ri.instance_time) <= NOW() 
            AND ri.is_completed = 0 
            AND ri.is_dismissed = 0" . $category_condition_instances . "
        ) as overdue_count
    ";
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) {
        $stmt->execute([$category, $category]);
    } else {
        $stmt->execute();
    }
    $stats['overdue'] = $stmt->fetchColumn();
    
    // Upcoming count
    $sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders 
            WHERE is_recurring = 0 
            AND reminder_date >= CURDATE() 
              AND is_completed = 0
            AND is_dismissed = 0" . $category_condition . "
            UNION ALL
            SELECT ri.id FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE ri.instance_date > CURDATE()
            AND ri.is_completed = 0 AND ri.is_dismissed = 0" . $category_condition_instances . "
        ) as upcoming_count
    ";
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) {
        $stmt->execute([$category, $category]);
    } else {
        $stmt->execute();
    }
    $stats['upcoming'] = $stmt->fetchColumn();
    
    // Dismissed count
    $sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders WHERE is_dismissed = 1" . $category_condition . "
            UNION ALL
            SELECT ri.id FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE ri.is_dismissed = 1" . $category_condition_instances . "
        ) as dismissed_count
    ";
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) {
        $stmt->execute([$category, $category]);
    } else {
        $stmt->execute();
    }
    $stats['dismissed'] = $stmt->fetchColumn();
    
    // Completed count
    $sql = "
        SELECT COUNT(*) FROM (
            SELECT id FROM reminders WHERE is_recurring = 0 AND is_completed = 1" . $category_condition . "
            UNION ALL
            SELECT ri.id FROM reminder_instances ri 
            JOIN reminders r ON ri.reminder_id = r.id 
            WHERE ri.is_completed = 1" . $category_condition_instances . "
        ) as completed_count
    ";
    $stmt = $dbh->prepare($sql);
    if (!empty($category)) {
        $stmt->execute([$category, $category]);
    } else {
        $stmt->execute();
    }
    $stats['completed'] = $stmt->fetchColumn();
    
    // Get categories
    $categories = $dbh->query("SELECT DISTINCT category FROM reminders WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);


// Get categories
if ($action === 'get_categories') {
    try {
        $stmt = $dbh->query("SELECT * FROM reminder_categories ORDER BY is_default DESC, name ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Add category
if ($action === 'add_category') {
    try {
        $name = trim($_POST['name']);
        $icon = $_POST['icon'];
        $color = $_POST['color'];

        // Validate inputs
        if (empty($name)) {
            throw new Exception('Category name is required');
        }

        // Check if category already exists
        $stmt = $dbh->prepare("SELECT id FROM reminder_categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            throw new Exception('Category already exists');
        }

        // Insert new category
        $stmt = $dbh->prepare("INSERT INTO reminder_categories (name, icon, color) VALUES (?, ?, ?)");
        $stmt->execute([$name, $icon, $color]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Update category
if ($action === 'update_category') {
    try {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $icon = $_POST['icon'];
        $color = $_POST['color'];

        // Validate inputs
        if (empty($name)) {
            throw new Exception('Category name is required');
        }

        // Check if category name already exists (excluding current category)
        $stmt = $dbh->prepare("SELECT id FROM reminder_categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Category name already exists');
        }

        // Update category
        $stmt = $dbh->prepare("UPDATE reminder_categories SET name = ?, icon = ?, color = ? WHERE id = ?");
        $stmt->execute([$name, $icon, $color, $id]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Delete category
if ($action === 'delete_category') {
    try {
        $id = $_POST['id'];

        // Check if category is default
        $stmt = $dbh->prepare("SELECT is_default FROM reminder_categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();

        if ($category && $category['is_default']) {
            throw new Exception('Cannot delete default categories');
        }

        // Check if category is being used
        $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM reminders WHERE category = (SELECT name FROM reminder_categories WHERE id = ?)");
        $stmt->execute([$id]);
        $usage = $stmt->fetch();

        if ($usage['count'] > 0) {
            throw new Exception('Cannot delete category that is being used by reminders');
        }

        // Delete category
        $stmt = $dbh->prepare("DELETE FROM reminder_categories WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

?>
<?php
// Check if 'session' exists before using it
if (isset($_SESSION['odmsaid'])) {
    $aid = $_SESSION['odmsaid'];
} else {
    header('Location: login.php');
    exit();
}


// Fetch userID from the database using the email stored in the session
$userQuery = mysqli_query($con, "SELECT id FROM tbladmin WHERE email = '$aid'");
$userResult = mysqli_fetch_assoc($userQuery);
$userID = $userResult['id']; // Get the userID

// Query to fetch unread messages details by userID
$unreadMessagesQuery = mysqli_query($con, "SELECT * FROM chat_messages WHERE is_read = 0 AND receiver_id = '$userID' ORDER BY timestamp ASC");

$unreadMessages = []; // Initialize array to hold unread messages data
while ($message = mysqli_fetch_assoc($unreadMessagesQuery)) {
    $unreadMessages[] = $message; // Add each unread message to the array
}

$unreadMessagesCount = count($unreadMessages); // Count the number of unread messages
?>
    <!DOCTYPE html>
    <html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reminders</title>
<?php include 'navi.php'; ?>
    <!-- Header -->
    <div class='card shadow-none border mb-3'>
        <div class='bg-holder bg-card d-none d-md-block'
             style='background-image:url(../assets/img/illustrations/corner-6.png);'>
        </div>
        <!--/.bg-holder-->

        <div class='card-header z-1'>
            <div class='row flex-between-center gx-0'>
                <div class='col-lg-auto d-flex align-items-center'>
                    <h4 class='mb-0 text-primary fw-bold'>My<span class='text-info fw-medium'> Reminders</span>
                    </h4>
                </div>
                <div class='col-lg-auto pt-3 pt-lg-0'>
                    <form class="$rowTask flex-lg-column flex-xxl-$rowTask gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                        <div class='col-auto'>
                        </div>
                        <div class='col-md-auto position-relative'>
                            <h6 class='mb-1 badge rounded-pill badge-subtle-info'><?php echo date('jS F Y'); ?> |
                                <span id="timeDisplay"></span></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card mb-3">
        <div class="card-body p-0">
            <nav class="nav nav-tabs border-0" id="reminderTabs" role="tablist">
                <a class="nav-link active px-4 py-3" id="list-tab" data-bs-toggle="tab" href="#list-view" role="tab" aria-controls="list-view" aria-selected="true">
                    <i class="fas fa-list me-2"></i>List View
                </a>
                <a class="nav-link px-4 py-3" id="calendar-tab" data-bs-toggle="tab" href="#calendar-view" role="tab" aria-controls="calendar-view" aria-selected="false">
                    <i class="fas fa-calendar-alt me-2"></i>Calendar View
                </a>
                <a class="nav-link px-4 py-3" id="settings-tab" data-bs-toggle="tab" href="#settings-view" role="tab" aria-controls="settings-view" aria-selected="false">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
                <a class="nav-link px-4 py-3" id="status-tab" data-bs-toggle="tab" href="#status-view" role="tab" aria-controls="status-view" aria-selected="false" onclick="isStatusTabActive = true; loadSystemStatus();">
                    <i class="fas fa-server me-2"></i>System Status
                </a>
            </nav>
        </div>
    </div>


    <!-- Main Content -->
    <div class="col mb-3">
    <!-- Tab Content -->
    <div class="tab-content" id="reminderTabContent">
    <!-- List View Tab -->
    <div class="tab-pane fade show active" id="list-view" role="tabpanel" aria-labelledby="list-tab">
    <!-- Filters Row -->
        <div class='card mb-2'>
            <div class='card-header'>
                <div class='d-flex justify-content-between align-items-center mb-3'>
                    <button class='btn btn-outline-primary' data-bs-toggle='modal' data-bs-target='#addReminderModal'>
                        <i class='fas fa-plus'></i> Add Reminder
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                        <i class="fas fa-tags me-2"></i>Manage Categories
                    </button>
                </div>

                <!-- Statistics Cards -->
                <div class='row mb-3 g-3'>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="?filter=today<?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="text-decoration-none">
                            <div class="card stat-card h-100 <?= $filter === 'today' ? 'border-info bg-info bg-opacity-10' : 'bg-body-tertiary' ?> shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-day fa-2x text-info"></i>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-info"><?= $stats['today'] ?></h4>
                                    <small class="text-muted fw-medium">Today</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class='col-md-2 col-sm-4 col-6'>
                        <a href="?filter=upcoming<?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="text-decoration-none">
                            <div class='card stat-card h-100 <?= $filter === 'upcoming' ? 'border-primary bg-primary bg-opacity-10' : 'bg-body-tertiary' ?> shadow-sm'>
                                <div class='card-body text-center p-3'>
                                    <div class="mb-2">
                                        <i class="fas fa-list fa-2x text-primary"></i>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-primary"><?= $stats['upcoming'] ?></h4>
                                    <small class="text-muted fw-medium">Upcoming</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="?filter=overdue<?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="text-decoration-none">
                            <div class="card stat-card h-100 <?= $filter === 'overdue' ? 'border-danger bg-danger bg-opacity-10' : 'bg-body-tertiary' ?> shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-danger"><?= $stats['overdue'] ?></h4>
                                    <small class="text-muted fw-medium">Overdue</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="?filter=completed<?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="text-decoration-none">
                            <div class="card stat-card h-100 <?= $filter === 'completed' ? 'border-success bg-success bg-opacity-10' : 'bg-body-tertiary' ?> shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-success"><?= $stats['completed'] ?></h4>
                                    <small class="text-muted fw-medium">Done</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="?filter=dismissed<?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="text-decoration-none">
                            <div class="card stat-card h-100 <?= $filter === 'dismissed' ? 'border-warning bg-warning bg-opacity-10' : 'bg-body-tertiary' ?> shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-eye-slash fa-2x text-warning"></i>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-warning"><?= $stats['dismissed'] ?></h4>
                                    <small class="text-muted fw-medium">Dismissed</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="?filter=all<?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="text-decoration-none">
                            <div class="card stat-card h-100 <?= $filter === 'all' ? 'border-secondary bg-secondary bg-opacity-10' : 'bg-body-tertiary' ?> shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-alt fa-2x text-secondary"></i>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-secondary"><?= $stats['total'] ?></h4>
                                    <small class="text-muted fw-medium">Total</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="row mb-3">
                    <div class="col-12 col-lg-4 ms-auto">
                        <?php if (!empty($categories)): ?>
                            <select class="form-select" onchange="window.location.href='?filter=<?= urlencode($filter) ?>&category=' + encodeURIComponent(this.value)">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                        <?= ucfirst(htmlspecialchars($cat)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <div class="card mb-3">
        <div class="card-header">
            <!-- Per Page and Select All Controls -->
            <div class="d-flex justify-content-between align-items-center gap-3">
                <!-- Per Page Dropdown -->
                <div class="d-flex align-items-center">
                    <label class="form-label me-2 mb-0">Show:</label>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="12" <?= $per_page == 12 ? 'selected' : '' ?>>12</option>
                        <option value="24" <?= $per_page == 24 ? 'selected' : '' ?>>24</option>
                        <option value="36" <?= $per_page == 36 ? 'selected' : '' ?>>36</option>
                        <option value="48" <?= $per_page == 48 ? 'selected' : '' ?>>48</option>
                        <option value="60" <?= $per_page == 60 ? 'selected' : '' ?>>60</option>
                        <option value="100" <?= $per_page == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <!-- Select All Checkbox -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllReminders">
                    <label class="form-check-label" for="selectAllReminders">
                        Select All
                    </label>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-2">
                <!-- Bulk Actions -->
                <div class="bulk-actions" style="display: none;">
                    <button class="btn btn-outline-danger" id="bulkDeleteBtn">
                        <i class="fas fa-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                    <button class="btn btn-outline-secondary" id="clearSelectionBtn">
                        <i class="fas fa-times me-1"></i>Clear Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4" id="reminders-container">
        <?php if (empty($reminders)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-bell-slash fa-2x text-muted"></i>
                        </div>
                    </div>
                    <h5 class="text-muted fw-light mb-2">No reminders found</h5>
                    <p class="text-muted small mb-4">Create your first reminder to get started!</p>
                    <button class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                        <i class="fas fa-plus me-2"></i>Add Your First Reminder
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($reminders as $reminder):
                $datetime = new DateTime($reminder['reminder_date'] . ' ' . $reminder['reminder_time']);
                $now = new DateTime();
                $is_overdue = $datetime < $now && !$reminder['is_completed'];
                $is_due_soon = $datetime->diff($now)->days == 0 && $datetime > $now;

                // Enhanced card styling
                $card_class = 'border-0 shadow-sm';
                $accent_color = 'primary';
                $status_icon = 'fas fa-clock';
                $status_text = 'Upcoming';
                $status_bg = 'bg-primary';

                if ($reminder['is_completed']) {
                    $accent_color = 'success';
                    $status_icon = 'fas fa-check-circle';
                    $status_text = 'Completed';
                    $status_bg = 'bg-success';
                } elseif ($is_overdue) {
                    $accent_color = 'danger';
                    $status_icon = 'fas fa-exclamation-triangle';
                    $status_text = 'Overdue';
                    $status_bg = 'bg-danger';
                } elseif ($is_due_soon) {
                    $accent_color = 'warning';
                    $status_icon = 'fas fa-bell';
                    $status_text = 'Due Soon';
                    $status_bg = 'bg-warning';
                }
                ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card bg h-100 <?= $card_class ?> hover-shadow-lg transition-all position-relative overflow-hidden <?= $reminder['is_completed'] ? 'opacity-85' : '' ?>">

                        <!-- Selection Checkbox -->
                        <div class="position-absolute" style="top: 1px; left: 1px; z-index: 2;">
                            <input type="checkbox" class="form-check-input reminder-checkbox" value="<?= $reminder['id'] ?>" style="transform: scale(1.2);">
                        </div>
                        <!-- Accent Bar -->
                        <div class="position-absolute top-0 start-0 w-100 <?= $status_bg ?>" style="height: 4px;"></div>
                        <!-- Card Header -->
                        <div class="card-header border-0 pb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <h5 class="card-title mb-2 fw-bold lh-sm <?= $reminder['is_completed'] ? 'text-decoration-line-through text-muted' : 'text-900' ?>">
                                        <?= htmlspecialchars($reminder['title']) ?>
                                    </h5>

                                    <!-- Priority and Category Badges -->
                                    <div class="d-flex flex-wrap gap-1 mb-0">
                                    <span class="badge rounded-pill badge-subtle-<?= $reminder['priority'] === 'high' ? 'danger' : ($reminder['priority'] === 'medium' ? 'warning' : 'secondary') ?> bg-opacity-15 text-<?= $reminder['priority'] === 'high' ? 'danger' : ($reminder['priority'] === 'medium' ? 'warning' : 'secondary') ?> border-0 px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-flag me-1"></i><?= ucfirst($reminder['priority']) ?>
                                    </span>
                                        <?php
                                        $categoryStmt = $dbh->query("SELECT name, icon, color FROM reminder_categories");
                                        $categoryData = [];
                                        while ($cat = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
                                            $categoryData[$cat['name']] = [
                                                'icon' => $cat['icon'],
                                                'color' => $cat['color']
                                            ];
                                        }
                                        $catIcon = $categoryData[$reminder['category']]['icon'] ?? 'fas fa-bell';
                                        $catColor = $categoryData[$reminder['category']]['color'] ?? '#007bff';
                                        ?>
                                        <span class="badge border-0"
                                              style="background-color: <?= $catColor ?>; color: white;"
                                              title="Category: <?= ucfirst($reminder['category']) ?>">
                                                <i class="<?= $catIcon ?> me-1"></i><?= ucfirst($reminder['category']) ?>
                                        </span>
                                        <?php if ($reminder['is_recurring']): ?>
                                            <span class="badge rounded-pill badge-subtle-info bg-opacity-15 text-info border-0 px-2 py-1" style="font-size: 0.7rem;" title="Repeats every <?= $reminder['recurring_days'] ?> day(s)">
                                            <i class="fas fa-redo me-1"></i>Recurring
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="text-end">
                                <span class="badge <?= $status_bg ?>-subtle bg-opacity-15 badge-subtle-<?= $accent_color ?> border-0 px-2 py-1 mb-2" style="font-size: 0.7rem;">
                                    <i class="<?= $status_icon ?> me-1"></i>
                                </span>

                                    <!-- Dropdown Menu -->
                                    <div class="dropdown d-block">
                                        <button class="btn btn-sm rounded-circle p-1" type="button" data-bs-toggle="dropdown" style="width: 28px; height: 28px;" title="More actions">
                                            <i class="fas fa-ellipsis-v" style="font-size: 0.7rem;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                            <?php if ($filter === 'dismissed'): ?>
                                                <li><a class="dropdown-item text-success rounded-2" href="#" onclick="restoreReminder(<?= $reminder['id'] ?>, '<?= $reminder['source_type'] ?? 'reminder' ?>', <?= $reminder['instance_id'] ?? 'null' ?>)">
                                                        <i class="fas fa-undo me-2"></i>Restore
                                                    </a></li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                            <?php else: ?>
                                                <?php if (!$reminder['is_completed']): ?>
                                                    <li><a class="dropdown-item text-success rounded-2" href="#" onclick="<?= $reminder['source_type'] === 'instance' ? 'completeReminderInstance(' . $reminder['instance_id'] . ')' : 'completeReminder(' . $reminder['id'] . ')' ?>">
                                                            <i class="fas fa-check me-2"></i>Mark Complete
                                                        </a></li>
                                                    <li><a class="dropdown-item text-primary rounded-2" href="#" onclick="editReminder(<?= $reminder['source_type'] === 'instance' ? $reminder['reminder_id'] : (is_numeric($reminder['id']) ? $reminder['id'] : "'" . $reminder['id'] . "'") ?>)">
                                                            <i class="fas fa-edit me-2"></i>Edit
                                                        </a></li>
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                <?php endif; ?>
                                                <li><a class="dropdown-item text-warning rounded-2" href="#" onclick="<?= $reminder['source_type'] === 'instance' && !empty($reminder['instance_id']) ? 'dismissReminderInstance(' . $reminder['instance_id'] . ')' : 'dismissReminder(' . $reminder['id'] . ')' ?>">
                                                        <i class="fas fa-eye-slash me-2"></i>Dismiss
                                                    </a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item text-danger rounded-2" href="#" onclick="deleteReminder(<?= $reminder['id'] ?>)">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body py-3">
                            <?php if ($reminder['description']): ?>
                                <p class="fw-semibold text-900 mb-1"><?= htmlspecialchars($reminder['description']) ?></p>
                            <?php endif; ?>

                            <!-- Date and Time with improved styling -->
                            <div class="d-flex align-items-center mb-1 p-3 bg-opacity-50 rounded-3">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1" style="font-size: 0.9rem;">
                                        <?= date('l, F j, Y', strtotime($reminder['reminder_date'])) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i><?= date('g:i A', strtotime($reminder['reminder_time'])) ?>
                                    </div>
                                    <!-- Time indicator and status badges -->
                                    <div class="d-flex gap-2 mt-2">
                                        <?php if ($reminder['is_dismissed'] == 1): ?>
                                            <span class="badge badge-subtle-secondary border-0 px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="fas fa-eye-slash me-1"></i>Dismissed
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($is_overdue): ?>
                                            <span class="badge badge-subtle-danger bg-opacity-15 text-danger border-0 px-2 py-1" style="font-size: 0.7rem;">
                                                <?php
                                                $diff = $now->diff($datetime);
                                                echo $diff->days > 0 ? $diff->days . ' days ago' : 'Today';
                                                ?>
                                            </span>
                                        <?php elseif ($is_due_soon): ?>
                                            <span class="badge badge-subtle-success rounded-pill fs-11">
                                                <?php
                                                $diff = $datetime->diff($now);
                                                $hours = $diff->h + ($diff->days * 24);
                                                echo $hours > 0 ? "Due in {$hours}h {$diff->i}m" : "In {$diff->i}m";
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Footer -->
                        <div class="card-footer border-0 pt-0 pb-3">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <!-- Email Notification -->
                                <?php if ($reminder['email_frequency'] !== 'none'): ?>
                                    <span class="badge badge-subtle-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.7rem;" title="Email: <?= ucfirst($reminder['email_frequency']) ?>">
                                    <i class="fas fa-envelope me-1"></i><?= ucfirst($reminder['email_frequency']) ?>
                                </span>
                                <?php endif; ?>

                                <!-- Advance Notification -->
                                <?php if ($reminder['advance_notification'] > 0): ?>
                                    <span class="badge badge-subtle-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.7rem;" title="Advance notification">
                                    <i class="fas fa-bell me-1"></i>
                                    <?php
                                    $minutes = $reminder['advance_notification'];
                                    echo $minutes >= 60 ? floor($minutes / 60) . 'h' . ($minutes % 60 > 0 ? ' ' . ($minutes % 60) . 'm' : '') : $minutes . 'm';
                                    ?> before
                                </span>
                                <?php endif; ?>

                                <!-- No notifications indicator -->
                                <?php if ($reminder['email_frequency'] === 'none' && $reminder['advance_notification'] == 0): ?>
                                    <span class="badge badge-subtle-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                    <i class="fas fa-bell-slash me-1"></i>No alerts
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Reminders pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>"><?= $total_pages ?></a>
                    </li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="text-center mt-2">
                <small class="text-muted">
                    Showing <?= min($offset + 1, $total_reminders) ?> to <?= min($offset + $per_page, $total_reminders) ?> of <?= $total_reminders ?> reminders
                </small>
            </div>
        </nav>
    <?php endif; ?>
    </div>
        <!-- Calendar View Tab -->
        <div class="tab-pane fade" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
            <div class="card shadow-none border mb-3">
                <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);"></div>
                <div class="card-header z-1">
                    <div class="row flex-between-center gx-0">
                        <div class="col-lg-auto d-flex align-items-center">
                            <h4 class="mb-0 text-primary fw-bold">Reminders <span class="text-info fw-medium">Calendar</span></h4>
                        </div>
                        <div class="col-md-auto p-3">
                            <form class="row align-items-center g-3">
                                <div class="col-md-auto position-relative">
                                    <div class="dropdown font-sans-serif me-md-2">
                                        <button class="btn btn-falcon-default text-600 btn-sm dropdown-toggle dropdown-caret-none" type="button" id="reminder-view-selector" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span id="reminder-current-view">Month View</span>
                                            <svg class="svg-inline--fa fa-sort fa-w-10 ms-2 fs-10" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="sort" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                                <path fill="currentColor" d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"></path>
                                            </svg>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="reminder-view-selector">
                                            <a class="dropdown-item d-flex justify-content-between active" href="#" data-fc-view="dayGridMonth">Month View<span class="icon-check"></span></a>
                                            <a class="dropdown-item d-flex justify-content-between" href="#" data-fc-view="timeGridWeek">Week View<span class="icon-check"></span></a>
                                            <a class="dropdown-item d-flex justify-content-between" href="#" data-fc-view="timeGridDay">Day View<span class="icon-check"></span></a>
                                            <a class="dropdown-item d-flex justify-content-between" href="#" data-fc-view="listWeek">List View<span class="icon-check"></span></a>
                                            <a class="dropdown-item d-flex justify-content-between" href="#" data-fc-view="year">Year View<span class="icon-check"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="card-body p-0 scrollbar m-3">
                    <div class="calendar-outline" id="reminderCalendar"></div>
                </div>
            </div>

            <!-- Reminder Modal -->
            <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content position-relative border-0">
                        <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                            <div class="position-relative z-1">
                                <h4 class="mb-0 text-white" id="reminderModalLabel">Reminder Details</h4>
                            </div>
                            <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-4 px-5" id="reminderDetails">
                            <!-- Reminder details will be inserted here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="completeReminderBtn">
                                <i class="fas fa-check me-1"></i>Mark Complete
                            </button>
                            <button type="button" class="btn btn-warning" id="dismissReminderBtn">
                                <i class="fas fa-eye-slash me-1"></i>Dismiss
                            </button>
                            <button type="button" class="btn btn-primary" id="editReminderBtn">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Settings View Tab -->
        <div class="tab-pane fade" id="settings-view" role="tabpanel" aria-labelledby="settings-tab">
            <div class="card shadow-none border mb-3">
                <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);"></div>
                <div class="card-header z-1">
                    <div class="row flex-between-center gx-0">
                        <div class="col-lg-auto d-flex align-items-center">
                            <h4 class="mb-0 text-primary fw-bold">Reminder <span class="text-info fw-medium">Settings</span></h4>
                        </div>
                        <div class="col-lg-auto pt-3 pt-lg-0">
                            <small class="text-muted">Configure your reminder notification preferences</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Email Notifications Settings -->
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Email Notification Settings
                            </h5>
                            <small class="text-muted">Control when and how you receive reminder emails</small>
                        </div>
                        <div class="card-body">
                            <form id="reminderSettingsForm">
                                <div class="row g-4">
                                    <!-- Morning Summary Email -->
                                    <div class="col-md-6">
                                        <div class="setting-item p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">
                                                        <i class="fas fa-sun text-warning me-2"></i>
                                                        Morning Summary Email
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        Receive a daily summary of today's reminders and overdue items every morning at 8:00 AM
                                                    </p>
                                                    <div class="d-flex align-items-center text-muted small">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <span>Sent daily at 8:00 AM</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch ms-3">
                                                    <input class="form-check-input" type="checkbox" id="morning_summary_enabled" name="morning_summary_enabled" checked>
                                                    <label class="form-check-label" for="morning_summary_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Evening Progress Email -->
                                    <div class="col-md-6">
                                        <div class="setting-item p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">
                                                        <i class="fas fa-moon text-info me-2"></i>
                                                        Evening Progress Email
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        Get an evening report showing completed, incomplete, and overdue reminders at 11:00 PM
                                                    </p>
                                                    <div class="d-flex align-items-center text-muted small">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <span>Sent daily at 11:00 PM</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch ms-3">
                                                    <input class="form-check-input" type="checkbox" id="evening_progress_enabled" name="evening_progress_enabled" checked>
                                                    <label class="form-check-label" for="evening_progress_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Due Reminders Email -->
                                    <div class="col-md-6">
                                        <div class="setting-item p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">
                                                        <i class="fas fa-bell text-danger me-2"></i>
                                                        Due Reminder Notifications
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        Receive instant email notifications when reminders become due (checked every 5 minutes)
                                                    </p>
                                                    <div class="d-flex align-items-center text-muted small">
                                                        <i class="fas fa-sync me-1"></i>
                                                        <span>Checked every 5 minutes</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch ms-3">
                                                    <input class="form-check-input" type="checkbox" id="due_reminders_enabled" name="due_reminders_enabled" checked>
                                                    <label class="form-check-label" for="due_reminders_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Send Even If No Reminders -->
                                    <div class="col-md-6">
                                        <div class="setting-item p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">
                                                        <i class="fas fa-paper-plane text-success me-2"></i>
                                                        Send Empty Summaries
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        Send morning and evening emails even when you have no reminders scheduled
                                                    </p>
                                                    <div class="d-flex align-items-center text-muted small">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <span>Applies to summary emails</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch ms-3">
                                                    <input class="form-check-input" type="checkbox" id="send_empty_summaries" name="send_empty_summaries" checked>
                                                    <label class="form-check-label" for="send_empty_summaries"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Configuration -->
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="mb-3 fw-semibold">
                                        <i class="fas fa-at text-primary me-2"></i>
                                        Email Configuration
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Notification Email Address</label>
                                            <input type="email" class="form-control" id="notification_email" name="notification_email" value="bryo4419@gmail.com">
                                            <small class="text-muted">Email address where notifications will be sent</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email Format</label>
                                            <select class="form-select" id="email_format" name="email_format">
                                                <option value="html" selected>HTML (Rich formatting)</option>
                                                <option value="text">Plain Text</option>
                                            </select>
                                            <small class="text-muted">Choose your preferred email format</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button -->
                                <div class="mt-4 pt-3 border-top text-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" id="resetSettingsBtn">
                                        <i class="fas fa-undo me-1"></i>Reset to Defaults
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- System Status Tab -->
        <div class="tab-pane fade" id="status-view" role="tabpanel" aria-labelledby="status-tab">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-server text-primary me-2" id="systemStatusIcon"></i>
                        System Status Dashboard
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshSystemStatus()" title="Refresh Status">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="testEmailService()" title="Test Email Service">
                            <i class="fas fa-envelope me-1"></i>Test Email
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- System Health Overview -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Overall System Health</h6>
                                        <span class="badge" id="healthBadge">Loading...</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar" id="healthProgressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted" id="lastSystemUpdate">Last updated: Never</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Components -->
                    <div class="row g-3">
                        <!-- Database Status -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-circle text-success me-2" id="dbStatusIcon"></i>
                                        <h6 class="mb-0">Database Connection</h6>
                                    </div>
                                    <p class="mb-1 fs-10 text-success" id="dbStatusText">Checking...</p>
                                    <small class="text-muted" id="dbLastCheck">Last check: Never</small>
                                </div>
                            </div>
                        </div>

                        <!-- Email Service Status -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-circle text-success me-2" id="emailStatusIcon"></i>
                                        <h6 class="mb-0">Email Service</h6>
                                    </div>
                                    <p class="mb-1 fs-10 text-success" id="emailStatusText">Checking...</p>
                                    <small class="text-muted" id="emailLastCheck">Last check: Never</small>
                                </div>
                            </div>
                        </div>

                        <!-- Last Email Sent -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-circle text-success me-2" id="lastEmailIcon"></i>
                                        <h6 class="mb-0">Last Email Sent</h6>
                                    </div>
                                    <p class="mb-1 fs-10 text-success" id="lastEmailText">Checking...</p>
                                    <small class="text-muted" id="lastEmailTime">Never</small>
                                </div>
                            </div>
                        </div>

                        <!-- Active Reminders -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-circle text-success me-2" id="activeRemindersIcon"></i>
                                        <h6 class="mb-0">Active Reminders</h6>
                                    </div>
                                    <p class="mb-1 fs-10 text-success" id="activeRemindersText">Checking...</p>
                                    <small class="text-muted" id="activeRemindersCount">0 total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Add Reminder Modal -->
    <div class="modal fade" id="addReminderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reminderForm">
                    <div class="modal-body">
                        <!-- Reminder Type Tabs -->
                        <div class="reminder-type-tabs mb-3">
                            <button type="button" class="btn btn-outline-primary reminder-type-tab active me-2" data-type="single">
                                <i class="fas fa-calendar-day me-1"></i> Single Day
                            </button>
                            <button type="button" class="btn btn-outline-primary reminder-type-tab me-2" data-type="multiple_days">
                                <i class="fas fa-calendar-check me-1"></i> Multiple Days
                            </button>
                            <button type="button" class="btn btn-outline-primary reminder-type-tab" data-type="recurring">
                                <i class="fas fa-redo me-1"></i> Recurring
                            </button>
                        </div>

                        <input type="hidden" name="reminder_type" id="reminder_type" value="single">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Title *</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select class="form-select" name="priority">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>

                        <!-- Single Day Options -->
                        <div id="single-day-options">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Date *</label>
                                        <input type="date" class="form-control" name="single_reminder_date" id="single_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" name="single_reminder_time" id="single_time" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multiple Days Options -->
                        <div id="multiple-days-options" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Select Multiple Dates *</label>
                                <input type="text" class="form-control" id="multiple-date-picker"
                                       placeholder="Click to select multiple dates">
                                <input type="hidden" name="selected_dates" id="selected_dates">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Selected Dates:</label>
                                <div class="selected-dates" id="selected-dates-display">
                                    <small class="text-muted">No dates selected</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time *</label>
                                <input type="time" class="form-control" name="multiple_reminder_time" id="multiple_time">
                            </div>
                        </div>

                        <!-- Recurring Options -->
                        <div id="recurring-options" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Start Date *</label>
                                        <input type="date" class="form-control" name="recurring_reminder_date" id="recurring_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" name="recurring_reminder_time" id="recurring_time">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Repeat every (days)</label>
                                        <input type="number" class="form-control" name="recurring_days" min="1" value="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        $stmt = $dbh->query("SELECT * FROM reminder_categories ORDER BY is_default DESC, name ASC");
                                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($categories as $cat):
                                            ?>
                                            <option value="<?= $cat['name'] ?>" data-icon="<?= $cat['icon'] ?>" data-color="<?= $cat['color'] ?>">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Advance Notification</label>
                                    <select class="form-select" name="advance_notification">
                                        <option value="0">None</option>
                                        <option value="30">30 minutes before</option>
                                        <option value="60">1 hour before</option>
                                        <option value="120">2 hours before</option>
                                        <option value="240">4 hours before</option>
                                        <option value="1440">1 day before</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Frequency</label>
                            <select class="form-select" name="email_frequency">
                                <option value="none">No Email</option>
                                <option value="once">Once</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Reminder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Reminder Modal -->
    <div class="modal fade" id="editReminderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editReminderForm">
                    <input type="hidden" name="id" id="edit_reminder_id">
                    <input type="hidden" name="original_type" id="edit_original_type">
                    <div class="modal-body">
                        <!-- Reminder Type Display -->
                        <div class="mb-3">
                            <label class="form-label">Reminder Type</label>
                            <div class="alert alert-info d-flex align-items-center" id="edit_type_display">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="edit_type_text">Loading...</span>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Title *</label>
                                    <input type="text" class="form-control" name="title" id="edit_title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select class="form-select" name="priority" id="edit_priority">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>

                        <!-- Single Day Editing -->
                        <div id="edit_single_day" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Date *</label>
                                        <input type="date" class="form-control" name="reminder_date" id="edit_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" name="reminder_time" id="edit_time">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Reminder Editing -->
                        <div id="edit_recurring" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Start Date *</label>
                                        <input type="date" class="form-control" name="recurring_start_date" id="edit_recurring_start_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" name="recurring_time" id="edit_recurring_time">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Repeat every (days)</label>
                                        <input type="number" class="form-control" name="recurring_days" id="edit_recurring_days" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="recurring_end_date" id="edit_recurring_end_date">
                                    </div>
                                </div>
                            </div>

                            <!-- Recurring Instances Management -->
                            <div class="mb-3">
                                <label class="form-label">Manage Instances</label>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> Changes to recurring settings will regenerate all future instances. Completed instances will be preserved.
                                </div>
                                <div id="recurring_instances_list" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Instances will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Multiple Days Editing -->
                        <div id="edit_multiple_days" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Multiple Days Reminder:</strong> This reminder was created for multiple specific dates. You can edit the basic information, but to change dates, you'll need to create a new reminder.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time *</label>
                                <input type="time" class="form-control" name="multiple_days_time" id="edit_multiple_days_time">
                            </div>
                        </div>

                        <!-- Common Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category" id="edit_category" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        $stmt = $dbh->query("SELECT * FROM reminder_categories ORDER BY is_default DESC, name ASC");
                                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($categories as $cat):
                                            ?>
                                            <option value="<?= $cat['name'] ?>" data-icon="<?= $cat['icon'] ?>" data-color="<?= $cat['color'] ?>">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Advance Notification</label>
                                    <select class="form-select" name="advance_notification" id="edit_advance_notification">
                                        <option value="0">None</option>
                                        <option value="30">30 minutes before</option>
                                        <option value="60">1 hour before</option>
                                        <option value="120">2 hours before</option>
                                        <option value="240">4 hours before</option>
                                        <option value="1440">1 day before</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Frequency</label>
                            <select class="form-select" name="email_frequency" id="edit_email_frequency">
                                <option value="none">No Email</option>
                                <option value="once">Once</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Reminder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Category Management Modal -->
    <div class="modal fade" id="manageCategoriesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-tags me-2"></i>Manage Categories
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Add New Category Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Category</h6>
                        </div>
                        <div class="card-body">
                            <form id="addCategoryForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Category Name</label>
                                        <input type="text" class="form-control" id="categoryName" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Icon</label>
                                        <div class="input-group">
                                        <span class="input-group-text">
                                            <i id="selectedIcon" class="fas fa-tag"></i>
                                        </span>
                                            <input type="text" class="form-control" id="categoryIcon" value="fas fa-tag" readonly>
                                            <button type="button" class="btn btn-outline-secondary" onclick="showIconPicker()">
                                                Choose
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="categoryColor" value="#007bff">
                                            <input type="text" class="form-control" id="categoryColorText" value="#007bff">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Add Category
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Categories -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Existing Categories</h6>
                        </div>
                        <div class="card-body">
                            <div id="categoriesList">
                                <!-- Categories will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div class="modal fade" id="iconPickerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Choose an Icon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" id="iconGrid">
                        <!-- Icons will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editCategoryId">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <div class="input-group">
                            <span class="input-group-text">
                                <i id="editSelectedIcon" class="fas fa-tag"></i>
                            </span>
                                <input type="text" class="form-control" id="editCategoryIcon" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="showIconPicker('edit')">
                                    Choose
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="editCategoryColor">
                                <input type="text" class="form-control" id="editCategoryColorText">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        let selectedDates = [];
        let multipleDatePicker;
        let selectedReminders = new Set();

        // Initialize date pickers with proper timezone handling
        flatpickr("input[type='date']", {
            minDate: "today",
            dateFormat: "Y-m-d",
            // Ensure dates are handled in local timezone
            parseDate: function(datestr, format) {
                return new Date(datestr + 'T00:00:00');
            },
            formatDate: function(date, format) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
        });

        // Initialize multiple date picker
        multipleDatePicker = flatpickr("#multiple-date-picker", {
            mode: "multiple",
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function (selectedDatesArray, dateStr, instance) {
                selectedDates = selectedDatesArray.map(date => {
                    // Ensure we get the date in local timezone without conversion
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                });
                updateSelectedDatesDisplay();
                document.getElementById('selected_dates').value = JSON.stringify(selectedDates);
            }
        });

        // Handle individual checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('reminder-checkbox')) {
                const reminderId = e.target.value;

                if (e.target.checked) {
                    selectedReminders.add(reminderId);
                } else {
                    selectedReminders.delete(reminderId);
                }

                updateBulkActions();
                updateSelectAllState();
            }
        });

        // Handle select all checkbox
        document.getElementById('selectAllReminders').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.reminder-checkbox');
            const isChecked = this.checked;

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const reminderId = checkbox.value;

                if (isChecked) {
                    selectedReminders.add(reminderId);
                } else {
                    selectedReminders.delete(reminderId);
                }
            });

            updateBulkActions();
        });

        // Update bulk actions visibility and count
        function updateBulkActions() {
            const bulkActions = document.querySelector('.bulk-actions');
            const selectedCount = document.getElementById('selectedCount');

            if (selectedReminders.size > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = selectedReminders.size;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        // Update select all checkbox state
        function updateSelectAllState() {
            const selectAllCheckbox = document.getElementById('selectAllReminders');
            const allCheckboxes = document.querySelectorAll('.reminder-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.reminder-checkbox:checked');

            if (checkedCheckboxes.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedCheckboxes.length === allCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllCheckbox.checked = false;
            }
        }

        // Handle bulk delete
        document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
            if (selectedReminders.size === 0) {
                showToast('Warning!', 'No reminders selected', 'warning');
                return;
            }

            // Create confirmation modal
            const confirmModal = `
        <div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-trash me-2"></i>Delete Multiple Reminders
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to permanently delete <strong>${selectedReminders.size}</strong> selected reminder(s)?</p>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <small><strong>Warning:</strong> This action cannot be undone. All selected reminders will be permanently removed.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">
                            <i class="fas fa-trash me-1"></i>Yes, Delete All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Remove existing modal if any
            const existingModal = document.getElementById('bulkDeleteConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', confirmModal);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('bulkDeleteConfirmModal'));
            modal.show();

            // Handle confirm button click
            document.getElementById('confirmBulkDeleteBtn').addEventListener('click', function() {
                const formData = new FormData();
                formData.append('action', 'bulk_delete_reminders');
                formData.append('ids', JSON.stringify([...selectedReminders]));

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            showToast('Success!', data.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Error!', data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error!', 'Something went wrong!', 'danger');
                    });
            });

            // Clean up modal when hidden
            document.getElementById('bulkDeleteConfirmModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        });

        // Handle clear selection
        document.getElementById('clearSelectionBtn').addEventListener('click', function() {
            selectedReminders.clear();
            document.querySelectorAll('.reminder-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateBulkActions();
            updateSelectAllState();
        });

        // Handle per page change
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        // Handle reminder type tabs
        document.querySelectorAll('.reminder-type-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                // Remove active class from all tabs
                document.querySelectorAll('.reminder-type-tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');

                const type = this.dataset.type;
                document.getElementById('reminder_type').value = type;

                // Hide all option divs
                document.getElementById('single-day-options').style.display = 'none';
                document.getElementById('multiple-days-options').style.display = 'none';
                document.getElementById('recurring-options').style.display = 'none';

                // Show relevant options
                if (type === 'single') {
                    document.getElementById('single-day-options').style.display = 'block';
                    document.getElementById('single_time').required = true;
                    document.getElementById('multiple_time').required = false;
                    document.getElementById('recurring_time').required = false;
                } else if (type === 'multiple_days') {
                    document.getElementById('multiple-days-options').style.display = 'block';
                    document.getElementById('single_time').required = false;
                    document.getElementById('multiple_time').required = true;
                    document.getElementById('recurring_time').required = false;
                } else if (type === 'recurring') {
                    document.getElementById('recurring-options').style.display = 'block';
                    document.getElementById('single_time').required = false;
                    document.getElementById('multiple_time').required = false;
                    document.getElementById('recurring_time').required = true;
                }
            });
        });

        // Update selected dates display
        function updateSelectedDatesDisplay() {
            const container = document.getElementById('selected-dates-display');

            if (selectedDates.length === 0) {
                container.innerHTML = '<small class="text-muted">No dates selected</small>';
                return;
            }

            container.innerHTML = selectedDates.map(date => {
                const formattedDate = new Date(date).toLocaleDateString();
                return `<span class="date-tag">${formattedDate}<span class="remove-date" onclick="removeDate('${date}')">&times;</span></span>`;
            }).join('');
        }

        // Remove date from selection
        function removeDate(dateToRemove) {
            selectedDates = selectedDates.filter(date => date !== dateToRemove);
            multipleDatePicker.setDate(selectedDates);
            updateSelectedDatesDisplay();
            document.getElementById('selected_dates').value = JSON.stringify(selectedDates);
        }

        // Form submission
        document.getElementById('reminderForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'add_reminder');

            // Validation for multiple days
            const reminderType = document.getElementById('reminder_type').value;
            if (reminderType === 'multiple_days' && selectedDates.length === 0) {
                showToast('Error!', 'Please select at least one date', 'danger');
                return;
            }

            // Handle different reminder types and set correct time and date values
            if (reminderType === 'multiple_days') {
                formData.delete('reminder_date'); // Remove single date field
                const timeValue = document.getElementById('multiple_time').value;
                formData.set('reminder_time', timeValue);
            } else if (reminderType === 'single') {
                // Get values from single day form
                const dateValue = document.getElementById('single_date').value;
                const timeValue = document.getElementById('single_time').value;

                if (!dateValue) {
                    showToast('Error!', 'Please select a date', 'danger');
                    return;
                }

                formData.set('reminder_date', dateValue);
                formData.set('reminder_time', timeValue);
            } else if (reminderType === 'recurring') {
                // Get values from recurring form
                const dateValue = document.getElementById('recurring_date').value;
                const timeValue = document.getElementById('recurring_time').value;

                if (!dateValue) {
                    showToast('Error!', 'Please select a start date', 'danger');
                    return;
                }

                formData.set('reminder_date', dateValue);
                formData.set('reminder_time', timeValue);
            }

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addReminderModal')).hide();
                        setTimeout(() => location.reload(), 5000);
                    } else {
                        showToast('Error!', data.message, 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Something went wrong!', 'danger');
                });
        });

        // Reset form when modal is closed
        document.getElementById('addReminderModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('reminderForm').reset();
            selectedDates = [];
            multipleDatePicker.clear();
            updateSelectedDatesDisplay();

            // Reset to single day tab
            document.querySelectorAll('.reminder-type-tab').forEach(t => t.classList.remove('active'));
            document.querySelector('.reminder-type-tab[data-type="single"]').classList.add('active');
            document.getElementById('reminder_type').value = 'single';
            document.getElementById('single-day-options').style.display = 'block';
            document.getElementById('multiple-days-options').style.display = 'none';
            document.getElementById('recurring-options').style.display = 'none';
        });

        // Action functions
        function completeReminder(id) {
            const formData = new FormData();
            formData.append('action', 'complete_reminder');
            formData.append('id', id);

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', 'Reminder marked as complete!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('Error!', data.message || 'Failed to complete reminder', 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Something went wrong!', 'danger');
                    console.error('Error:', error);
                });
        }

        function dismissReminder(id) {
            // Create custom confirmation modal
            const confirmModal = `
        <div class="modal fade" id="dismissConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-warning">
                            <i class="fas fa-eye-slash me-2"></i>Dismiss Reminder
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to dismiss this reminder?</p>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Dismissed reminders will be hidden from your list but can be restored later.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-warning" id="confirmDismissBtn">
                            <i class="fas fa-eye-slash me-1"></i>Yes, Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Remove existing modal if any
            const existingModal = document.getElementById('dismissConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', confirmModal);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('dismissConfirmModal'));
            modal.show();

            // Handle confirm button click
            document.getElementById('confirmDismissBtn').addEventListener('click', function() {
                const formData = new FormData();
                formData.append('action', 'dismiss_reminder');
                formData.append('id', id);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            showToast('Success!', 'Reminder dismissed!', 'success');
                            setTimeout(() => location.reload(), 5000);
                        } else {
                            showToast('Error!', 'Failed to dismiss reminder', 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error!', 'Something went wrong!', 'danger');
                    });
            });

            // Clean up modal when hidden
            document.getElementById('dismissConfirmModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function dismissReminderInstance(instanceId) {
            // Create custom confirmation modal
            const confirmModal = `
        <div class="modal fade" id="dismissConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-warning">
                            <i class="fas fa-eye-slash me-2"></i>Dismiss Reminder
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to dismiss this reminder?</p>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Dismissed reminders will be hidden from your list but can be restored later.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-warning" id="confirmDismissBtn">
                            <i class="fas fa-eye-slash me-1"></i>Yes, Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Remove existing modal if any
            const existingModal = document.getElementById('dismissConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', confirmModal);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('dismissConfirmModal'));
            modal.show();

            // Handle confirm button click
            document.getElementById('confirmDismissBtn').addEventListener('click', function() {
                const formData = new FormData();
                formData.append('action', 'dismiss_reminder_instance');
                formData.append('instance_id', instanceId);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            showToast('Success!', 'Reminder dismissed!', 'success');
                            setTimeout(() => location.reload(), 5000);
                        } else {
                            showToast('Error!', 'Failed to dismiss reminder', 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error!', 'Something went wrong!', 'danger');
                    });
            });

            // Clean up modal when hidden
            document.getElementById('dismissConfirmModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function deleteReminder(id) {
            // Create custom confirmation modal
            const confirmModal = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-trash me-2"></i>Delete Reminder
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to permanently delete this reminder?</p>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <small><strong>Warning:</strong> This action cannot be undone. The reminder will be permanently removed from your account.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="fas fa-trash me-1"></i>Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Remove existing modal if any
            const existingModal = document.getElementById('deleteConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', confirmModal);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();

            // Handle confirm button click
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                const formData = new FormData();
                formData.append('action', 'delete_reminder');
                formData.append('id', id);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            showToast('Success!', 'Reminder deleted!', 'success');
                            setTimeout(() => location.reload(), 5000);
                        } else {
                            showToast('Error!', 'Failed to delete reminder', 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error!', 'Something went wrong!', 'danger');
                    });
            });

            // Clean up modal when hidden
            document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function completeReminderInstance(instanceId) {
            const formData = new FormData();
            formData.append('action', 'complete_reminder_instance');
            formData.append('instance_id', instanceId);

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', 'Reminder instance completed!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('Error!', data.message || 'Failed to complete reminder instance', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error!', 'An error occurred', 'error');
                });
        }

        // Updated edit reminder function
        function editReminder(id) {
            const formData = new FormData();
            formData.append('action', 'get_reminder_details');
            formData.append('id', id);

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const reminder = data.reminder;
                        const type = data.type;
                        const instances = data.instances || [];
                        const selectedDates = data.selected_dates || [];

                        // Fill basic information
                        document.getElementById('edit_reminder_id').value = reminder.id;
                        document.getElementById('edit_title').value = reminder.title;
                        document.getElementById('edit_description').value = reminder.description || '';
                        document.getElementById('edit_priority').value = reminder.priority;
                        document.getElementById('edit_category').value = reminder.category;
                        document.getElementById('edit_advance_notification').value = reminder.advance_notification;
                        document.getElementById('edit_email_frequency').value = reminder.email_frequency;

                        // Store the reminder type for form submission
                        document.getElementById('editReminderForm').dataset.reminderType = type;
                        document.getElementById('edit_original_type').value = type;

                        // Hide all edit sections first
                        document.getElementById('edit_single_day').style.display = 'none';
                        document.getElementById('edit_recurring').style.display = 'none';
                        document.getElementById('edit_multiple_days').style.display = 'none';

                        // Show appropriate section based on type
                        if (type === 'recurring') {
                            document.getElementById('edit_type_text').innerHTML = '<i class="fas fa-redo me-2"></i>Recurring Reminder';
                            document.getElementById('edit_recurring').style.display = 'block';

                            document.getElementById('edit_recurring_start_date').value = reminder.reminder_date;
                            document.getElementById('edit_recurring_time').value = reminder.reminder_time;
                            document.getElementById('edit_recurring_days').value = reminder.recurring_days;
                            document.getElementById('edit_recurring_end_date').value = reminder.end_date || '';

                            // Load instances
                            loadRecurringInstances(instances);

                        } else if (type === 'multiple_days') {
                            document.getElementById('edit_type_text').innerHTML = `<i class="fas fa-calendar-check me-2"></i>Multiple Days Reminder (${selectedDates.length} dates)`;
                            document.getElementById('edit_multiple_days').style.display = 'block';
                            document.getElementById('edit_multiple_days_time').value = reminder.reminder_time;

                            // Show selected dates info
                            const datesInfo = selectedDates.map(date => new Date(date).toLocaleDateString()).join(', ');
                            const alertDiv = document.querySelector('#edit_multiple_days .alert');
                            alertDiv.innerHTML = `
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Multiple Days Reminder:</strong> This reminder is set for the following dates: ${datesInfo}.
                    You can edit the basic information and time, but to change dates, you'll need to create a new reminder.
                `;

                        } else {
                            document.getElementById('edit_type_text').innerHTML = '<i class="fas fa-calendar-day me-2"></i>Single Day Reminder';
                            document.getElementById('edit_single_day').style.display = 'block';
                            document.getElementById('edit_date').value = reminder.reminder_date;
                            document.getElementById('edit_time').value = reminder.reminder_time;
                        }

                        new bootstrap.Modal(document.getElementById('editReminderModal')).show();
                    } else {
                        showToast('Error!', data.message, 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Failed to load reminder details', 'danger');
                    console.error('Error:', error);
                });
        }

        function loadRecurringInstances(instances) {
            const container = document.getElementById('recurring_instances_list');

            if (instances.length === 0) {
                container.innerHTML = '<p class="text-muted mb-0">No instances found</p>';
                return;
            }

            const instancesHtml = instances.map(instance => {
                const date = new Date(instance.instance_date + ' ' + instance.instance_time);
                const isCompleted = instance.is_completed == 1;
                const isDismissed = instance.is_dismissed == 1;

                let statusBadge = '<span class="badge bg-primary">Pending</span>';
                if (isCompleted) {
                    statusBadge = '<span class="badge bg-success">Completed</span>';
                } else if (isDismissed) {
                    statusBadge = '<span class="badge bg-warning">Dismissed</span>';
                }

                return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <strong>${date.toLocaleDateString()}</strong> at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
                <div>
                    ${statusBadge}
                </div>
            </div>
        `;
            }).join('');

            container.innerHTML = instancesHtml;
        }

        // Updated edit form submission
        document.getElementById('editReminderForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const reminderType = this.dataset.reminderType || document.getElementById('edit_original_type').value || 'single';

            // Determine which action to use based on reminder type
            let action = 'update_reminder'; // default for single reminders

            if (reminderType === 'recurring') {
                action = 'update_recurring_reminder';
                // Map the form fields to the expected parameter names
                formData.set('reminder_date', document.getElementById('edit_recurring_start_date').value);
                formData.set('reminder_time', document.getElementById('edit_recurring_time').value);
                formData.set('recurring_days', document.getElementById('edit_recurring_days').value);
                formData.set('end_date', document.getElementById('edit_recurring_end_date').value);

                formData.append('action', action);
                submitEditForm(formData, action);

            } else if (reminderType === 'multiple_days') {
                action = 'update_multiple_days_reminder';

                // For multiple days, we need to get the current selected dates
                const currentId = document.getElementById('edit_reminder_id').value;

                // Create a new FormData for the get request
                const getFormData = new FormData();
                getFormData.append('action', 'get_reminder_details');
                getFormData.append('id', currentId);

                // Get current dates and update with new time
                fetch('', {
                    method: 'POST',
                    body: getFormData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.selected_dates) {
                            formData.set('selected_dates', JSON.stringify(data.selected_dates));
                            formData.set('reminder_time', document.getElementById('edit_multiple_days_time').value);
                            formData.append('action', action);

                            // Submit the form
                            submitEditForm(formData, action);
                        } else {
                            showToast('Error!', 'Failed to get current dates for multiple days reminder', 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error!', 'Failed to update multiple days reminder', 'danger');
                        console.error('Error:', error);
                    });
                return; // Exit early since we're handling submission in the callback

            } else {
                // Single day reminder
                formData.append('action', action);
                submitEditForm(formData, action);
            }
        });

        // Helper function to submit the edit form
        function submitEditForm(formData, action) {
            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editReminderModal')).hide();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('Error!', data.message, 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Something went wrong!', 'danger');
                    console.error('Error:', error);
                });
        }

        // Toast function
        function showToast(title, message, type = 'info') {
            const toastId = 'toast-' + Date.now();
            const toast = `
                            <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                                <div class="toast-body bg-${type} text-white d-flex align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center">
                                        <strong class="me-2">${title}</strong>
                                        <span>${message}</span>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                            `;

            document.getElementById('toast-container').insertAdjacentHTML('beforeend', toast);
            const toastElement = new bootstrap.Toast(document.getElementById(toastId));
            toastElement.show();
        }


        // Check for due reminders every minute
        setInterval(checkDueReminders, 600000);

        function checkDueReminders() {
            fetch('check_reminder')
                .then(response => response.json())
                .then(data => {
                    data.forEach(reminder => {
                        // Calculate time remaining
                        const reminderDateTime = new Date(reminder.reminder_date + ' ' + reminder.reminder_time);
                        const now = new Date();
                        const timeDiff = reminderDateTime - now;
                        const hoursRemaining = timeDiff / (1000 * 60 * 60);

                        // Show persistent toast for reminders with less than 10 hours remaining
                        if (hoursRemaining <= 10 && hoursRemaining > 0 && !reminder.is_completed) {
                            showUrgentReminderToast(reminder, hoursRemaining);
                        }
                    });
                });
        }

        function completeReminderFromToast(id, toastId) {
            // Get the reminder data to check if it's an instance
            const toastElement = document.getElementById(toastId);
            const isInstance = toastElement.dataset.isInstance === 'true';
            const instanceId = toastElement.dataset.instanceId;

            const formData = new FormData();

            if (isInstance) {
                formData.append('action', 'complete_reminder_instance');
                formData.append('instance_id', instanceId);
                formData.append('reminder_id', id);
            } else {
                formData.append('action', 'complete_reminder');
                formData.append('id', id);
            }

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the urgent toast
                        const toast = document.getElementById(toastId);
                        if (toast) {
                            bootstrap.Toast.getInstance(toast).hide();
                            setTimeout(() => toast.remove(), 300);
                        }
                        const message = isInstance ? 'Reminder instance marked as complete!' : 'Reminder marked as complete!';
                        showToast('Success!', message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                });
        }

        function dismissReminderFromToast(id, toastId) {
            // Get the reminder data to check if it's an instance
            const toastElement = document.getElementById(toastId);
            const isInstance = toastElement.dataset.isInstance === 'true';
            const instanceId = toastElement.dataset.instanceId;

            // Create confirmation modal for dismiss from toast
            const confirmModal = `
        <div class="modal fade" id="dismissFromToastModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-warning">
                            <i class="fas fa-eye-slash me-2"></i>Dismiss Urgent Reminder
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to dismiss this urgent reminder?</p>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>${isInstance ? 'This will dismiss only this instance of the recurring reminder.' : 'This reminder is due soon. Dismissing will hide it from your urgent notifications.'}</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-warning" id="confirmDismissFromToastBtn">
                            <i class="fas fa-eye-slash me-1"></i>Yes, Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            document.body.insertAdjacentHTML('beforeend', confirmModal);
            const modal = new bootstrap.Modal(document.getElementById('dismissFromToastModal'));
            modal.show();

            document.getElementById('confirmDismissFromToastBtn').addEventListener('click', function() {
                const formData = new FormData();

                if (isInstance) {
                    formData.append('action', 'dismiss_reminder_instance');
                    formData.append('instance_id', instanceId);
                    formData.append('reminder_id', id);
                } else {
                    formData.append('action', 'dismiss_reminder');
                    formData.append('id', id);
                }

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            // Remove the urgent toast
                            const toast = document.getElementById(toastId);
                            if (toast) {
                                bootstrap.Toast.getInstance(toast).hide();
                                setTimeout(() => toast.remove(), 300);
                            }
                            const message = isInstance ? 'Reminder instance dismissed!' : 'Urgent reminder dismissed!';
                            showToast('Success!', message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        }
                    });
            });

            document.getElementById('dismissFromToastModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function showUrgentReminderToast(reminder, hoursRemaining) {
            const toastId = 'urgent-toast-' + reminder.id + (reminder.is_instance ? '-instance-' + reminder.instance_id : '');

            // Check if toast already exists for this reminder
            if (document.getElementById(toastId)) {
                return; // Don't create duplicate toasts
            }

            // Format time remaining
            let timeText = '';
            if (hoursRemaining < 1) {
                const minutesRemaining = Math.floor((hoursRemaining * 60));
                timeText = minutesRemaining > 0 ? `${minutesRemaining} minutes` : 'Less than a minute';
            } else {
                const hours = Math.floor(hoursRemaining);
                const minutes = Math.floor((hoursRemaining - hours) * 60);
                timeText = minutes > 0 ? `${hours}h ${minutes}m` : `${hours} hours`;
            }

            const priorityClass = reminder.priority === 'high' ? 'danger' :
                reminder.priority === 'medium' ? 'warning' : 'info';

            const recurringBadge = reminder.is_recurring ?
                `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
            <i class="fas fa-redo me-1"></i>${reminder.is_instance ? 'Recurring Instance' : 'Recurring'}
        </span>` : '';

            const toast = `
    <div class="toast urgent-reminder-toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true"
         data-bs-autohide="false" data-is-instance="${reminder.is_instance || false}"
         data-instance-id="${reminder.instance_id || ''}">
        <div class="toast-header bg-${priorityClass} text-white">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong class="me-auto">Urgent Reminder</strong>
            <small class="text-white-50">${timeText} remaining</small>
            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">${reminder.title}</h6>
                    ${reminder.description ? `<p class="mb-2 text-muted small">${reminder.description}</p>` : ''}
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-${priorityClass} bg-opacity-10 text-${priorityClass} border border-${priorityClass} border-opacity-25">
                            <i class="fas fa-flag me-1"></i>${reminder.priority.charAt(0).toUpperCase() + reminder.priority.slice(1)}
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-tag me-1"></i>${reminder.category.charAt(0).toUpperCase() + reminder.category.slice(1)}
                        </span>
                        ${recurringBadge}
                    </div>
                    <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="fas fa-calendar me-1"></i>
                        <span>${new Date(reminder.reminder_date + ' ' + reminder.reminder_time).toLocaleString()}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-success btn-sm flex-fill" onclick="completeReminderFromToast(${reminder.id}, '${toastId}')">
                    <i class="fas fa-check me-1"></i>Mark Complete
                </button>
                <button class="btn btn-warning btn-sm flex-fill" onclick="dismissReminderFromToast(${reminder.id}, '${toastId}')">
                    <i class="fas fa-eye-slash me-1"></i>Dismiss
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="snoozeReminder(${reminder.id}, '${toastId}')">
                    <i class="fas fa-clock me-1"></i>Snooze
                </button>
            </div>
        </div>
    </div>
    `;

            document.getElementById('toast-container').insertAdjacentHTML('beforeend', toast);
            const toastElement = new bootstrap.Toast(document.getElementById(toastId));
            toastElement.show();
        }

        function snoozeReminder(id, toastId) {
            // Create snooze options modal
            const snoozeModal = `
        <div class="modal fade" id="snoozeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-info">
                            <i class="fas fa-clock me-2"></i>Snooze Reminder
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">How long would you like to snooze this reminder?</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="applySnooze(${id}, '${toastId}', 15)">
                                <i class="fas fa-clock me-2"></i>15 minutes
                            </button>
                            <button class="btn btn-outline-primary" onclick="applySnooze(${id}, '${toastId}', 30)">
                                <i class="fas fa-clock me-2"></i>30 minutes
                            </button>
                            <button class="btn btn-outline-primary" onclick="applySnooze(${id}, '${toastId}', 60)">
                                <i class="fas fa-clock me-2"></i>1 hour
                            </button>
                            <button class="btn btn-outline-primary" onclick="applySnooze(${id}, '${toastId}', 120)">
                                <i class="fas fa-clock me-2"></i>2 hours
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            document.body.insertAdjacentHTML('beforeend', snoozeModal);
            const modal = new bootstrap.Modal(document.getElementById('snoozeModal'));
            modal.show();

            document.getElementById('snoozeModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function applySnooze(id, toastId, minutes) {
            // Hide the urgent toast temporarily
            const toast = document.getElementById(toastId);
            if (toast) {
                bootstrap.Toast.getInstance(toast).hide();
                setTimeout(() => toast.remove(), 300);
            }

            // Close snooze modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('snoozeModal'));
            modal.hide();

            showToast('Snoozed!', `Reminder snoozed for ${minutes} minutes`, 'info');

            // Set timeout to show the toast again after snooze period
            setTimeout(() => {
                checkDueReminders(); // This will re-check and show the toast if still urgent
            }, minutes * 60 * 1000);
        }

        function restoreReminder(id, sourceType = 'reminder', instanceId = null) {
            const formData = new FormData();
            formData.append('action', 'restore_reminder');
            formData.append('id', id);
            formData.append('source_type', sourceType);

            if (instanceId) {
                formData.append('instance_id', instanceId);
            }

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('Error!', 'Failed to restore reminder', 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Something went wrong!', 'danger');
                });
        }

        // Initial check
        checkDueReminders();

        // Available icons for categories
        const availableIcons = [
            'fas fa-briefcase', 'fas fa-user', 'fas fa-home', 'fas fa-heart', 'fas fa-star',
            'fas fa-graduation-cap', 'fas fa-car', 'fas fa-plane', 'fas fa-shopping-cart', 'fas fa-gift',
            'fas fa-camera', 'fas fa-music', 'fas fa-book', 'fas fa-gamepad', 'fas fa-coffee',
            'fas fa-utensils', 'fas fa-dumbbell', 'fas fa-running', 'fas fa-bicycle', 'fas fa-swimmer',
            'fas fa-dollar-sign', 'fas fa-tag', 'fas fa-book-open', 'fas fa-university', 'fas fa-school',
            'fas fa-chalkboard-teacher', 'fas fa-pencil-alt', 'fas fa-pen', 'fas fa-highlighter', 'fas fa-bookmark',
            'fas fa-certificate', 'fas fa-award', 'fas fa-trophy', 'fas fa-brain', 'fas fa-lightbulb',
            'fas fa-microscope', 'fas fa-flask', 'fas fa-calculator', 'fas fa-globe', 'fas fa-language',
            'fas fa-laptop', 'fas fa-desktop', 'fas fa-tablet-alt', 'fas fa-file-alt', 'fas fa-clipboard-list',
            'fas fa-calendar-alt', 'fas fa-clock', 'fas fa-stopwatch', 'fas fa-chart-line', 'fas fa-puzzle-piece',
            'fas fa-cogs', 'fas fa-atom', 'fas fa-dna', 'fas fa-seedling', 'fas fa-palette',
            'fas fa-video', 'fas fa-headphones', 'fas fa-comments', 'fas fa-users', 'fas fa-user-graduate',
            'fas fa-chalkboard', 'fas fa-ruler', 'fas fa-compass', 'fas fa-map', 'fas fa-search',
            'fas fa-question-circle', 'fas fa-exclamation-circle', 'fas fa-info-circle'
        ];

        // Load categories when modal opens
        $('#manageCategoriesModal').on('show.bs.modal', function() {
            loadCategories();
        });

        // Load categories list
        function loadCategories() {
            $.post('reminders', {
                action: 'get_categories'
            }, function(response) {
                if (response.success) {
                    displayCategories(response.categories);
                } else {
                    showToast('Error', 'Failed to load categories', 'danger');
                }
            }, 'json').fail(function() {
                showToast('Error', 'Failed to connect to server', 'danger');
            });
        }

        // Display categories in the list
        function displayCategories(categories) {
            let html = '';
            categories.forEach(category => {
                html += `
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="${category.icon}" style="color: ${category.color}; font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">${category.name}</h6>
                        <small class="text-muted">${category.icon} • ${category.color}</small>
                    </div>
                    ${category.is_default ? '<span class="badge badge-subtle-info ms-2">Default</span>' : ''}
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editCategory(${category.id}, '${category.name}', '${category.icon}', '${category.color}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${!category.is_default ? `
                        <button class="btn btn-outline-danger" onclick="deleteCategory(${category.id}, '${category.name}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
            });
            $('#categoriesList').html(html);
        }

        // Show icon picker
        function showIconPicker(mode = 'add') {
            let html = '';
            availableIcons.forEach(icon => {
                html += `
            <div class="col-2 col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100 p-2" onclick="selectIcon('${icon}', '${mode}')">
                    <i class="${icon}"></i>
                </button>
            </div>
        `;
            });
            $('#iconGrid').html(html);
            $('#iconPickerModal').modal('show');
        }

        // Select icon
        function selectIcon(icon, mode) {
            if (mode === 'edit') {
                $('#editCategoryIcon').val(icon);
                $('#editSelectedIcon').attr('class', icon);
            } else {
                $('#categoryIcon').val(icon);
                $('#selectedIcon').attr('class', icon);
            }
            $('#iconPickerModal').modal('hide');
        }

        // Color picker sync
        $('#categoryColor').on('input', function() {
            $('#categoryColorText').val($(this).val());
        });

        $('#categoryColorText').on('input', function() {
            $('#categoryColor').val($(this).val());
        });

        $('#editCategoryColor').on('input', function() {
            $('#editCategoryColorText').val($(this).val());
        });

        $('#editCategoryColorText').on('input', function() {
            $('#editCategoryColor').val($(this).val());
        });

        // Add category form submission
        $('#addCategoryForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...').prop('disabled', true);

            const formData = {
                action: 'add_category',
                name: $('#categoryName').val(),
                icon: $('#categoryIcon').val(),
                color: $('#categoryColor').val()
            };

            $.post('reminders', formData, function(response) {
                if (response.success) {
                    // Reset form
                    $('#addCategoryForm')[0].reset();
                    $('#categoryIcon').val('fas fa-tag');
                    $('#selectedIcon').attr('class', 'fas fa-tag');
                    $('#categoryColor').val('#007bff');
                    $('#categoryColorText').val('#007bff');

                    // Reload categories
                    loadCategories();

                    // Show success toast
                    showToast('Success', 'Category added!', 'success');
                } else {
                    showToast('Error', response.message || 'Error adding category', 'danger');
                }
            }, 'json').fail(function() {
                showToast('Error', 'Failed to connect to server', 'danger');
            }).always(function() {
                submitBtn.html(originalText).prop('disabled', false);
            });
        });

        // Edit category
        function editCategory(id, name, icon, color) {
            $('#editCategoryId').val(id);
            $('#editCategoryName').val(name);
            $('#editCategoryIcon').val(icon);
            $('#editSelectedIcon').attr('class', icon);
            $('#editCategoryColor').val(color);
            $('#editCategoryColorText').val(color);
            $('#editCategoryModal').modal('show');
        }

        // Edit category form submission
        $('#editCategoryForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

            const formData = {
                action: 'update_category',
                id: $('#editCategoryId').val(),
                name: $('#editCategoryName').val(),
                icon: $('#editCategoryIcon').val(),
                color: $('#editCategoryColor').val()
            };

            $.post('reminders', formData, function(response) {
                if (response.success) {
                    $('#editCategoryModal').modal('hide');
                    loadCategories();
                    showToast('Success', 'Category updated!', 'success');
                } else {
                    showToast('Error', response.message || 'Error updating category', 'danger');
                }
            }, 'json').fail(function() {
                showToast('Error', 'Failed to connect to server', 'danger');
            }).always(function() {
                submitBtn.html(originalText).prop('disabled', false);
            });
        });

        // Delete category
        function deleteCategory(id, name) {
            // Create a custom confirmation toast instead of browser confirm
            const confirmToastId = 'confirm-toast-' + Date.now();
            const confirmToast = `
        <div class="toast" id="${confirmToastId}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
            <div class="toast-body bg-warning text-dark d-flex flex-column p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong>Confirm Delete</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <p class="mb-3">Are you sure you want to delete the category "${name}"? This action cannot be undone.</p>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="dismissConfirmToast('${confirmToastId}')">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteCategory(${id}, '${confirmToastId}')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    `;

            document.getElementById('toast-container').insertAdjacentHTML('beforeend', confirmToast);
            const toastElement = new bootstrap.Toast(document.getElementById(confirmToastId));
            toastElement.show();
        }

        // Dismiss confirmation toast
        function dismissConfirmToast(toastId) {
            const toastElement = bootstrap.Toast.getInstance(document.getElementById(toastId));
            if (toastElement) {
                toastElement.hide();
            }
        }

        // Confirm delete category
        function confirmDeleteCategory(id, toastId) {
            // Dismiss confirmation toast
            dismissConfirmToast(toastId);

            // Show loading toast
            showToast('Deleting', 'Deleting category...', 'info');

            $.post('reminders', {
                action: 'delete_category',
                id: id
            }, function(response) {
                if (response.success) {
                    loadCategories();
                    showToast('Success', 'Category deleted!', 'success');
                } else {
                    showToast('Error', response.message || 'Error deleting category', 'danger');
                }
            }, 'json').fail(function() {
                showToast('Error', 'Failed to connect to server', 'danger');
            });
        }

    </script>
    <script>
        // Calendar functionality
        let reminderCalendar;

        // Fetch reminders data for calendar
        function fetchRemindersForCalendar() {
            return fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_calendar_reminders'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data.reminders.map(reminder => {
                            // Determine status and badge info
                            const reminderDateTime = new Date(reminder.reminder_date + ' ' + reminder.reminder_time);
                            const now = new Date();
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            const reminderDate = new Date(reminder.reminder_date);
                            reminderDate.setHours(0, 0, 0, 0);

                            let status = 'upcoming';
                            let badgeIcon = 'U';
                            let backgroundColor = '';
                            let borderColor = '';
                            let textColor = '';

                            // Determine status and Bootstrap badge colors
                            if (reminder.is_dismissed == 1) {
                                status = 'dismissed';
                                badgeIcon = 'D';
                                backgroundColor = 'var(--bs-warning)';
                                borderColor = 'var(--bs-warning)';
                                textColor = 'var(--bs-dark)';
                            } else if (reminder.is_completed == 1) {
                                status = 'completed';
                                badgeIcon = '✓';
                                backgroundColor = 'var(--bs-success)';
                                borderColor = 'var(--bs-success)';
                                textColor = 'white';
                            } else if (reminderDateTime < now) {
                                status = 'overdue';
                                badgeIcon = '!';
                                backgroundColor = 'var(--bs-danger)';
                                borderColor = 'var(--bs-danger)';
                                textColor = 'white';
                            } else if (reminderDate.getTime() === today.getTime()) {
                                status = 'today';
                                badgeIcon = 'T';
                                backgroundColor = 'var(--bs-info)';
                                borderColor = 'var(--bs-info)';
                                textColor = 'white';
                            } else {
                                // Upcoming - use priority colors
                                switch (reminder.priority) {
                                    case 'high':
                                        backgroundColor = 'var(--bs-danger)';
                                        borderColor = 'var(--bs-danger)';
                                        textColor = 'white';
                                        break;
                                    case 'medium':
                                        backgroundColor = 'var(--bs-warning)';
                                        borderColor = 'var(--bs-warning)';
                                        textColor = 'var(--bs-dark)';
                                        break;
                                    case 'low':
                                        backgroundColor = 'var(--bs-secondary)';
                                        borderColor = 'var(--bs-secondary)';
                                        textColor = 'white';
                                        break;
                                    default:
                                        backgroundColor = 'var(--bs-primary)';
                                        borderColor = 'var(--bs-primary)';
                                        textColor = 'white';
                                }
                            }

                            return {
                                id: reminder.id,
                                title: reminder.title,
                                start: reminder.reminder_date + 'T' + reminder.reminder_time,
                                end: reminder.reminder_date + 'T23:59:59', // Ensure event ends on same day
                                backgroundColor: backgroundColor,
                                borderColor: borderColor,
                                textColor: textColor,
                                extendedProps: {
                                    description: reminder.description,
                                    priority: reminder.priority,
                                    category: reminder.category,
                                    category_icon: reminder.category_icon || 'fas fa-bell', // Use category icon or fallback
                                    category_color: reminder.category_color,
                                    advance_notification: reminder.advance_notification,
                                    email_frequency: reminder.email_frequency,
                                    is_completed: reminder.is_completed,
                                    is_dismissed: reminder.is_dismissed,
                                    is_recurring: reminder.is_recurring,
                                    source_type: reminder.source_type,
                                    instance_id: reminder.instance_id,
                                    status: status,
                                    badgeIcon: badgeIcon
                                }
                            };
                        });
                    }
                    return [];
                })
                .catch(error => {
                    console.error('Error fetching reminders:', error);
                    return [];
                });
        }

        // Initialize reminder calendar when tab is shown
        document.getElementById('calendar-tab').addEventListener('shown.bs.tab', function (e) {
            if (!reminderCalendar) {
                const calendarEl = document.getElementById('reminderCalendar');

                fetchRemindersForCalendar().then(events => {
                    reminderCalendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: ''
                        },
                        views: {
                            year: {
                                type: 'dayGrid',
                                duration: { years: 1 },
                                buttonText: 'Year'
                            }
                        },
                        events: events,
                        eventClick: function (info) {
                            showReminderModal(info.event);
                        },
                        eventContent: function (info) {
                            const content = document.createElement('div');
                            content.classList.add('d-flex', 'align-items-center', 'justify-content-between', 'p-1', 'rounded', 'small', 'h-100', 'w-100');

                            // Apply Bootstrap badge-subtle classes based on status
                            const reminder = info.event.extendedProps;
                            let badgeClass = '';

                            switch(reminder.status) {
                                case 'today':
                                    badgeClass = 'badge-subtle-info';
                                    break;
                                case 'completed':
                                    badgeClass = 'badge-subtle-success';
                                    break;
                                case 'overdue':
                                    badgeClass = 'badge-subtle-danger';
                                    break;
                                case 'dismissed':
                                    badgeClass = 'badge-subtle-warning';
                                    break;
                                default: // upcoming
                                    switch(reminder.priority) {
                                        case 'high':
                                            badgeClass = 'badge-subtle-danger';
                                            break;
                                        case 'medium':
                                            badgeClass = 'badge-subtle-warning';
                                            break;
                                        case 'low':
                                            badgeClass = 'badge-subtle-secondary';
                                            break;
                                        default:
                                            badgeClass = 'badge-subtle-primary';
                                    }
                            }

                            content.classList.add('badge', badgeClass);

                            // Use category icon, but override with status-specific icons when appropriate
                            let icon = reminder.category_icon || 'fas fa-bell';

                            if (reminder.is_completed) {
                                icon = 'fas fa-check-circle';
                            } else if (reminder.is_dismissed) {
                                icon = 'fas fa-eye-slash';
                            } else if (reminder.is_recurring) {
                                // Show both category icon and recurring indicator
                                icon = `${reminder.category_icon || 'fas fa-bell'} me-1"></i><i class="fas fa-redo`;
                            }

                            content.innerHTML = `
        <div class="flex-grow-1 text-truncate">
            <i class="${icon} me-1"></i><strong>${info.event.title}</strong>
        </div>
        <div class="calendar-event-badge-icon ms-1" title="Status: ${reminder.status.charAt(0).toUpperCase() + reminder.status.slice(1)}">
            ${reminder.badgeIcon}
        </div>
    `;

                            return { domNodes: [content] };
                        }
                    });

                    reminderCalendar.render();
                });

                // Handle view selector for reminders
                document.querySelectorAll('#reminder-view-selector + .dropdown-menu .dropdown-item').forEach(function(item) {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const view = this.getAttribute('data-fc-view');
                        reminderCalendar.changeView(view);
                        document.getElementById('reminder-current-view').textContent = this.textContent.trim();

                        document.querySelectorAll('#reminder-view-selector + .dropdown-menu .dropdown-item').forEach(function(item) {
                            item.classList.remove('active');
                        });
                        this.classList.add('active');
                    });
                });
            }
        });

        // Show reminder modal with details
        function showReminderModal(event) {
            const reminder = event.extendedProps;
            const reminderDate = new Date(event.start);
            const formattedDate = reminderDate.toLocaleDateString();
            const formattedTime = reminderDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            // Status badges
            const badges = {
                'high': '<span class="badge badge-subtle-danger">High Priority</span>',
                'medium': '<span class="badge badge-subtle-warning">Medium Priority</span>',
                'low': '<span class="badge badge-subtle-secondary">Low Priority</span>'
            };

            let statusBadge = '';
            if (reminder.is_completed) {
                statusBadge = '<span class="badge badge-subtle-success"><i class="fas fa-check me-1"></i>Completed</span>';
            } else if (reminder.is_dismissed) {
                statusBadge = '<span class="badge badge-subtle-warning"><i class="fas fa-eye-slash me-1"></i>Dismissed</span>';
            } else {
                // Show specific status based on the calculated status
                switch(reminder.status) {
                    case 'today':
                        statusBadge = '<span class="badge badge-subtle-info"><i class="fas fa-calendar-day me-1"></i>Due Today</span>';
                        break;
                    case 'overdue':
                        statusBadge = '<span class="badge badge-subtle-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>';
                        break;
                    default:
                        statusBadge = '<span class="badge badge-subtle-primary"><i class="fas fa-clock me-1"></i>Upcoming</span>';
                }
            }

            // Get category icon and color, with fallbacks
            const categoryIcon = reminder.category_icon || 'fas fa-tag';
            const categoryColor = reminder.category_color || '#007bff';

            // Convert hex color to RGB for background opacity
            const hexToRgb = (hex) => {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? {
                    r: parseInt(result[1], 16),
                    g: parseInt(result[2], 16),
                    b: parseInt(result[3], 16)
                } : {r: 0, g: 123, b: 255}; // fallback to blue
            };

            const rgb = hexToRgb(categoryColor);
            const categoryBgColor = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)`;

            const reminderDetails = `
        <div class="modal-body px-card pb-card pt-1 fs-9">
            <div class="d-flex mt-3">
                <span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x" style="color: ${categoryBgColor};"></i>
                    <i class="${categoryIcon} fa-stack-1x" style="color: ${categoryColor};"></i>
                </span>
                <div class="flex-1">
                    <h5 class="mb-1">${event.title}</h5>
                    ${reminder.description ? `<p class="text-muted mb-0">${reminder.description}</p>` : ''}
                </div>
            </div>
            <div class="d-flex mt-3">
                <span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x text-200"></i>
                    <i class="fas fa-calendar-check fa-stack-1x text-primary"></i>
                </span>
                <div class="flex-1">
                    <p class="mb-0">Due: ${formattedDate}, ${formattedTime}</p>
                </div>
            </div>
            <div class="d-flex mt-3">
                <span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x text-200"></i>
                    <i class="fas fa-flag fa-stack-1x text-primary"></i>
                </span>
                <div class="flex-1">
                    <p class="mb-0">${badges[reminder.priority]}</p>
                </div>
            </div>
            <div class="d-flex mt-3">
                <span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x" style="color: ${categoryBgColor};"></i>
                    <i class="${categoryIcon} fa-stack-1x" style="color: ${categoryColor};"></i>
                </span>
                <div class="flex-1">
                    <p class="mb-0">
                        <span class="badge border-0 px-2 py-1" style="background-color: ${categoryColor}; color: white;">
                            <i class="${categoryIcon} me-1"></i>${reminder.category}
                        </span>
                    </p>
                </div>
            </div>
            <div class="d-flex mt-3">
                <span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x text-200"></i>
                    <i class="fas fa-info-circle fa-stack-1x text-primary"></i>
                </span>
                <div class="flex-1">
                    <p class="mb-0">${statusBadge}</p>
                </div>
            </div>
            ${reminder.is_recurring ? `
            <div class="d-flex mt-3">
                <span class="fa-stack ms-n1 me-3">
                    <i class="fas fa-circle fa-stack-2x text-200"></i>
                    <i class="fas fa-redo fa-stack-1x text-primary"></i>
                </span>
                <div class="flex-1">
                    <p class="mb-0"><span class="badge badge-subtle-info">Recurring Reminder</span></p>
                </div>
            </div>
            ` : ''}
        </div>
    `;

            document.getElementById('reminderDetails').innerHTML = reminderDetails;

            // Set up action buttons
            const completeBtn = document.getElementById('completeReminderBtn');
            const dismissBtn = document.getElementById('dismissReminderBtn');
            const editBtn = document.getElementById('editReminderBtn');

            // Hide/show buttons based on status
            if (reminder.is_completed) {
                completeBtn.style.display = 'none';
            } else {
                completeBtn.style.display = 'inline-block';
                completeBtn.onclick = () => {
                    if (reminder.source_type === 'instance') {
                        completeReminderInstance(reminder.instance_id);
                    } else {
                        completeReminder(event.id);
                    }
                    bootstrap.Modal.getInstance(document.getElementById('reminderModal')).hide();
                };
            }

            if (reminder.is_dismissed) {
                dismissBtn.textContent = 'Restore';
                dismissBtn.className = 'btn btn-success';
                dismissBtn.innerHTML = '<i class="fas fa-undo me-1"></i>Restore';
                dismissBtn.onclick = () => {
                    restoreReminder(event.id, reminder.source_type, reminder.instance_id);
                    bootstrap.Modal.getInstance(document.getElementById('reminderModal')).hide();
                };
            } else {
                dismissBtn.textContent = 'Dismiss';
                dismissBtn.className = 'btn btn-warning';
                dismissBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Dismiss';
                dismissBtn.onclick = () => {
                    if (reminder.source_type === 'instance') {
                        dismissReminderInstance(reminder.instance_id);
                    } else {
                        dismissReminder(event.id);
                    }
                    bootstrap.Modal.getInstance(document.getElementById('reminderModal')).hide();
                };
            }

            editBtn.onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('reminderModal')).hide();
                editReminder(reminder.source_type === 'instance' ? reminder.reminder_id : event.id);
            };

            const reminderModal = new bootstrap.Modal(document.getElementById('reminderModal'));
            reminderModal.show();
        }

        // Restore reminder function
        function restoreReminder(id, sourceType, instanceId) {
            const formData = new FormData();
            formData.append('action', 'restore_reminder');
            formData.append('id', id);
            formData.append('source_type', sourceType || 'reminder');
            if (instanceId) {
                formData.append('instance_id', instanceId);
            }

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', data.message, 'success');
                        // Refresh calendar
                        if (reminderCalendar) {
                            fetchRemindersForCalendar().then(events => {
                                reminderCalendar.removeAllEvents();
                                reminderCalendar.addEventSource(events);
                            });
                        }
                    } else {
                        showToast('Error!', data.message, 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Something went wrong!', 'danger');
                });
        }
    </script>
    <script>
        // Settings form handling
        document.getElementById('reminderSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'save_reminder_settings');

            // Convert checkboxes to 1/0 values
            const checkboxes = ['morning_summary_enabled', 'evening_progress_enabled', 'due_reminders_enabled', 'send_empty_summaries'];
            checkboxes.forEach(checkbox => {
                const element = document.getElementById(checkbox);
                formData.set(checkbox, element.checked ? '1' : '0');
            });

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success!', 'Settings saved successfully!', 'success');
                    } else {
                        showToast('Error!', data.message || 'Failed to save settings', 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error!', 'Something went wrong!', 'danger');
                });
        });

        // Reset settings
        document.getElementById('resetSettingsBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                const formData = new FormData();
                formData.append('action', 'reset_reminder_settings');

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Success!', 'Settings reset to defaults!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Error!', 'Failed to reset settings', 'danger');
                        }
                    });
            }
        });

        // Load settings on page load
        function loadReminderSettings() {
            const formData = new FormData();
            formData.append('action', 'get_reminder_settings');

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const settings = data.settings;

                        // Set checkbox values
                        document.getElementById('morning_summary_enabled').checked = settings.morning_summary_enabled === '1';
                        document.getElementById('evening_progress_enabled').checked = settings.evening_progress_enabled === '1';
                        document.getElementById('due_reminders_enabled').checked = settings.due_reminders_enabled === '1';
                        document.getElementById('send_empty_summaries').checked = settings.send_empty_summaries === '1';

                        // Set other values
                        document.getElementById('notification_email').value = settings.notification_email || '';
                        document.getElementById('email_format').value = settings.email_format || 'html';
                    }
                });
        }

        // Load settings when settings tab is shown
        document.getElementById('settings-tab').addEventListener('shown.bs.tab', function() {
            loadReminderSettings();
        });
    </script>
    <script>
        // System status variables
        let statusUpdateInterval;
        let isStatusTabActive = false;

        // Initialize system status when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadSystemStatus();

            // Auto-refresh every 30 seconds
            statusUpdateInterval = setInterval(function() {
                if (isStatusTabActive) {
                    loadSystemStatus();
                }
            }, 30000);
        });

        // Load system status
        function loadSystemStatus() {
            const formData = new FormData();
            formData.append('action', 'get_system_status');

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatusDisplay(data.status);
                    } else {
                        console.error('Failed to load system status:', data.message);
                        showErrorStatus();
                    }
                })
                .catch(error => {
                    console.error('Error loading system status:', error);
                    showErrorStatus();
                });
        }

        // Update status display
        function updateStatusDisplay(status) {
            // Update email service status
            updateStatusComponent('email', status.email_service);

            // Update database status
            updateStatusComponent('db', status.database);

            // Update last email status
            const lastEmailIcon = document.getElementById('lastEmailIcon');
            const lastEmailText = document.getElementById('lastEmailText');
            const lastEmailTime = document.getElementById('lastEmailTime');

            lastEmailIcon.className = `fas fa-circle text-${getStatusColor(status.last_email.status)}`;
            lastEmailText.textContent = status.last_email.message;
            lastEmailTime.textContent = status.last_email.timestamp ?
                new Date(status.last_email.timestamp).toLocaleString() : 'Never';

            // Update active reminders
            const activeRemindersIcon = document.getElementById('activeRemindersIcon');
            const activeRemindersText = document.getElementById('activeRemindersText');
            const activeRemindersCount = document.getElementById('activeRemindersCount');

            activeRemindersIcon.className = `fas fa-circle text-${status.active_reminders.count > 0 ? 'info' : 'secondary'}`;
            activeRemindersText.textContent = status.active_reminders.message;
            activeRemindersCount.textContent = status.active_reminders.count + ' total';

            // Update system health
            const healthBadge = document.getElementById('healthBadge');
            const healthProgressBar = document.getElementById('healthProgressBar');
            const lastSystemUpdate = document.getElementById('lastSystemUpdate');
            const systemStatusIcon = document.getElementById('systemStatusIcon');

            const healthScore = status.system_health.score;
            const healthStatus = status.system_health.status;

            healthProgressBar.style.width = healthScore + '%';
            healthProgressBar.setAttribute('aria-valuenow', healthScore);

            // Set progress bar color
            healthProgressBar.className = 'progress-bar';
            if (healthScore >= 75) {
                healthProgressBar.classList.add('bg-success');
                healthBadge.className = 'badge bg-success';
                systemStatusIcon.className = 'fas fa-server text-success me-2';
            } else if (healthScore >= 50) {
                healthProgressBar.classList.add('bg-warning');
                healthBadge.className = 'badge bg-warning';
                systemStatusIcon.className = 'fas fa-server text-warning me-2';
            } else {
                healthProgressBar.classList.add('bg-danger');
                healthBadge.className = 'badge bg-danger';
                systemStatusIcon.className = 'fas fa-server text-danger me-2';
            }

            healthBadge.textContent = Math.round(healthScore) + '% - ' + status.system_health.message;
            lastSystemUpdate.textContent = 'Last updated: ' + new Date().toLocaleString();
        }

        // Update individual status component
        function updateStatusComponent(component, statusData) {
            const icon = document.getElementById(component + 'StatusIcon');
            const text = document.getElementById(component + 'StatusText');
            const lastCheck = document.getElementById(component + 'LastCheck');

            if (icon && text && lastCheck) {
                icon.className = `fas fa-circle text-${getStatusColor(statusData.status)}`;
                text.textContent = statusData.message;
                lastCheck.textContent = 'Last check: ' + new Date(statusData.last_check).toLocaleString();
            }
        }

        // Get status color based on status
        function getStatusColor(status) {
            switch (status) {
                case 'online':
                case 'configured':
                case 'working':
                case 'success':
                    return 'success';
                case 'warning':
                    return 'warning';
                case 'offline':
                case 'failed':
                case 'critical':
                    return 'danger';
                case 'active':
                    return 'info';
                default:
                    return 'secondary';
            }
        }

        // Show error status when loading fails
        function showErrorStatus() {
            const components = ['email', 'db', 'lastEmail', 'activeReminders'];

            components.forEach(component => {
                const icon = document.getElementById(component + 'StatusIcon') || document.getElementById(component + 'Icon');
                const text = document.getElementById(component + 'StatusText') || document.getElementById(component + 'Text');

                if (icon && text) {
                    icon.className = 'fas fa-circle text-secondary';
                    text.textContent = 'Error loading';
                }
            });

            const healthBadge = document.getElementById('healthBadge');
            const systemStatusIcon = document.getElementById('systemStatusIcon');

            if (healthBadge) healthBadge.textContent = 'Error loading status';
            if (systemStatusIcon) systemStatusIcon.className = 'fas fa-server text-secondary me-2';
        }

        // Refresh system status manually
        function refreshSystemStatus() {
            // Show loading state
            const refreshBtn = event.target.closest('button');
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
            refreshBtn.disabled = true;

            loadSystemStatus();

            // Reset button after 2 seconds
            setTimeout(() => {
                refreshBtn.innerHTML = originalContent;
                refreshBtn.disabled = false;
            }, 2000);
        }

        // Test email service
        function testEmailService() {
            const formData = new FormData();
            formData.append('action', 'test_email_service');

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Email Test', data.message, data.status === 'working' ? 'success' : 'warning');
                        // Refresh status after test
                        setTimeout(loadSystemStatus, 1000);
                    } else {
                        showToast('Email Test Failed', data.message, 'danger');
                    }
                })
                .catch(error => {
                    showToast('Error', 'Failed to test email service', 'danger');
                });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Activate tab based on URL hash on page load
            const hash = window.location.hash;
            if (hash) {
                const tabLink = document.querySelector(`a[href="${hash}"]`);
                if (tabLink) {
                    const tab = new bootstrap.Tab(tabLink);
                    tab.show();
                }
            }

            // Update URL hash when a tab is clicked
            const tabLinks = document.querySelectorAll('#reminderTabs a.nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('shown.bs.tab', function (event) {
                    history.replaceState(null, null, event.target.getAttribute('href'));
                });
            });
        });
    </script>

<?php include "footer.php"; ?>