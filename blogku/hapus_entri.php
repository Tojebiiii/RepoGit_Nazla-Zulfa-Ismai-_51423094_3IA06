<?php

include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id_entri = mysqli_real_escape_string($koneksi, $_POST['id']);

    
    $query_delete_link = "DELETE FROM entri_tag WHERE entri_id = $id_entri";
    mysqli_query($koneksi, $query_delete_link);

    $query_delete_entri = "DELETE FROM entri WHERE id = $id_entri";

    if (mysqli_query($koneksi, $query_delete_entri)) {
        header("Location: index.php?status=deleted");
        exit();
    } else {
        echo "Tidak Bisa Menghapus " . mysqli_error($koneksi);
    }
} else {
    header("Location: index.php");
    exit();
}
?>