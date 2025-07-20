-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Jul 2025 pada 08.47
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_perumahan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kontak`
--

CREATE TABLE `tb_kontak` (
  `id_kontak` int(11) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `type_rumah` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pesan` text NOT NULL,
  `tanggal_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_rumah` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_kontak`
--

INSERT INTO `tb_kontak` (`id_kontak`, `nama`, `email`, `telepon`, `type_rumah`, `pesan`, `tanggal_kirim`, `id_rumah`) VALUES
(5, 'Zahira Putri Azahwa', 'zahiraazahwa123@gmail.com', '0895636788750', 'ARION', 'saya mau atur jadwal untuk melihat proyek ini', '2025-06-23 14:01:48', NULL),
(26, 'panjulll Destarani', 'putrya168@gmail.com', '0895636722417', 'ARION', 'kl', '2025-07-12 12:49:13', 7);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_login`
--

CREATE TABLE `tb_login` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(10) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','pimpinan') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_login`
--

INSERT INTO `tb_login` (`id_admin`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$IkLG4OYOWP2LEGD91uWAfOOrhYG5asNlZ8dFpwS/101RbbKIyHvM.', 'admin'),
(2, 'pimpinan', '$2y$10$h7ADIJPNA6nIKQdbuB2FJuPtRmXLYaCfsBRje0x1OsiHNARdpsmqG', 'pimpinan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pembayaran`
--

CREATE TABLE `tb_pembayaran` (
  `id_pembayaran` varchar(20) NOT NULL,
  `id_pembelian` varchar(50) NOT NULL,
  `nama_pembeli` varchar(50) NOT NULL,
  `status_pembelian` enum('terjual') NOT NULL DEFAULT 'terjual',
  `jenis_transaksi` enum('uang DP') NOT NULL DEFAULT 'uang DP',
  `tanggal_pembayaran` date NOT NULL,
  `jumlah_pembayaran` decimal(15,0) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `type_rumah` varchar(30) DEFAULT NULL,
  `blok_rumah` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_pembayaran`
--

INSERT INTO `tb_pembayaran` (`id_pembayaran`, `id_pembelian`, `nama_pembeli`, `status_pembelian`, `jenis_transaksi`, `tanggal_pembayaran`, `jumlah_pembayaran`, `bukti_pembayaran`, `type_rumah`, `blok_rumah`) VALUES
('PYM001', 'VV5', 'Amelia Putri Destarani', 'terjual', 'uang DP', '2025-07-11', 681000000, 'uploads/bukti_pembayaran/6870d21307bf4.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pembelian`
--

CREATE TABLE `tb_pembelian` (
  `id_pembelian` varchar(100) NOT NULL,
  `tanggal_pembelian` date NOT NULL,
  `nama_pembeli` varchar(50) NOT NULL,
  `type_rumah` varchar(30) NOT NULL,
  `blok_rumah` varchar(5) NOT NULL,
  `id_unit` int(11) UNSIGNED DEFAULT NULL,
  `status_pembelian` enum('Terbooking','Gagal Booking','terjual') NOT NULL,
  `id_rumah` int(11) UNSIGNED DEFAULT NULL,
  `no_ktp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_pembelian`
--

INSERT INTO `tb_pembelian` (`id_pembelian`, `tanggal_pembelian`, `nama_pembeli`, `type_rumah`, `blok_rumah`, `id_unit`, `status_pembelian`, `id_rumah`, `no_ktp`, `alamat`, `telepon`) VALUES
('VV1', '2025-05-01', 'Zahira putri azahwa', 'VOLANS', 'V1', 77, 'Gagal Booking', 1, '167119087907098', 'Jln. lunjuk', '082182521275'),
('VV3', '2025-06-10', 'Fardan maulana ilham mahendra', 'VOLANS', 'V3', 79, 'Terbooking', 1, '167119087908351', 'Jln. Sematang borang', '082182521098'),
('VV5', '2025-07-05', 'Amelia Putri Destarani', 'VOLANS', 'V5', 81, 'terjual', 1, '167119087900090', 'Jln. Brigjen H Hasan kasim', '0895636722417');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_rumah`
--

CREATE TABLE `tb_rumah` (
  `id_rumah` int(11) UNSIGNED NOT NULL,
  `type_rumah` varchar(30) NOT NULL,
  `harga_rumah` decimal(15,0) DEFAULT NULL,
  `foto_rumah` varchar(255) DEFAULT NULL,
  `site_plan` varchar(255) DEFAULT NULL,
  `floor_plan` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `spesifikasi_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tb_rumah`
--

INSERT INTO `tb_rumah` (`id_rumah`, `type_rumah`, `harga_rumah`, `foto_rumah`, `site_plan`, `floor_plan`, `deskripsi`, `spesifikasi_detail`, `id_admin`) VALUES
(1, 'VOLANS', 3405000000, 'uploads/683ce57cea41c_Cuplikan layar 2025-06-02 064221.png', 'uploads/683ce9c3167e6_Cuplikan layar 2025-05-19 103132.png', 'uploads/683ce50f292dd_Cuplikan layar 2025-06-02 064032.png', 'Rumah 2 lantai  yang megah dan mewah yang terletak di Cluster Hydra. Memiliki 4+1 Kamar tidur dengan toilet berada di dalam tiap-tiap kamar sangat cocok untuk keluarga besar.\r\n', 'LUAS BANGUNAN : 190 m2\r\nLUAS TANAH : 300 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : Aluminium ex.Stardec\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Jotun\r\nLANTAI: Granite Tile 80x80\r\nSANITAIR : ex. Toto\r\n', NULL),
(2, 'DORADO', 2208000000, 'uploads/683ce6e9673dc_Cuplikan layar 2025-06-02 064507.png', 'uploads/683ce9afe2b82_Cuplikan layar 2025-06-02 065951.png', 'uploads/683ce6e967960_Cuplikan layar 2025-06-02 064530.png', 'Rumah 2 lantai yang eksklusif dan terbatas yang terletak di jalan utama (boulevard)dengan ROW 17 M. Seluruh bangunan TIPE DORADO menghadap ke taman dan/atau fasilitas umum seperti kolam serta playground\r\n\r\n', 'LUAS BANGUNAN : 138 m2\r\nLUAS TANAH : 162 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : UPVC\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Jotun\r\nLANTAI: Granite Tile Polished 80x80\r\nSANITAIR : ex. Toto\r\n', NULL),
(3, 'NASHIRA', 1620000000, 'uploads/683ce8033854c_Cuplikan layar 2025-06-02 065235.png', 'uploads/683ce90700d88_Cuplikan layar 2025-06-02 065730.png', 'uploads/683ce80338f77_Cuplikan layar 2025-06-02 065251.png', 'Rumah yang Terdiri dari 2 lantai dengan visual yang modern, serta memiliki 3 kamar tidur. Sangat cocok untuk kaum milenial yang mengedepankan modernitas dan fleksibelitas\r\n', 'LUAS BANGUNAN : 110 m2\r\nLUAS TANAH : 120 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : UPVC\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Jotun\r\nLANTAI: Granite Tile Polished 80x80\r\nSANITAIR : ex. Toto\r\n', NULL),
(4, 'CARINA', 900000000, 'uploads/68582ae9b7830_Cuplikan layar 2025-06-22 230854.png', 'uploads/6858300da57c2_Cuplikan layar 2025-06-22 233143.png', 'uploads/68582ae9b81a6_Cuplikan layar 2025-06-22 230940.png', 'Rumah 2 lantai yang simple cocok untuk kaum muda dengan mobilitas yang\r\ntinggi.\r\n', 'LUAS BANGUNAN : 85 m2\r\nLUAS TANAH : 105 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : UPVC\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Jotun\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. Toto\r\n', NULL),
(5, 'MYRA', 1285000000, 'uploads/68582c0f198a5_Cuplikan layar 2025-06-22 231417.png', 'uploads/6858305d21f15_Cuplikan layar 2025-06-22 233316.png', 'uploads/68582c0f19cd6_Cuplikan layar 2025-06-22 231430.png', 'Rumah minimalis yang terdiri dari 2 lantai lengkap dengan 3 kamar tidur dan 2 kamar mandi. Selain itu juga rumah ini memiliki tanah lebih yang luas sehingga bisa dikembangkan dengan konsep sendiri.\r\n', 'LUAS BANGUNAN : 73 m2\r\nLUAS TANAH : 120 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : UPVC\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Jotun\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. Toto\r\n', NULL),
(6, 'LYRA', 1205000000, 'uploads/68582ca149567_Cuplikan layar 2025-06-22 231617.png', 'uploads/68583152d1f11_Cuplikan layar 2025-06-22 233603.png', 'uploads/685831fbe2d75_Cuplikan layar 2025-06-22 234004.png', 'Rumah 1 lantai dengan dilengkapi 3 kamar tidur dan 2 kamar mandi. Dibangun sangat terbatas sangat cocok untuk orang yang menyukai rumah 1 lantai dengan tanah yang lebih luas.\r\n', 'LUAS BANGUNAN : 75 m2\r\nLUAS TANAH : 135 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : Aluminium ex. YKK\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Spektrum\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. American Standard\r\n', NULL),
(7, 'ARION', 750000000, 'uploads/68582d2fba79f_Cuplikan layar 2025-06-22 231921.png', 'uploads/6858323dcc9f0_Cuplikan layar 2025-06-22 234117.png', 'uploads/68582d2fbad07_Cuplikan layar 2025-06-22 231931.png', 'Rumah 1 lantai dengan konsep Eco Home sangat cocok untuk keluarga kecil yang simple.\r\n', 'LUAS BANGUNAN : 48 m2\r\nLUAS TANAH : 90 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : Aluminium ex. YKK\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Spektrum\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. American Standard\r\n', NULL),
(8, 'LEONIS', 721000000, 'uploads/68582daf07f13_Cuplikan layar 2025-06-22 232112.png', 'uploads/6858312621abb_Cuplikan layar 2025-06-22 233603.png', 'uploads/68582daf08445_Cuplikan layar 2025-06-22 232124.png', 'Rumah 1 lantai yang terdiri dari 2 kamar tidur dan 1 kamar mandi dengan konsep minimalis sangat cocok untuk keluarga baru.\r\n', 'LUAS BANGUNAN : 47 m2\r\nLUAS TANAH : 90 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan ex.Taso\r\nJENDELA : Aluminium ex. YKK\r\nPINTU : Pintu by Porta\r\nAKSESORIS PINTU : Stainless Steel ex. Dekson\r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Spektrum\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. American Standard\r\n', NULL),
(9, 'VELLA A', 6000000000, 'uploads/68582e2869749_Cuplikan layar 2025-06-22 232313.png', 'uploads/685830b8eee10_Cuplikan layar 2025-06-22 233432.png', 'uploads/68582e2869ffa_Cuplikan layar 2025-06-22 232326.png', 'Rumah 1 lantai yang mengusung konsep skandinavian menyajikan visual yang lebih fresh.\r\n', 'LUAS BANGUNAN : 45 m2\r\nLUAS TANAH : 90 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan \r\nJENDELA : Aluminium ex. aluprima\r\nPINTU : Pintu by Porta & engineering door\r\nAKSESORIS PINTU : Stainless Steel \r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Spektrum\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. American Standard\r\n', NULL),
(10, 'VELLA B', 625000000, 'uploads/68582edcc1881_Cuplikan layar 2025-06-22 232610.png', 'uploads/685830cfb142b_Cuplikan layar 2025-06-22 233432.png', 'uploads/68582edcc1e83_Cuplikan layar 2025-06-22 232636.png', 'Rumah 1 lantai dengan desain minimalis yang affordable sangat cocok untuk keluarga muda.\r\n', 'LUAS BANGUNAN : 36 m2\r\nLUAS TANAH : 84 m2\r\nPONDASI : Pondasi Tapak Beton Bertulang\r\nSTRUKTUR : Beton Bertulang\r\nATAP : Rangka Baja Ringan \r\nJENDELA : Aluminium ex. aluprima\r\nPINTU : Pintu by Porta & engineering door\r\nAKSESORIS PINTU : Stainless Steel \r\nPLAFON : Gypsum\r\nDINDING : Bata Merah fin. Plester aci + cat ex.Spektrum\r\nLANTAI: Granite Tile Polished 60x60\r\nSANITAIR : ex. American Standard\r\n', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_transaksi_pembelian`
--

CREATE TABLE `tb_transaksi_pembelian` (
  `id_transaksi_pembelian` int(11) NOT NULL,
  `id_pembelian` varchar(50) NOT NULL,
  `tanggal_transaksi` date DEFAULT current_timestamp(),
  `jenis_transaksi` enum('Uang Booking','Return Uang Booking') NOT NULL,
  `jumlah_transaksi` decimal(15,0) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `kwitansi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_transaksi_pembelian`
--

INSERT INTO `tb_transaksi_pembelian` (`id_transaksi_pembelian`, `id_pembelian`, `tanggal_transaksi`, `jenis_transaksi`, `jumlah_transaksi`, `keterangan`, `kwitansi`) VALUES
(75, 'VV1', '2025-05-09', 'Uang Booking', 1000000, 'uang booking', 'uploads/kwitansi/kwitansi_6870d1704e7a9.jpg'),
(76, 'VV1', '2025-05-30', 'Return Uang Booking', 950000, 'tidak cocok ', NULL),
(77, 'VV3', '2025-06-19', 'Uang Booking', 1000000, 'uang booking', 'uploads/kwitansi/kwitansi_6870d1c208a4d.jpg'),
(78, 'VV5', '2025-07-08', 'Uang Booking', 1000000, 'uang booking', 'uploads/kwitansi/kwitansi_6870d1e73a172.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_unit`
--

CREATE TABLE `tb_unit` (
  `id_unit` int(11) UNSIGNED NOT NULL,
  `id_rumah` int(11) UNSIGNED NOT NULL,
  `nama_blok` varchar(5) NOT NULL,
  `type_unit` varchar(30) NOT NULL,
  `status` enum('tersedia','terjual','terbooking') NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_unit`
--

INSERT INTO `tb_unit` (`id_unit`, `id_rumah`, `nama_blok`, `type_unit`, `status`) VALUES
(32, 7, 'A1', 'ARION', 'tersedia'),
(33, 7, 'A2', 'ARION', 'tersedia'),
(34, 7, 'A3', 'ARION', 'tersedia'),
(35, 7, 'A4', 'ARION', 'tersedia'),
(36, 7, 'A5', 'ARION', 'tersedia'),
(37, 4, 'C1', 'CARINA', 'tersedia'),
(38, 4, 'C2', 'CARINA', 'tersedia'),
(39, 4, 'C3', 'CARINA', 'tersedia'),
(40, 4, 'C4', 'CARINA', 'tersedia'),
(41, 4, 'C5', 'CARINA', 'tersedia'),
(42, 2, 'D1', 'DORADO', 'tersedia'),
(43, 2, 'D2', 'DORADO', 'tersedia'),
(44, 2, 'D3', 'DORADO', 'tersedia'),
(45, 2, 'D4', 'DORADO', 'tersedia'),
(46, 2, 'D5', 'DORADO', 'tersedia'),
(47, 8, 'L1', 'LEONIS', 'tersedia'),
(48, 8, 'L2', 'LEONIS', 'tersedia'),
(49, 8, 'L3', 'LEONIS', 'tersedia'),
(50, 8, 'L4', 'LEONIS', 'tersedia'),
(51, 8, 'L5', 'LEONIS', 'tersedia'),
(52, 6, 'L1', 'LYRA', 'tersedia'),
(53, 6, 'L2', 'LYRA', 'tersedia'),
(54, 6, 'L3', 'LYRA', 'tersedia'),
(55, 6, 'L4', 'LYRA', 'tersedia'),
(56, 6, 'L5', 'LYRA', 'tersedia'),
(57, 5, 'M1', 'MYRA', 'tersedia'),
(58, 5, 'M2', 'MYRA', 'tersedia'),
(59, 5, 'M3', 'MYRA', 'tersedia'),
(60, 5, 'M4', 'MYRA', 'tersedia'),
(61, 5, 'M5', 'MYRA', 'tersedia'),
(62, 3, 'N1', 'NASHIRA', 'tersedia'),
(63, 3, 'N2', 'NASHIRA', 'tersedia'),
(64, 3, 'N3', 'NASHIRA', 'tersedia'),
(65, 3, 'N4', 'NASHIRA', 'tersedia'),
(66, 3, 'N5', 'NASHIRA', 'tersedia'),
(67, 9, 'V1', 'VELLA A', 'tersedia'),
(68, 9, 'V2', 'VELLA A', 'tersedia'),
(69, 9, 'V3', 'VELLA A', 'tersedia'),
(70, 9, 'V4', 'VELLA A', 'tersedia'),
(71, 9, 'V5', 'VELLA A', 'tersedia'),
(72, 10, 'V1', 'VELLA B', 'tersedia'),
(73, 10, 'V2', 'VELLA B', 'tersedia'),
(74, 10, 'V3', 'VELLA B', 'tersedia'),
(75, 10, 'V4', 'VELLA B', 'tersedia'),
(76, 10, 'V5', 'VELLA B', 'tersedia'),
(77, 1, 'V1', 'VOLANS', 'tersedia'),
(78, 1, 'V2', 'VOLANS', 'tersedia'),
(79, 1, 'V3', 'VOLANS', 'terbooking'),
(80, 1, 'V4', 'VOLANS', 'tersedia'),
(81, 1, 'V5', 'VOLANS', 'terjual');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tb_kontak`
--
ALTER TABLE `tb_kontak`
  ADD PRIMARY KEY (`id_kontak`),
  ADD KEY `fk_kontak_rumah_type` (`type_rumah`),
  ADD KEY `fk_kontak_ke_rumah` (`id_rumah`);

--
-- Indeks untuk tabel `tb_login`
--
ALTER TABLE `tb_login`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pembelian` (`id_pembelian`);

--
-- Indeks untuk tabel `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `fk_pembelian_rumah` (`id_rumah`),
  ADD KEY `fk_pembelian_unit` (`id_unit`);

--
-- Indeks untuk tabel `tb_rumah`
--
ALTER TABLE `tb_rumah`
  ADD PRIMARY KEY (`id_rumah`),
  ADD UNIQUE KEY `type_rumah` (`type_rumah`);

--
-- Indeks untuk tabel `tb_transaksi_pembelian`
--
ALTER TABLE `tb_transaksi_pembelian`
  ADD PRIMARY KEY (`id_transaksi_pembelian`),
  ADD KEY `id_pembelian` (`id_pembelian`);

--
-- Indeks untuk tabel `tb_unit`
--
ALTER TABLE `tb_unit`
  ADD PRIMARY KEY (`id_unit`),
  ADD KEY `idx_type_unit` (`type_unit`),
  ADD KEY `idx_id_rumah` (`id_rumah`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tb_kontak`
--
ALTER TABLE `tb_kontak`
  MODIFY `id_kontak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `tb_login`
--
ALTER TABLE `tb_login`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tb_rumah`
--
ALTER TABLE `tb_rumah`
  MODIFY `id_rumah` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `tb_transaksi_pembelian`
--
ALTER TABLE `tb_transaksi_pembelian`
  MODIFY `id_transaksi_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT untuk tabel `tb_unit`
--
ALTER TABLE `tb_unit`
  MODIFY `id_unit` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tb_kontak`
--
ALTER TABLE `tb_kontak`
  ADD CONSTRAINT `fk_kontak_ke_rumah` FOREIGN KEY (`id_rumah`) REFERENCES `tb_rumah` (`id_rumah`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  ADD CONSTRAINT `tb_pembayaran_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `tb_pembelian` (`id_pembelian`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  ADD CONSTRAINT `fk_pembelian_rumah` FOREIGN KEY (`id_rumah`) REFERENCES `tb_rumah` (`id_rumah`),
  ADD CONSTRAINT `fk_pembelian_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit` (`id_unit`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_transaksi_pembelian`
--
ALTER TABLE `tb_transaksi_pembelian`
  ADD CONSTRAINT `tb_transaksi_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `tb_pembelian` (`id_pembelian`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_unit`
--
ALTER TABLE `tb_unit`
  ADD CONSTRAINT `fk_id_rumah` FOREIGN KEY (`id_rumah`) REFERENCES `tb_rumah` (`id_rumah`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
