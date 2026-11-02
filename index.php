<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Iman Syariah - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="container">

        <div class="left-panel">
            <h2>Bank Iman Syariah</h2>

            <div class="welcome">
                <h1>Bertransaksi dengan tenang, sesuai prinsip syariah.</h1>

                <p>
                    Kelola rekening, pembiayaan, dan zakat Anda
                    dalam satu tempat yang aman dan transparan.
                </p>
            </div>

            <div class="features">
                <p>✓ Akad jelas: Wadiah, Mudharabah, dan Murabahah</p>
                <p>✓ Margin dan biaya ditampilkan transparan</p>
                <p>✓ Tunaikan zakat langsung dari aplikasi</p>
            </div>

            <footer>© 2026 Bank Iman Syariah</footer>
        </div>

        <div class="right-panel">
            <div class="login-box">

                <h2>Masuk</h2>
                <p>Masuk dengan username dan kata sandi Anda.</p>

                <form action="auth/login.php" method="POST">

                    <label>Username</label>
                    <input type="text" name="username"
                           placeholder="Masukkan username" required>

                    <label>Kata sandi</label>
                    <input type="password" name="password"
                           placeholder="Masukkan kata sandi" required>

                    <button type="submit">Masuk</button>

                </form>

            </div>
        </div>

    </div>

</body>
</html>