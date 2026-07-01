<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

$error = '';

if (isset($_POST['logout'])) {
    adminLogout();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_user'], $_POST['login_pass'])) {
    $username = trim((string) $_POST['login_user']);
    $password = (string) $_POST['login_pass'];

    if (
        defined('ADMIN_USERNAME') &&
        defined('ADMIN_PASSWORD') &&
        hash_equals(ADMIN_USERNAME, $username) &&
        hash_equals(ADMIN_PASSWORD, $password)
    ) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid username or password.';
}

if (!adminIsLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admin Login | Green Torque</title>
      <link rel="stylesheet" href="../css/style.css">
      <link rel="stylesheet" href="admin.css">
    </head>
    <body class="admin-page">
      <main class="admin-login">
        <form class="admin-card" method="post">
          <h1>Green Torque Admin</h1>
          <p>Sign in to view appointment requests.</p>
          <?php if ($error !== '') : ?>
            <p class="admin-alert admin-alert--error"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>
          <label>
            Username
            <input type="text" name="login_user" required autocomplete="username">
          </label>
          <label>
            Password
            <input type="password" name="login_pass" required autocomplete="current-password">
          </label>
          <button type="submit" class="btn btn--primary btn--full">Sign In</button>
        </form>
      </main>
    </body>
    </html>
    <?php
    exit;
}

requireAdminLogin();

require __DIR__ . '/../api/db.php';

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $appointmentId = (int) $_POST['appointment_id'];
    $status = trim((string) $_POST['status']);
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    if ($appointmentId > 0 && in_array($status, $allowedStatuses, true)) {
        try {
            $statement = createPdo()->prepare('UPDATE appointments SET status = :status WHERE id = :id');
            $statement->execute([
                'status' => $status,
                'id' => $appointmentId,
            ]);
            $notice = 'Appointment #' . $appointmentId . ' updated to ' . $status . '.';
        } catch (Throwable $exception) {
            $error = 'Unable to update appointment status.';
        }
    }
}

try {
    $appointments = createPdo()
        ->query('SELECT * FROM appointments ORDER BY preferred_date ASC, preferred_time ASC, created_at DESC')
        ->fetchAll();
} catch (Throwable $exception) {
    $appointments = [];
    $error = 'Unable to load appointments. Check database setup and api/config.php.';
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointments | Green Torque Admin</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <div class="admin-header__inner">
      <div>
        <h1>Appointment Dashboard</h1>
        <p>View and manage booking requests from greentorqueev.com</p>
      </div>
      <form method="post">
        <button type="submit" name="logout" value="1" class="btn btn--outline">Log Out</button>
      </form>
    </div>
  </header>

  <main class="admin-main container">
    <?php if ($notice !== '') : ?>
      <p class="admin-alert admin-alert--success"><?= h($notice) ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
      <p class="admin-alert admin-alert--error"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if (empty($appointments)) : ?>
      <section class="admin-card">
        <p>No appointments yet. When customers submit the booking form, requests will appear here.</p>
      </section>
    <?php else : ?>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Vehicle</th>
              <th>Service</th>
              <th>Preferred</th>
              <th>Status</th>
              <th>Notes</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $appointment) : ?>
              <?php
                $serviceText = serviceLabel($appointment['service']);
                if (!empty($appointment['hybrid_service'])) {
                    $serviceText .= ' / ' . hybridServiceLabel($appointment['hybrid_service']);
                }
              ?>
              <tr>
                <td>#<?= (int) $appointment['id'] ?></td>
                <td>
                  <strong><?= h($appointment['customer_name']) ?></strong><br>
                  <a href="mailto:<?= h($appointment['email']) ?>"><?= h($appointment['email']) ?></a><br>
                  <a href="tel:<?= h($appointment['phone']) ?>"><?= h($appointment['phone']) ?></a>
                </td>
                <td><?= h($appointment['vehicle_year'] . ' ' . $appointment['vehicle_make'] . ' ' . $appointment['vehicle_model']) ?></td>
                <td><?= h($serviceText) ?></td>
                <td>
                  <?= h($appointment['preferred_date']) ?><br>
                  <?= h(formatTimeLabel(substr($appointment['preferred_time'], 0, 5))) ?>
                </td>
                <td><span class="admin-status admin-status--<?= h($appointment['status']) ?>"><?= h($appointment['status']) ?></span></td>
                <td><?= h($appointment['notes'] !== '' ? $appointment['notes'] : '—') ?></td>
                <td>
                  <form method="post" class="admin-inline-form">
                    <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                    <select name="status">
                      <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $statusOption) : ?>
                        <option value="<?= h($statusOption) ?>" <?= $appointment['status'] === $statusOption ? 'selected' : '' ?>>
                          <?= h($statusOption) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn--primary">Save</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
