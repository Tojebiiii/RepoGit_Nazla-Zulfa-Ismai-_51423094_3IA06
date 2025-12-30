<?php
include 'koneksi.php';

$message = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    
.
    $isi = mysqli_real_escape_string($koneksi, $_POST['isi']); 

    $tags_input = isset($_POST['tags']) ? mysqli_real_escape_string($koneksi, $_POST['tags']) : '';
    $bg_color = mysqli_real_escape_string($koneksi, $_POST['background_color']);
 
    $img_url = mysqli_real_escape_string($koneksi, $_POST['illustration_url']); 

    if (!empty($judul) && !empty($isi)) {
      
        $query_entri = "INSERT INTO entri (judul, isi, background_color, illustration_url) 
                         VALUES ('$judul', '$isi', '$bg_color', '$img_url')";
        
        if (mysqli_query($koneksi, $query_entri)) {
            $last_id = mysqli_insert_id($koneksi); 
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
                    $query_link = "INSERT INTO entri_tag (entri_id, tag_id) VALUES ($last_id, $tag_id)";
                    mysqli_query($koneksi, $query_link);
                }
            }
            header("Location: lihat_entri.php?id=$last_id");
            exit();
        } else {
            
            $message = "Error menyimpan entri: " . mysqli_error($koneksi);
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
    <title>✍️ My Daily Life (ADMIN VIEW)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .color-group { display: flex; align-items: center; }
        .color-group label { margin-right: 15px; }
    </style>
    
    <script src="https://cdn.tiny.cloud/1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#isi',
            plugins: 'advlist autolink lists link image charmap print preview anchor ' +
                     'searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | ' +
                     'bold italic backcolor | alignleft aligncenter ' +
                     'alignright alignjustify | bullist numlist outdent indent | ' +
                     'removeformat | help | image media', 
            
         
            image_title: true,
            automatic_uploads: false, 
           
            
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>✍️ My Daily Life</h1>
        <p><a href="index.php" class="link-kembali">← HomePage</a></p>
        
        <?php if (!empty($message)): ?>
            <p style="color: red; margin-bottom: 15px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="judul">Bagaimana Hari Kamu?</label>
            <input type="text" id="judul" name="judul" required>

            <label for="isi">Ceritakan Padaku:</label>
            <textarea id="isi" name="isi" required></textarea> 

            <label for="tags">Tags (Pisahkan dengan koma, Gunakan huruf kecil)</label>
            <input type="text" id="tags" name="tags" placeholder="cth: ide_proyek, perjalanan, mood_baik">

            <div class="color-group">
                <label for="background_color">Background</label>
                <input type="color" id="background_color" name="background_color" value="#FFFFFF">
            </div>

            <label for="illustration_url">Add URL Image (Untuk Header)</label>
            <input type="text" id="illustration_url" name="illustration_url" placeholder="cth: https://contoh.com/gambar.jpg">

            <button type="submit">SAVE</button>
        </form>
    </div>
</body>

</html>
