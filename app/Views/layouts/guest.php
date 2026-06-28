<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Operis', ENT_QUOTES) ?></title>
    <link rel="stylesheet" href="/assets/css/theme.min.css">
    <link rel="stylesheet" href="/assets/css/user.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <?= $content ?>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
