<?php
session_start();
session_destroy();
echo "<script>
       location.href='/liberty/index.php';
    </script>";
