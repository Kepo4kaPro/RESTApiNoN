<?php

const Pattern = [
    "phone" => "/^[7]{1}[0-9]{10}+$/",
    "mail" => "/[A-Za-z0-9._%+-]{3,}@[a-zA-Z]{3,}([.]{1}[a-zA-Z]{2,}|[.]{1}[a-zA-Z]{2,}[.]{1}[a-zA-Z]{2,})/"
];


$main_uri = explode("/",$_SERVER['REQUEST_URI']);
$method = $_SERVER['REQUEST_METHOD'];

$GLOBALS['$mysqli'] = new mysqli("localhost", "api-back", "zO4hE2gI0uvT0c", "api");
$IsAlive = new is_allive();
cors();

if(isset($main_uri[0])){
    $mysqli = $GLOBALS['$mysqli'];
    if(isset($main_uri[1])){
        switch ($method){
            case "POST" :{
                switch ($main_uri[1]){
                    case "register" :{
                        $errors = [];
                        if(isset($_POST["login"])) $login = $_POST["login"];
                        if(isset($_POST["mailP"])) $mailP = $_POST["mailP"];
                        if(isset($_POST["pass"])) $pass = $_POST["pass"];
                        if(isset($_POST["passc"])) $passc = $_POST["passc"];

                        if(!isset($login) or $IsAlive->login($login)) $errors[] = "login";
                        if(!isset($mailP)) $errors[] = "mailP";
                        if(!isset($pass) or !isset($passc) or $pass!=$passc) $errors[] = "pass";

                        $typeM = null;
                        if(preg_match(Pattern["phone"], $mailP)){
                            if($IsAlive->phone($mailP)) $errors[] = "mailP";
                            $typeM = "phone";
                        }elseif(preg_match(Pattern["mail"], $mailP)){
                            if($IsAlive->mail($mailP)) $errors[] = "mailP";
                            $typeM = "mail";
                        }else{
                            $errors[] = "mailP";
                        }
                        if($errors){
                            http_response_code(422);
                            exit(json_encode([
                                "error" => [
                                    "message" => "Validation error",
                                    "errors" => $errors
                                ]
                            ]));
                        }else{
                            $mail = $typeM === "mail" ? $mailP : "";
                            $phone = $typeM === "phone" ? $mailP : "";;
                            $hashPass = password_hash($pass, PASSWORD_BCRYPT);
                            $mysqli ->query("INSERT INTO users (login, phone, mail, pass) VALUES ('$login', '$phone', '$mail', '$hashPass')");
                            $user_id = $mysqli->insert_id;
                            $token = user_autorise($user_id);
                            http_response_code(200);
                            exit(json_encode([
                                "id" => $user_id,
                                "token" => $token
                            ]));
                        }
                    }
                    case "login" :{
                        $errors = [];
                        if(isset($_POST["login"])) $login = $_POST["login"];
                        if(isset($_POST["pass"])) $pass = $_POST["pass"];

                        if(!isset($login)) $errors[] = "login";
                        if(!isset($pass)) $errors[] = "pass";

                        if($errors){
                            http_response_code(422);
                            exit(json_encode([
                                "error" => [
                                    "message" => "Validation error",
                                    "errors" => $errors
                                ]
                            ]));
                        }else{
                            $id = null;
                            if(preg_match(Pattern["phone"], $login))
                                $id = $IsAlive->phone($login);
                            elseif (preg_match(Pattern["mail"], $login))
                                $id = $IsAlive->mail($login);
                            else $id = $IsAlive->login($login);

                            $user_pass = null;
                            $token = null;
                            if($id){
                                $user_pass = $mysqli->query("SELECT pass FROM users WHERE id='$id'") -> fetch_assoc()["pass"];
                            }
                            if($user_pass){
                                if(password_verify($pass, $user_pass)){
                                    $token = user_autorise($id);
                                }
                            }

                            if($token){
                                http_response_code(200);
                                exit(json_encode([
                                    "id" => $id,
                                    "token" => $token
                                ]));
                            }else{
                                http_response_code(400);
                                exit(json_encode([
                                    "errors" => [
                                        "message" => "login or password invalid"
                                    ]
                                ]));
                            }
                        }
                    } break;
                    case "logout" :{
                        if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                            $token = explode(" ", $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
                            if (isset($token[0]) and $token[0] == "Bearer") {
                                $qvery = $token[1];
                                $mysqli->query("DELETE FROM authorization WHERE token = '$qvery'");
                            }
                        }
                    }
                }
            } break ;
            case "GET" :{
                $cat_qvery = $main_uri[2];
                switch ($main_uri[1]){
                    case "catalog" : {
                        if(isset($cat_qvery)){
                            $cat_qvery = $mysqli->real_escape_string($cat_qvery);
                            $result = $mysqli->query("SELECT id, langname FROM catalogs WHERE name = '$cat_qvery'");
                            $rev = $result->fetch_assoc();
                            $id_catalog = $rev['id'];
                            $name = $rev['langname'];
                            $result = $mysqli->query("SELECT id, name, price, main_pict FROM products WHERE id_catalog = '$id_catalog'");

                            $respons = [];
                            while ($row = $result->fetch_assoc()){
                                array_push($respons, $row);
                            }
                            echo json_encode(["name"=> $name, "content" => $respons]);
                        }
                        break;
                    }
                    case "product" : {
                        if(isset($cat_qvery) and is_numeric($cat_qvery)){
                            $cat_qvery = intval($cat_qvery);
                            $result = $mysqli->query("SELECT name, description, composition, sizes, price, main_pict, others_pict, id_catalog FROM products WHERE id = '$cat_qvery'");
                            $resis = $result->fetch_assoc();
                            $answ = [];

                            $answ['name'] = $resis['name'];
                            $answ['description'] = $resis['description'];
                            $answ['composition'] = json_decode($resis['composition']);
                            $answ['sizes'] = json_decode($resis['sizes']);
                            $answ['price'] = $resis['price'];
                            $answ['main_pict'] = $resis['main_pict'];
                            $answ['others_pict'] = json_decode($resis['others_pict']);
                            $qid = $resis['id_catalog'];
                            $result = $mysqli->query("SELECT name FROM catalogs WHERE id = '$qid'");
                            $resis = $result->fetch_assoc();
                            $answ['catalog'] = $resis['name'];

                            header("Content-Type: application/json; charset=utf-8");
                            echo json_encode($answ);
                        }
                        break;
                    }
                    case "vmodals_sizes" : {
                        if(isset($cat_qvery)){
                            $cat_qvery = $mysqli->real_escape_string($cat_qvery);
                            $result = $mysqli->query("SELECT description, sizes FROM vmodals_sizes WHERE name_catalog = '$cat_qvery'");
                            $answ = $result->fetch_assoc();
                            $answ['description'] = json_decode($answ['description']);
                            $answ['sizes'] = json_decode($answ['sizes']);

                            header("Content-Type: application/json; charset=utf-8");
                            echo json_encode($answ);
                        }
                        break;
                    }
                    case "account" : {
                        $user_id = is_authorization();

                        if($user_id){
                            $resultA = $mysqli->query("SELECT login, phone, mail FROM users WHERE id = '$user_id'")->fetch_assoc();
                            $resultB = $mysqli->query("SELECT name, uindex, edge, county, streets, house, apartment FROM user_delivery WHERE user_id = '$user_id'");

                             echo json_encode([
                                "account" => $resultA,
                                "delivery" => $resultB->fetch_assoc()
                             ]);

                        }
                        break;
                    }
                    case "pct" : {
                        $format = $_GET['f'];
                        $defFormat = ['png', 'webp'];
                        $cat_qvery = strtok($cat_qvery, '?');
                        if(isset($cat_qvery)){
                            if(isset($format) and in_array($format, $defFormat)){
                                $pct = $_SERVER["DOCUMENT_ROOT"]. "/pictures/" .$format. "/" .$cat_qvery. "." .$format;
                                $fp = fopen($pct, 'rb');
                                if(file_exists($pct)){
                                    header("Cache-Control: max-age=86400");
                                    header("Content-Type: image/".$format);
                                    echo file_get_contents($pct);
                                }
                            }
                        }
                        break;
                    }
                }
            } break;
        }
    }
}


