
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Iman Syariah - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

    <div class="container">

        <div class="left-panel">
            <div class="brand">
                <div class="brand-logo">B</div>
                <h2>Bank Iman Syariah</h2>
            </div>

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

                    <label>Masuk sebagai</label>

                    <div class="login-role">
                        <label class="role-option">
                            <input type="radio" name="role" value="nasabah" checked>
                            <span class="role-content">
                                <span class="role-icon">👤</span>
                                <strong>Nasabah</strong>
                                <small>Akses rekening pribadi</small>
                            </span>
                        </label>

                        <label class="role-option">
                            <input type="radio" name="role" value="admin">
                            <span class="role-content">
                                <span class="role-icon">🛡️</span>
                                <strong>Admin</strong>
                                <small>Kelola operasional bank</small>
                            </span>
                        </label>
                    </div>

                    <label for="username">Username</label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Masukkan username"
                        required
                    >

                    <label for="password">Kata sandi</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan kata sandi"
                            required
                        >

                        <button
                            type="button"
                            id="togglePassword"
                            class="toggle-password"
                            aria-label="Lihat kata sandi"
                        >
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>

                    <button type="submit">Masuk</button>

                </form>

                <p class="register-link">
    Belum punya akun?
    <a href="daftar.php">Daftar sekarang</a>
</p>

            </div>
        </div>

    </div>

    <script>
        const password = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {
            const visible = password.type === "password";

            password.type = visible ? "text" : "password";

            this.innerHTML = visible
                ? `<svg viewBox="0 0 24 24">
                     <path d="M3 3L21 21"/>
                     <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                     <path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-4 4.5"/>
                     <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.2 3.3-.6"/>
                   </svg>`
                : `<svg viewBox="0 0 24 24">
                     <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                     <circle cx="12" cy="12" r="3"/>
                   </svg>`;
        });
    </script>

</body>
</html>
