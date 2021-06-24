<?php
include_once './config/database.php';
require_once './vendor/autoload.php';
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$email = '';
$password = '';

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$table_name = 'user';

$query = "SELECT id, username, password FROM " . $table_name . " WHERE email = ? LIMIT 0,1";

$stmt = $conn->prepare( $query );
$stmt->bindParam(1, $email);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $username = $row['username'];
    $password2 = $row['password'];

    if(password_verify($password, $password2))
    {
        $secret_key = "YOUR_SECRET_KEY";
        $issuer_claim = "THE_ISSUER"; // this can be the servername
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 10; //not before in seconds
        $expire_claim = $issuedat_claim + 60; // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $id,
                "username" => $username,
                "email" => $email
        ));

        http_response_code(200);

        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
            array(
                "message" => "Successful login.",
                "accessToken" => $jwt,
                "email" => $email,
                "expireAt" => $expire_claim
            ));
    }
    else{

        http_response_code(401);
        echo json_encode(array("message" => "Login failed.", "password" => $password));
    }
}
?>
=======
// Import script autoload agar bisa menggunakan library
require_once('vendor/autoload.php');
require_once('./cors.php');
// Import library
use Firebase\JWT\JWT;
use Dotenv\Dotenv;

// Load dotenv
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Atur jenis response
header('Content-Type: application/json');

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit();
}

// Ambil data json yang dikirim user
$json = file_get_contents('php://input');
$input_user = json_decode($json);

// Jika tidak ada data email atau password
if (!isset($input_user->email) || !isset($input_user->password)) {
  http_response_code(400);
  exit();
}

$user = [
  'email' => 'ana30kth@gmail.com',
  'password' => 'bismillah3010'
];

// Atur jenis response
header('Content-Type: application/json');

// Jika email atau password tidak sesuai
if ($input_user->email !== $user['email'] || $input_user->password !== $user['password']) {
  echo json_encode([
    'success' => false,
    'data' => null,
    'message' => 'Email atau password tidak sesuai'
  ]);
  exit();
}

// Menghitung waktu kadaluarsa token. Dalam kasus ini akan terjadi setelah 15 menit
$waktu_kadaluarsa = time() + (20 * 60);

// Buat payload dan access token
$payload = [
  'email' => $input_user->email,
  // Di library ini wajib menambah key exp untuk mengatur masa berlaku token
  'exp' => $waktu_kadaluarsa
];

// Men-generate access token
$access_token = JWT::encode($payload, $_ENV['ACCESS_TOKEN_SECRET']);

// Kirim kembali ke user
echo json_encode([
  'success' => true,
  'data' => [
    'accessToken' => $access_token,
    'expiry' => date(DATE_ISO8601, $waktu_kadaluarsa)
  ],
  'message' => 'Login berhasil!'
]);

// Ubah waktu kadaluarsa lebih lama, dalam kasus ini 1 jam
$payload['exp'] = time() + (60 * 60);
$refresh_token = JWT::encode($payload, $_ENV['REFRESH_TOKEN_SECRET']);

// Simpan refresh token di http-only cookie
setcookie('refreshToken', $refresh_token, $payload['exp'], '', '', false, true);
>>>>>>> 788e068fa2430dd916722984a1d50ea7b9cab1a8
