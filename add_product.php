<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Nature Lover Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-image: url('https://images.unsplash.com/photo-1631592058858-a8c4b556df5b?q=80&w=2072&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-position: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.3);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            width: 350px;
            text-align: center;
            margin-right: 20%;
        }
        h2 {
            color: #2E8B57;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 2);
        }
        p {
            color: #f0f0f0;
            margin-bottom: 20px;
            font-size: 0.9em;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 1);
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #2E8B57;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #256C43;
        }
        .error, .success {
            font-size: 0.9em;
            margin-top: 5px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <?php
    // Aktifkan display errors untuk debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Inisialisasi variabel (Tugas Dasar PHP: Deklarasi variabel)
    $name = $price = $description = "";
    $error = $success = "";

    // Proses data form (Tugas Dasar PHP: Penggunaan if-else)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil data dari form (Tugas Form Input)
        $name = $_POST["name"] ?? "";
        $price = $_POST["price"] ?? "";
        $description = $_POST["description"] ?? "";

        // Debugging: Log data yang diterima
        error_log("Form submitted: " . print_r($_POST, true));
        error_log("Data after retrieval - Name: $name, Price: $price, Description: $description");

        // Hapus format Rupiah dari harga (jika ada)
        $price = str_replace(['Rp.', '.', ',-'], ['', '', ''], $price);

        // Debugging: Log harga setelah diformat
        error_log("Price after removing format: $price");

        // Validasi data (Tugas Validasi)
        if (empty($name) || empty($price) || empty($description)) {
            $error = "Semua field harus diisi! Nama: '$name', Harga: '$price', Deskripsi: '$description'";
            error_log("Validation failed: Empty fields - Name: '$name', Price: '$price', Description: '$description'");
        } else {
            // Validasi harga harus angka positif
            if (!is_numeric($price) || $price <= 0) {
                $error = "Harga harus berupa angka positif!";
                error_log("Validation failed: Price is not a positive number");
            } else {
                // Koneksi ke database
                $conn = mysqli_connect("localhost", "root", "", "ecommerce");

                if (!$conn) {
                    $error = "Koneksi database gagal: " . mysqli_connect_error();
                    error_log("Database connection failed: " . mysqli_connect_error());
                } else {
                    error_log("Database connection successful");

                    // Gunakan prepared statement untuk keamanan
                    $stmt = mysqli_prepare($conn, "INSERT INTO products (nama_produk, harga, deskripsi) VALUES (?, ?, ?)");
                    if ($stmt) {
                        error_log("Prepared statement created successfully");
                        mysqli_stmt_bind_param($stmt, "sds", $name, $price, $description);
                        if (mysqli_stmt_execute($stmt)) {
                            $success = "Produk berhasil ditambahkan!";
                            error_log("Data inserted successfully: $name, $price, $description");
                            // Redirect untuk mencegah form resubmission
                            header("Location: " . $_SERVER["PHP_SELF"]);
                            exit();
                        } else {
                            $error = "Gagal menambahkan produk: " . mysqli_stmt_error($stmt);
                            error_log("Error inserting data: " . mysqli_stmt_error($stmt));
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $error = "Gagal mempersiapkan statement: " . mysqli_error($conn);
                        error_log("Error preparing statement: " . mysqli_error($conn));
                    }
                    mysqli_close($conn);
                }
            }
        }
    }
    ?>

    <div class="form-container">
        <h2>Add New Product</h2>
        <p>Fill in the details to add a product</p>

        <!-- Form input untuk menambah produk (Tugas Form Input) -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="productForm">
            <input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($name); ?>" required>
            <input type="text" name="price" placeholder="Price (Rp.)" value="<?php echo htmlspecialchars($price); ?>" pattern="[0-9]*" inputmode="numeric" required>
            <textarea name="description" placeholder="Description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
            <button type="submit">Add Product</button>
        </form>

        <!-- Tampilkan pesan error atau sukses -->
        <?php if ($error) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success) { ?>
            <div class="success" id="successMessage"><?php echo $success; ?></div>
        <?php } ?>
    </div>

    <script>
        // Hilangkan pesan sukses setelah 1.5 detik
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 1500);
        }
    </script>
</body>
</html>