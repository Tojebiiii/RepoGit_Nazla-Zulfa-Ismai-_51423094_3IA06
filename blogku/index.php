<?php
include 'koneksi.php';

$search_query = "";
$filter_tag_id = null;
$filter_month = null;
$filter_year = null;
$where_clause = "";
$params = [];


if (isset($_GET['month']) && is_numeric($_GET['month']) && isset($_GET['year']) && is_numeric($_GET['year'])) {
    $filter_month = (int)$_GET['month'];
    $filter_year = (int)$_GET['year'];
    
    
    $where_clause .= " WHERE MONTH(e.tanggal) = $filter_month AND YEAR(e.tanggal) = $filter_year";
    $params['month'] = $filter_month;
    $params['year'] = $filter_year;
}


if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = mysqli_real_escape_string($koneksi, $_GET['search']);
    $prefix = empty($where_clause) ? " WHERE " : " AND ";
    $where_clause .= $prefix . "(e.judul LIKE '%$search_query%' OR e.isi LIKE '%$search_query%')";
}

if (isset($_GET['tag_id']) && is_numeric($_GET['tag_id'])) {
    $filter_tag_id = (int)$_GET['tag_id'];
    $prefix = empty($where_clause) ? " WHERE " : " AND ";
    $where_clause .= $prefix . "e.id IN (SELECT entri_id FROM entri_tag WHERE tag_id = $filter_tag_id)";
}

$query = "SELECT e.id, e.judul, LEFT(e.isi, 200) AS isi_singkat, e.tanggal 
          FROM entri e $where_clause 
          ORDER BY e.tanggal DESC";
$result = mysqli_query($koneksi, $query);

$tags_query = "SELECT id, nama FROM tag ORDER BY nama ASC";
$tags_result = mysqli_query($koneksi, $tags_query);


$archive_query = "SELECT DISTINCT YEAR(tanggal) AS tahun, MONTH(tanggal) AS bulan 
                  FROM entri 
                  ORDER BY tahun DESC, bulan DESC";
$archive_result = mysqli_query($koneksi, $archive_query);


$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏡 Diary Digital Publik - Kumpulan Entri</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
   
        .sidebar { float: right; width: 250px; padding: 15px; background: #FFFAF0; border-left: 1px solid #F8BBD0; border-radius: 0 12px 12px 0; margin-left: 20px; }
        .main-content { margin-right: 270px; }
        .sidebar h3 { color: #FF69B4; margin-top: 0; padding-bottom: 5px; border-bottom: 1px solid #F8BBD0; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li a { display: block; padding: 5px 0; color: #333; text-decoration: none; transition: color 0.2s; }
        .sidebar ul li a:hover, .sidebar ul li a.active { color: #E91E63; font-weight: bold; }
        .add-button-container { text-align: right; margin-bottom: 20px; } /* Gaya tombol baru Anda */
    </style>
</head>
<body>
    <div class="container">
        
        <div class="sidebar">
            <h3>🗄️ Arsip Bulanan</h3>
            <ul>
                <li><a href="index.php" class="<?php echo (is_null($filter_month)) ? 'active' : ''; ?>">Lihat Semua</a></li>
                <?php while($archive = mysqli_fetch_assoc($archive_result)): ?>
                    <?php
                        $is_active = ($filter_month == $archive['bulan'] && $filter_year == $archive['tahun']);
                        $link_class = $is_active ? 'active' : '';
                        $month_name = $nama_bulan[(int)$archive['bulan']];
                    ?>
                    <li>
                        <a href="index.php?month=<?php echo $archive['bulan']; ?>&year=<?php echo $archive['tahun']; ?>" class="<?php echo $link_class; ?>">
                            <?php echo $month_name . " " . $archive['tahun']; ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
                </div> <div class="main-content">
            <h1>🏡 My Daily Life</h1>
            <div class="add-button-container">
                <a href="tambah_entri.php" class="add-button">
                    ✨ Tulis Diary Baru
                </a>
            </div>

            <div class="filter-area">
                <form method="GET" class="search-form">
                    <?php if ($filter_month && $filter_year): ?>
                        <input type="hidden" name="month" value="<?php echo $filter_month; ?>">
                        <input type="hidden" name="year" value="<?php echo $filter_year; ?>">
                    <?php endif; ?>
                    
                    <input type="text" name="search" placeholder="Cari di Judul atau Isi..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>
            
            <h3>Filter Berdasarkan Tag:</h3>
            <div class="tag-list">
                 <a href="index.php" class="<?php echo is_null($filter_tag_id) ? 'active' : ''; ?>">Semua</a>
                <?php while($tag = mysqli_fetch_assoc($tags_result)): ?>
                    <a href="index.php?tag_id=<?php echo $tag['id']; ?>" class="<?php echo ($filter_tag_id == $tag['id']) ? 'active' : ''; ?>">
                        #<?php echo htmlspecialchars($tag['nama']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
            <hr>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="entri">
                        <h2>
                            <a href="lihat_entri.php?id=<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['judul']); ?>
                            </a>
                        </h2>
                        <span class="tanggal">
                            <?php echo date("d F Y, H:i", strtotime($row['tanggal'])); ?> WIB
                        </span>
                        <p>
                            <?php echo htmlspecialchars($row['isi_singkat']); ?>... 
                            <a href="lihat_entri.php?id=<?php echo $row['id']; ?>">Baca Selengkapnya</a>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada diary yang ditemukan dengan kriteria saat ini.</p>
            <?php endif; ?>
        </div> </div>
</body>

</html>
