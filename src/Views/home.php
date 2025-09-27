<?php
$title = 'Welcome';
ob_start();
?>
<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Budgeting Platform</h1>
        <p class="col-md-8 fs-4">This is a starter scaffold for the budgeting and order management platform. Build your modules and controllers under the src directory.</p>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
