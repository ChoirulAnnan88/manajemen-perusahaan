<!DOCTYPE html>
<html>
<head>
    <title>Error - Application Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #d9534f; }
        .debug { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Application Error</h1>
    <div class="debug">
        <p><strong>Message:</strong> <?= esc($message) ?></p>
        <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE): ?>
            <p><strong>File:</strong> <?= esc($exception->getFile()) ?></p>
            <p><strong>Line:</strong> <?= esc($exception->getLine()) ?></p>
            <p><strong>Trace:</strong></p>
            <pre><?= esc($exception->getTraceAsString()) ?></pre>
        <?php endif ?>
    </div>
</body>
</html>