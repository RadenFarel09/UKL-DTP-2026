<?php
session_start();
session_unset(); # Buat menghapus seluruh variabel session yang tersimpan, sehingga user dianggap logout
session_destroy(); # Buat menghancrkan sesi secara total di server
header("Location: login.php?pesan=logout");
exit;
