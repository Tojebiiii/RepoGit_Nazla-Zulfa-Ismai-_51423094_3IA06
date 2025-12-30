<?php

include 'koneksi.php';

$message = ''; 
$entri = null;
$tags_sekarang = '';
$entri_id = null;


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $entri_id = (int)$_GET['id'];
    $query = "SELECT judul, isi, background_color, illustration_url FROM entri WHERE id = $entri_id";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $entri = mysqli_fetch_assoc($result);

      
        $tags_query = "SELECT t.nama FROM tag t JOIN entri_tag et ON t.id = et.tag_id WHERE et.entri_id = $entri_id";
        $tags_result = mysqli_query($koneksi, $tags_query);
        $tag_names = [];
        while ($tag = mysqli_fetch_assoc($tags_result)) {
            $tag_names[] = $tag['nama'];
        }
        $tags_sekarang = implode(', ', $tag_names);

    } else {
        header("Location: index.php");
        exit();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_entri'])) {
    $entri_id = mysqli_real_escape_string($koneksi, $_POST['id_entri']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi = mysqli_real_escape_string($koneksi, $_POST['isi']); // TinyMCE HTML
    $tags_input = isset($_POST['tags']) ? mysqli_real_escape_string($koneksi, $_POST['tags']) : '';
    $bg_color = mysqli_real_escape_string($koneksi, $_POST['background_color']);
    $img_url = mysqli_real_escape_string($koneksi, $_POST['illustration_url']);

    if (!empty($judul) && !empty($isi)) {
        $query_update = "UPDATE entri SET 
                            judul = '$judul', 
                            isi = '$isi', 
                            background_color = '$bg_color', 
                            illustration_url = '$img_url'
                         WHERE id = $entri_id";
        
        if (mysqli_query($koneksi, $query_update)) {
            
            mysqli_query($koneksi, "DELETE FROM entri_tag WHERE entri_id = $entri_id");

         
            if (!empty($tags_input)) {
                $tags_array = array_map('trim', explode(',', $tags_input));
                foreach ($tags_array as $tag_name) {
                    $tag_name = strtolower($tag_name);
                    if (empty($tag_name)) continue;
                    
                   
                    $query_check_tag = "SELECT id FROM tag WHERE nama = '$tag_name'";
                    $result_check = mysqli_query($koneksi, $query_check_tag);
                    
                    if (mysqli_num_rows($result_check) > 0) {
                        $row = mysqli_fetch_assoc($result_check);
                        $tag_id = $row['id'];
                    } else {
                        
                        $query_insert_tag = "INSERT INTO tag (nama) VALUES ('$tag_name')";
                        mysqli_query($koneksi, $query_insert_tag);
                        $tag_id = mysqli_insert_id($koneksi);
                    }
                    
                    // Link tag ke entri
                    $query_link = "INSERT INTO entri_tag (entri_id, tag_id) VALUES ($entri_id, $tag_id)";
                    mysqli_query($koneksi, $query_link);
                }
            }
            
            header("Location: lihat_entri.php?id=$entri_id&status=updated");
            exit();
        } else {
            $message = "Error mengupdate dairy: " . mysqli_error($koneksi);
        }
    } else {
        $message = "Judul dan Isi tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Edit Diary Daily</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .color-group { display: flex; align-items: center; }
        .color-group label { margin-right: 15px; }
    </style>
    
    <script src="https://cdn.tiny.cloud/1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea', 
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | image media', 
            image_title: true,
            automatic_uploads: false,
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>✏️ Edit Dairy </h1>
        <p><a href="lihat_entri.php?id=<?php echo $entri_id; ?>" class="link-kembali">← HomePage</a></p>
        
        <?php if (!empty($message)): ?>
            <p style="color: red; margin-bottom: 15px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($entri): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="id_entri" value="<?php echo $entri_id; ?>">

                <label for="judul">Bagaiman Perasaan Kamu Hari ini?</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($entri['judul']); ?>" required>

                <label for="isi">Ceritakan Kepadaku</label>
                <textarea id="isi" name="isi" required><?php echo $entri['isi']; ?></textarea> 

                <label for="tags">Tags (Pisahkan dengan koma, Gunakan huruf kecil)</label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($tags_sekarang); ?>" placeholder="cth: ide_proyek, perjalanan, mood_baik">

                <div class="color-group">
                    <label for="background_color">Background</label>
                    <input type="color" id="background_color" name="background_color" value="<?php echo htmlspecialchars($entri['background_color']); ?>">
                </div>

                <label for="illustration_url">URL Gambar (Optional)</label>
                <input type="text" id="illustration_url" name="illustration_url" value="<?php echo htmlspecialchars($entri['illustration_url']); ?>" placeholder="cth: https://contoh.com/gambar.jpg">

                <button type="submit">SAVE CHANGE</button>
            </form>
        <?php else: ?>
             <p>Diary tidak ditemukan</p>
        <?php endif; ?>
    </div>
</body>

</html>
