<?php
$koneksi = mysqli_connect("localhost", "root", "", "pwl_kasir_restoran");

// Fungsi Registrasi Akun
function register_akun() {
    global $koneksi;

    $username = htmlspecialchars($_POST["username"]);
    $password = md5(htmlspecialchars($_POST["password"]));
    $confirm_password = md5(htmlspecialchars($_POST["confirm_password"]));
    $role = htmlspecialchars($_POST["role"]);

    // Validasi kecocokan password
    if ($password != $confirm_password) {
        $_SESSION['error'] = "Password tidak cocok!";
        return -1;
    }

    // Cek apakah username sudah ada di salah satu tabel
    $check_user = mysqli_query($koneksi, "SELECT username FROM user WHERE username = '$username' 
                                         UNION 
                                         SELECT username FROM admin WHERE username = '$username'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $_SESSION['error'] = "Username sudah digunakan!";
        return -1;
    }

    // Masukkan ke tabel yang sesuai berdasarkan role
    if ($role == 'admin') {
        $query = "INSERT INTO admin (username, password) VALUES ('$username', '$password')";
    } else {
        $query = "INSERT INTO user (username, password) VALUES ('$username', '$password')";
    }

    mysqli_query($koneksi, $query);

    if (mysqli_error($koneksi)) {
        $_SESSION['error'] = "Registrasi gagal: " . mysqli_error($koneksi);
        return -1;
    }

    return mysqli_affected_rows($koneksi);
}

// Fungsi Login Akun
function login_akun() {
    global $koneksi;

    $username = htmlspecialchars($_POST["username"]);
    $password = md5(htmlspecialchars($_POST["password"]));

    // Cek di tabel admin
    $cek_admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin 
                                                      WHERE username = '$username' AND 
                                                            password = '$password'"));

    // Cek di tabel user
    $cek_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM user 
                                                     WHERE username = '$username' AND 
                                                           password = '$password'"));

    if ($cek_admin == null && $cek_user == null) {
        $_SESSION['error'] = "Username atau password salah!";
        return false;
    }

    if ($cek_user != null) {
        $_SESSION["akun-user"] = [
            "username" => $username,
            "role" => "user"
        ];
    }

    if ($cek_admin != null) {
        $_SESSION["akun-admin"] = [
            "username" => $username,
            "role" => "admin"
        ];
    }

    header("Location: index.php");
    exit();
}

// Fungsi Ambil Data
function ambil_data($query) {
    global $koneksi;

    $result = mysqli_query($koneksi, $query);
    $data = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

// Function Tambah Data

function tambah_data_menu()
{
    global $koneksi;

    $nama = htmlspecialchars($_POST["nama"]);
    $harga = (int) htmlspecialchars($_POST["harga"]);
    $gambar = $_FILES["gambar"]["name"];
    $kategori = htmlspecialchars($_POST["kategori"]);
    $status = htmlspecialchars($_POST["status"]);

    if (!$nama || !$harga || !$gambar || !$kategori || !$status) {
        echo "<script>alert('Form tidak lengkap');</script>";
        return -1;
    }

    $format_gambar = ["jpg", "jpeg", "png", "gif"];
    $cek_gambar = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

    if (!in_array($cek_gambar, $format_gambar)) {
        echo "<script>alert('File bukan gambar valid!');</script>";
        return -1;
    }

    $nama_gambar = uniqid() . ".$cek_gambar";
    if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], "src/img/$nama_gambar")) {
        echo "<script>alert('Upload gambar gagal!');</script>";
        return -1;
    }

    $max = ambil_data("SELECT MAX(SUBSTR(kode_menu, 3)) AS kode FROM menu")[0]["kode"];
    $kode_angka = $max ? (int)$max + 1 : 1;
    $kode_menu = "MN" . $kode_angka;

    $query = "INSERT INTO menu (kode_menu, nama, harga, gambar, kategori, status)
              VALUES ('$kode_menu', '$nama', $harga, '$nama_gambar', '$kategori', '$status')";

    mysqli_query($koneksi, $query);

    if (mysqli_error($koneksi)) {
        die("Query Error: " . mysqli_error($koneksi));
    }

    return mysqli_affected_rows($koneksi);
}




// Function Edit Data Menu

function edit_data_menu()

