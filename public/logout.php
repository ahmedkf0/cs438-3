<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php"); // إعادة توجيه إلى صفحة الفعاليات بعد تسجيل الخروج
exit();
?>
