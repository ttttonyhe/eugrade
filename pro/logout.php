<?php
    session_start();
    if(isset($_SESSION['logged_in_id'])){
        unset($_SESSION['logged_in_id']);
        echo '<script>window.location.href="../login.html"</script>';
    }else{
        echo '<script>window.history.back(-1);</script>';
    }

?>