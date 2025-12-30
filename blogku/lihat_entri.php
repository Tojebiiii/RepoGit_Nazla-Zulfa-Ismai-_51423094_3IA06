
<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id_entri = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $query = "SELECT judul, isi, tanggal, background_color, illustration_url FROM entri WHERE id = $id_entri";
    $result = mysqli_query($koneksi, $query);
    $entri = mysqli_fetch_assoc($result);

    $tags_query = "SELECT t.id, t.nama 
                   FROM tag t 
                   JOIN entri_tag et ON t.id = et.tag_id 
                   WHERE et.entri_id = $id_entri";
    $tags_result = mysqli_query($koneksi, $tags_query);

} else {
    header("Location: index.php");
    exit();
}

$bg_color = $entri['background_color'] ?? '#FFFFFF';
$img_url = $entri['illustration_url'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $entri ? htmlspecialchars($entri['judul']) : 'Entri Tidak Ditemukan'; ?> | Diary</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        .container { 
            background-color: <?php echo htmlspecialchars($bg_color); ?>; 
        }
        .isi-entri img { max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; display: block; }
        .action-buttons { margin-top: 30px; border-top: 1px solid #F8BBD0; padding-top: 15px; }
        .action-buttons a.edit { background-color: #FF69B4; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; margin-right: 10px; font-weight: bold; }
        .action-buttons button.delete { background-color: #f44336; color: white; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($entri): ?>
            <p><a href="index.php" class="link-kembali">← HomePage</a></p>
            <h1><?php echo htmlspecialchars($entri['judul']); ?></h1>
            <span class="tanggal">
                Diposting pada: <?php echo date("l, d F Y, H:i", strtotime($entri['tanggal'])); ?> WIB
            </span>

            <?php if (!empty($img_url)): ?>
                <div class="illustration">
                    <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Ilustrasi Entri" />
                </div>
            <?php endif; ?>
            
            <div class="tags-section">
                <strong>Tags:</strong> 
                <?php if (mysqli_num_rows($tags_result) > 0): ?>
                    <?php while ($tag = mysqli_fetch_assoc($tags_result)): ?>
                        <span>
                            <a href="index.php?tag_id=<?php echo $tag['id']; ?>">
                                #<?php echo htmlspecialchars($tag['nama']); ?>
                            </a>
                        </span>
                    <?php endwhile; ?>
                <?php else: ?>
                    <span>Tidak ada tag</span>
                <?php endif; ?>
            </div>
            
            <hr>

            <div class="isi-entri">
                <?php 
                echo $entri['isi']; 
                ?>
            </div>

            <div class="action-buttons">
                <a href="edit_entri.php?id=<?php echo $id_entri; ?>" class="edit">✏️ Edit Diary</a>
                
                <form method="POST" action="hapus_entri.php" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus diary ini secara permanen?');">
                    <input type="hidden" name="id" value="<?php echo $id_entri; ?>">
                    <button type="submit" class="delete">🗑️ Hapus</button>
                </form>
            </div>
            <?php else: ?>
            <h1>Diary tidak ditemukan</h1>
            <p>Maaf, diary harian yang kamu cari tidak ditemukan</p>
            <p><a href="index.php" class="link-kembali">← HomePage</a></p>
        <?php endif; ?>
    </div>
</body>
</html>