{

    global $koneksi;



    $id_menu = $_POST["id_menu"];

    $nama = htmlspecialchars($_POST["nama"]);

    $harga = (int) htmlspecialchars($_POST["harga"]);

    $gambar = htmlspecialchars($_FILES["gambar"]["name"]);

    $kategori = htmlspecialchars($_POST["kategori"]);

    $status = htmlspecialchars($_POST["status"]);

    $kode_menu = htmlspecialchars($_POST["kode_menu"]);



    // cek format gambar

    $format_gambar = ["jpg", "jpeg", "png", "gif"];

    $cek_gambar = explode(".", $gambar);

    $cek_gambar = strtolower(end($cek_gambar));

    if (!in_array($cek_gambar, $format_gambar) && strlen($gambar) != 0) {

        echo "<script>

            alert('File yang diupload bukan merupakan image!');

        </script>";

        return -1;
    }



    // cek jika admin mengupload gambar yang baru

    $gambar_lama = $_POST["gambar-lama"];



    if (strlen($gambar) == 0) {

        $gambar = $gambar_lama;
    } else if ($gambar != $gambar_lama && strlen($gambar) != 0) {

        move_uploaded_file($_FILES["gambar"]["tmp_name"], "src/img/$gambar");

        unlink("src/img/$gambar_lama");
    }



    // eksekusi query update

    mysqli_query($koneksi, "UPDATE menu

                            SET kode_menu = '$kode_menu',

                                nama = '$nama',

                                harga = $harga,

                                gambar = '$gambar',

                                kategori = '$kategori',

                                `status` = '$status'

                            WHERE id_menu = $id_menu

    ");

    return mysqli_affected_rows($koneksi);
}



// Function Hapus Data Menu

function hapus_data_menu()

{

    global $koneksi;



    $id_menu = $_GET["id_menu"];



    // hapus file gambar

    $file_gambar = ambil_data("SELECT * FROM menu WHERE id_menu = $id_menu")[0]["gambar"];

    if (file_exists("src/img/$file_gambar")) unlink("src/img/$file_gambar");



    // eksekusi query delete

    mysqli_query($koneksi, "DELETE FROM menu

                            WHERE id_menu = $id_menu

    ");

    return mysqli_affected_rows($koneksi);
}



// Tambah Data Pesanan & Transaksi

function tambah_data_pesanan()

{

    global $koneksi;



    // Nama Pelanggan

    $pelanggan = htmlspecialchars($_POST["pelanggan"]);

    // Generate Kode Pesanan

    $kode_pesanan = uniqid();



    // Mengambil Data Qty dan Kode Menu

    $list_pesanan = [];

    $max_menu = count(ambil_data("SELECT * FROM menu"));

    for ($i = 1; $i <= $max_menu; $i++) {

        if ((int) $_POST["qty$i"] != 0) {

            array_push($list_pesanan, [

                "kode_menu" => $_POST["kode_menu$i"],

                "qty" => (int) $_POST["qty$i"]

            ]);
        }
    }


    // Cek Jika Memesan Tapi Kosong
    if (count($list_pesanan) == 0) {
        echo "<script>
            alert('Anda belum memesan menu!');
        </script>";
        return -1;
    }

    // Tambah Data Pesanan

    foreach ($list_pesanan as $lp) {

        $kode_menu = $lp["kode_menu"];

        $qty = $lp["qty"];

        mysqli_query($koneksi, "INSERT INTO pesanan

                                VALUES ('', '$kode_pesanan', '$kode_menu', $qty);

        ");
    }



    // Tambah Data Transaksi

    mysqli_query($koneksi, "INSERT INTO transaksi

                            VALUES ('', '$kode_pesanan', '$pelanggan', NOW())

    ");

    return mysqli_affected_rows($koneksi);
}



// Hapus Data Pesanan & Transaksi

function hapus_data_pesanan()

{

    global $koneksi;



    $kode_pesanan = $_GET["kode_pesanan"];

    // eksekusi query delete

    mysqli_query($koneksi, "DELETE FROM transaksi

                            WHERE kode_pesanan = '$kode_pesanan'

    ");

    mysqli_query($koneksi, "DELETE FROM pesanan

                            WHERE kode_pesanan = '$kode_pesanan'

    ");

    return mysqli_affected_rows($koneksi);
}
