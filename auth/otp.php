<?php
session_start();

if (!isset($_SESSION["pending_user_id"])) {
    header("Location: ../index.php");
    exit;
}

if (time() >= $_SESSION["otp_expiry"]) {
    unset($_SESSION["otp_hash"], $_SESSION["demo_otp"]);
    $expired = true;
    $remaining = 0;
} else {
    $expired = false;
    $remaining = $_SESSION["otp_expiry"] - time();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="login-page otp-page">

<div class="container">

    <!-- PANEL KIRI -->
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


    <!-- PANEL KANAN -->
    <div class="right-panel">

        <div class="login-box otp-card">

            <div class="otp-icon">✓</div>

            <h2>Verifikasi OTP</h2>

            <p class="otp-description">
                Masukkan kode verifikasi 6 digit untuk melanjutkan.
            </p>

            <?php if ($expired): ?>

                <p style="color:red;">
                    Kode OTP sudah kedaluwarsa.
                </p>

                <a href="../index.php">Kembali ke Login</a>

            <?php else: ?>

                <div class="otp-timer">
                    <p>Sisa waktu verifikasi</p>
                    <h2 id="countdown">05:00</h2>
                </div>

                <form action="verify_otp.php" method="POST" id="otpForm">

                    <label>Kode OTP</label>

                    <input
                        type="text"
                        name="otp"
                        class="otp-input"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        placeholder="Masukkan 6 digit OTP"
                        required
                    >

                    <button type="submit" id="verifyButton">
                        Verifikasi
                    </button>

                </form>

                <div class="otp-demo">
                    <p>Kode OTP kamu:</p>

                    <strong class="otp-code">
                        <?= htmlspecialchars($_SESSION["demo_otp"]) ?>
                    </strong>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php if (!$expired): ?>
<script>
let remaining = <?= (int)$remaining ?>;

const countdown = document.getElementById("countdown");
const otpForm = document.getElementById("otpForm");

function updateCountdown() {
    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;

    countdown.textContent =
        String(minutes).padStart(2, "0") + ":" +
        String(seconds).padStart(2, "0");

    if (remaining <= 0) {
        countdown.textContent = "00:00";
        countdown.style.color = "red";

        otpForm.style.display = "none";

        const message = document.createElement("p");
        message.style.color = "red";
        message.textContent =
            "Kode OTP sudah kedaluwarsa. Silakan login kembali.";

        otpForm.parentNode.insertBefore(message, otpForm);
        return;
    }

    if (remaining <= 60) {
        countdown.style.color = "red";
    }

    remaining--;
    setTimeout(updateCountdown, 1000);
}

updateCountdown();
</script>
<?php endif; ?>

</body>
</html>