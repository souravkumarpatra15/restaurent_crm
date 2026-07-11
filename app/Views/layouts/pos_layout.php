<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title><?= $title ?? 'POS' ?> — RestOne</title>
<meta name="csrf-token" data-name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
<meta name="theme-color" content="#0F172A">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('css/pos.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/main.css') ?>">
<script src="<?= base_url('js/main.js') ?>"></script>
<link rel="icon" href="<?= base_url('images/favicon.png') ?>">
</head>
<body><?= $this->renderSection('content') ?></body>
</html>
