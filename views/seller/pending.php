<?php
$page_title = 'BookHaven - Seller Account Pending';
include_once BASE_PATH . '/views/includes/header.php';
?>

<div class="card text-center">
    <div class="card-body p-5">
        <i class="fas fa-clock text-warning mb-4" style="font-size: 5rem;"></i>
        <h2 class="card-title mb-3">Your Account is Pending Approval</h2>
        <p class="mb-4">تم إنشاء حسابك بنجاح، ولكن يجب انتظار موافقة المسؤول قبل أن تتمكن من البيع.</p>
        <p class="mb-4">Your account has been created successfully, but you must wait for administrator approval before you can sell on BookHaven.</p>
        <a href="index.php" class="btn btn-primary">
            Return to Homepage
        </a>
    </div>
</div>

<?php include_once BASE_PATH . '/views/includes/footer.php'; ?>