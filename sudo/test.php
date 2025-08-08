<?php
require_once 'reminder_functions.php';

echo "=== REMINDER SYSTEM TEST SCRIPT ===\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Basic email functionality
echo "1. Testing basic email functionality...\n";
function testBasicEmail() {
    global $email_config;

    $subject = "Test Email - " . date('Y-m-d H:i:s');
    $content = "<h2>✅ Email System Test</h2><p>If you receive this, your email configuration is working correctly!</p>";
    $htmlBody = getModernEmailTemplate($subject, $content);

    $result = sendEmail($email_config['to_email'], $subject, $htmlBody);

    if ($result) {
        echo "   ✅ Basic email test: SUCCESS\n";
    } else {
        echo "   ❌ Basic email test: FAILED\n";
    }

    return $result;
}

// Test 2: Database connection and reminder queries
echo "\n2. Testing database connection and queries...\n";
function testDatabaseQueries() {
    global $dbh;

    try {
        // Test basic connection
        $stmt = $dbh->query("SELECT COUNT(*) as total FROM reminders");
        $result = $stmt->fetch();
        echo "   ✅ Database connection: SUCCESS\n";
        echo "   📊 Total reminders in database: " . $result['total'] . "\n";

        // Test today's reminders
        $today = date('Y-m-d');
        $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM reminders WHERE reminder_date = ? AND is_completed = 0");
        $stmt->execute([$today]);
        $todayCount = $stmt->fetch()['count'];
        echo "   📅 Today's incomplete reminders: " . $todayCount . "\n";

        // Test overdue reminders
        $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM reminders WHERE reminder_date < ? AND is_completed = 0 AND is_dismissed = 0");
        $stmt->execute([$today]);
        $overdueCount = $stmt->fetch()['count'];
        echo "   ⚠️  Overdue reminders: " . $overdueCount . "\n";

        // Show some sample reminders
        $stmt = $dbh->prepare("SELECT title, reminder_date, reminder_time, priority, is_completed FROM reminders ORDER BY reminder_date DESC, reminder_time DESC LIMIT 5");
        $stmt->execute();
        $samples = $stmt->fetchAll();

        if (!empty($samples)) {
            echo "   📝 Sample reminders:\n";
            foreach ($samples as $reminder) {
                $status = $reminder['is_completed'] ? '✅' : '⏳';
                echo "      $status " . $reminder['title'] . " - " . $reminder['reminder_date'] . " " . $reminder['reminder_time'] . " (" . $reminder['priority'] . ")\n";
            }
        }

        return true;
    } catch (Exception $e) {
        echo "   ❌ Database test: FAILED - " . $e->getMessage() . "\n";
        return false;
    }
}

// Test 3: Force morning summary (regardless of time)
echo "\n3. Testing morning summary email (forced)...\n";
function testMorningSummary() {
    $result = sendMorningSummaryEmail();
    if ($result) {
        echo "   ✅ Morning summary email: SENT\n";
    } else {
        echo "   ❌ Morning summary email: FAILED (might be no reminders to send)\n";
    }
    return $result;
}

// Test 4: Force evening progress (regardless of time)
echo "\n4. Testing evening progress email (forced)...\n";
function testEveningProgress() {
    $result = sendEveningProgressEmail();
    if ($result) {
        echo "   ✅ Evening progress email: SENT\n";
    } else {
        echo "   ❌ Evening progress email: FAILED (might be no reminders to send)\n";
    }
    return $result;
}

// Test 5: Check due reminders (current time window)
echo "\n5. Testing due reminder checks...\n";
function testDueReminders() {
    echo "   🔍 Checking for due reminders in current 5-minute window...\n";

    // Capture output from checkAndSendDueReminders
    ob_start();
    checkAndSendDueReminders();
    $output = ob_get_clean();

    if (empty($output)) {
        echo "   ℹ️  No due reminders found in current time window\n";
    } else {
        echo "   📧 Due reminder output:\n";
        echo "      " . str_replace("\n", "\n      ", trim($output)) . "\n";
    }

    return true;
}

// Test 6: Create a test reminder for immediate testing
echo "\n6. Creating test reminder for immediate notification...\n";
function createTestReminder() {
    global $dbh;

    try {
        // Create a reminder due in 1 minute
        $testTime = date('Y-m-d H:i:s', strtotime('+1 minute'));
        $testDate = date('Y-m-d', strtotime('+1 minute'));
        $testTimeOnly = date('H:i:s', strtotime('+1 minute'));

        $stmt = $dbh->prepare("INSERT INTO reminders (title, description, reminder_date, reminder_time, priority, category, is_recurring, is_completed, is_dismissed, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, NOW())");

        $result = $stmt->execute([
            'TEST REMINDER - Delete Me',
            'This is a test reminder created by the test script. You can safely delete this.',
            $testDate,
            $testTimeOnly,
            'high',
            'test'
        ]);

        if ($result) {
            echo "   ✅ Test reminder created for: " . date('H:i:s', strtotime('+1 minute')) . "\n";
            echo "   ⏰ Wait 1 minute and run due reminder check to test notifications\n";
            return $dbh->lastInsertId();
        } else {
            echo "   ❌ Failed to create test reminder\n";
            return false;
        }
    } catch (Exception $e) {
        echo "   ❌ Error creating test reminder: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run all tests
$results = [];
$results['basic_email'] = testBasicEmail();
$results['database'] = testDatabaseQueries();
$results['morning_summary'] = testMorningSummary();
$results['evening_progress'] = testEveningProgress();
$results['due_reminders'] = testDueReminders();
$results['test_reminder'] = createTestReminder();

// Summary
echo "\n=== TEST SUMMARY ===\n";
foreach ($results as $test => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "$status - " . str_replace('_', ' ', ucwords($test, '_')) . "\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Check your email inbox for test messages\n";
echo "2. Wait 1 minute and run: php -r \"require_once 'reminder_functions.php'; checkAndSendDueReminders();\" to test the due reminder\n";
echo "3. Check php-errors.log for any error messages\n";
echo "4. If tests pass, set up your cron jobs\n";

?>