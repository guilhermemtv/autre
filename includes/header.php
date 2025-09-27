<?php
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autre - Painel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
    <aside class="app-sidebar" id="sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-2">
            <span class="fw-bold">Autre</span>
            <button class="btn btn-sm btn-outline-dark" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
<?php