class is_allive{
    private $mysql;

    public function __construct() {
        $this->mysql = $GLOBALS['$mysqli'];
    }
    public function login($qvery){
        return $this->mysql->query("SELECT id FROM users WHERE login = '$qvery'")->fetch_assoc()["id"];
    }
    public function mail($qvery){
        return $this->mysql->query("SELECT id FROM users WHERE mail  = '$qvery'")->fetch_assoc()["id"];
    }
    public function phone($qvery){
        return $this->mysql->query("SELECT id FROM users WHERE phone = '$qvery'")->fetch_assoc()["id"];
    }
}


function user_autorise($user_id){
    $mysqli = $GLOBALS['$mysqli'];
    $token = generate_token();
    $mysqli->query("INSERT INTO authorization (user_id, token) VALUES ('$user_id', '$token')");
    return $token;
}

function generate_token(){
    $sumb = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm123456789";  //61
    $token = "";
    for ($i=0; $i<=24; $i++){
        $token =$token.$sumb[random_int(0, 60)];
    }
    return $token;
}

function is_authorization(){
    if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $mysqli = $GLOBALS['$mysqli'];
        $token = explode(" ", $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        if (isset($token[0]) and $token[0] == "Bearer"){
            $qvery = $token[1];
            return $mysqli->query("SELECT user_id FROM authorization WHERE token = '$qvery'")->fetch_assoc()["user_id"];
        }
    }
    return false;
}

function cors() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}