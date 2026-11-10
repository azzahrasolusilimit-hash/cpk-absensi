
<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class AuthController
{
    /**
     * Registrasi mahasiswa baru.
     */
    public static function register(): void
    {
        requireMethod('POST');

        $data = getJsonInput();

        $missing = validateRequiredFields(
            $data,
            ['name', 'phone', 'password']
        );

        if (!empty($missing)) {
            jsonResponse([
                'success' => false,
                'message' => 'Semua field wajib harus diisi.',
                'fields' => $missing
            ], 422);
        }

        $name = trim($data['name']);
        $phone = trim($data['phone']);
        $password = $data['password'];

        if (strlen($name) > 100) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama maksimal 100 karakter.'
            ], 422);
        }

        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            jsonResponse([
                'success' => false,
                'message' => 'Nomor telepon harus 10-15 digit.'
            ], 422);
        }

        if (strlen($password) < 8) {
            jsonResponse([
                'success' => false,
                'message' => 'Password minimal 8 karakter.'
            ], 422);
        }

        try {
            $pdo = db();

            $id = bin2hex(random_bytes(16));

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (id, name, phone, password_hash, role)
                VALUES (?, ?, ?, ?, 'mahasiswa')"
            );

            $stmt->execute([
                $id,
                $name,
                $phone,
                $passwordHash
            ]);

            jsonResponse([
                'success' => true,
                'message' => 'Registrasi berhasil.',
                'data' => [
                    'id' => $id,
                    'name' => $name,
                    'phone' => $phone,
                    'role' => 'mahasiswa'
                ]
            ], 201);

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                jsonResponse([
                    'success' => false,
                    'message' => 'Nomor telepon sudah terdaftar.'
                ], 409);
            }

            error_log($e->getMessage());

            jsonResponse([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Login pengguna.
     */
    public static function login(): void
    {
        requireMethod('POST');

        $data = getJsonInput();

        $missing = validateRequiredFields(
            $data,
            ['phone', 'password']
        );

        if (!empty($missing)) {
            jsonResponse([
                'success' => false,
                'message' => 'Nomor telepon dan password wajib diisi.'
            ], 422);
        }

        $phone = trim($data['phone']);
        $password = $data['password'];

        try {
            $pdo = db();

            $stmt = $pdo->prepare(
                "SELECT id, name, phone, password_hash, role, photo
                 FROM users
                 WHERE phone = ?
                 LIMIT 1"
            );

            $stmt->execute([$phone]);

            $user = $stmt->fetch();

            if (
                !$user ||
                !password_verify($password, $user['password_hash'])
            ) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Nomor telepon atau password salah.'
                ], 401);
            }

            // Membuat session login baru.
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            unset($user['password_hash']);

            jsonResponse([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => $user
            ]);

        } catch (PDOException $e) {
            error_log($e->getMessage());

            jsonResponse([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Memeriksa pengguna yang sedang login.
     */
    public static function me(): void
    {
        requireMethod('GET');

        if (empty($_SESSION['user_id'])) {
            jsonResponse([
                'success' => false,
                'message' => 'Anda belum login.'
            ], 401);
        }

        try {
            $pdo = db();

            $stmt = $pdo->prepare(
                "SELECT id, name, phone, role, photo
                 FROM users
                 WHERE id = ?
                 LIMIT 1"
            );

            $stmt->execute([$_SESSION['user_id']]);

            $user = $stmt->fetch();

            if (!$user) {
                $_SESSION = [];
                session_destroy();

                jsonResponse([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan.'
                ], 401);
            }

            jsonResponse([
                'success' => true,
                'message' => 'Session masih aktif.',
                'data' => $user
            ]);

        } catch (PDOException $e) {
            error_log($e->getMessage());

            jsonResponse([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Logout pengguna.
     */
    public static function logout(): void
    {
        requireMethod('POST');

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => 'Lax'
                ]
            );
        }

        session_destroy();

        jsonResponse([
            'success' => true,
            'message' => 'Logout berhasil.'
        ]);
    }
}
