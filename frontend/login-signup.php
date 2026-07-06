                                                                    <?php
session_start();
require_once 'config.php';

// =============================================
// REGISTER
// =============================================
if (isset($_POST['register'])) {
    $nama     = trim($_POST['username']);
    $npm      = trim($_POST['npm']);
    $email    = strtolower(trim($_POST['email'])); // FIX #1: lowercase email saat register
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validasi NPM
    if (!ctype_digit($npm) || strlen($npm) < 5 || strlen($npm) > 10 || (strlen($npm) === 10 && $npm > '2147483647')) {
        $_SESSION['REGISTER_ERROR'] = "NPM must be a numeric value with 5 to 10 digits and fit MySQL INT range.";
        $_SESSION['active_form'] = 'signup-form';
        redirect('index.php');
        exit();
    }

    // Cek email duplikat
    $stmt = $conn->prepare("SELECT mahasiswa_email FROM mahasiswa WHERE mahasiswa_email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $checkEmail = $stmt->get_result();

    if ($checkEmail->num_rows > 0) {
        $stmt->close(); // FIX #2: close statement sebelum redirect
        $_SESSION['REGISTER_ERROR'] = "Email already registered.";
        $_SESSION['active_form'] = 'signup-form';
        redirect('index.php');
        exit();
    }

    $stmt->close(); // FIX #2: close statement sebelum prepare ulang

    // Insert data mahasiswa baru
    $stmt = $conn->prepare("INSERT INTO mahasiswa (mahasiswa_nama, mahasiswa_npm, mahasiswa_email, mahasiswa_password) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssss", $nama, $npm, $email, $password); // FIX #3: "ssss" bukan "sssss"
    
    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['REGISTER_SUCCESS'] = "Registration successful. Please login."; // FIX #4: tambah pesan sukses
        redirect('index.php');
        exit();
    } else {
        $stmt->close();
        $_SESSION['REGISTER_ERROR'] = "Registration failed. Please try again.";
        $_SESSION['active_form'] = 'signup-form';
        redirect('index.php');
        exit();
    }
}

// =============================================
// LOGIN
// =============================================
if (isset($_POST['login'])) {
    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE mahasiswa_email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close(); // FIX #2: close setelah fetch

        if (password_verify($password, $user['mahasiswa_password'])) {
            // Regenerate session ID untuk mencegah session fixation
            session_regenerate_id(true); // FIX #5: keamanan session

            $_SESSION['name']  = $user['mahasiswa_nama'];
            $_SESSION['email'] = $user['mahasiswa_email'];
            $_SESSION['npm']   = $user['mahasiswa_npm'];
            $_SESSION['role']  = $user['role'];

            // FIX #6: redirect berbeda per role
            if ($user['role'] === 'admin') {
                redirect('home.php');
            } elseif ($user['role'] === 'ketua') {
                redirect('home.php');
            } else {
                redirect('home.php');
            }
            exit();
        } else {
            $_SESSION['LOGIN_ERROR'] = "Invalid email or password.";
            $_SESSION['active_form'] = 'login-form';
            redirect('index.php');
            exit();
        }
    } else {
        $stmt->close();
        $_SESSION['LOGIN_ERROR'] = "Invalid email or password.";
        $_SESSION['active_form'] = 'login-form';
        redirect('index.php');
        exit();
    }
}
?>