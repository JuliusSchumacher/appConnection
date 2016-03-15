<?php

//Check input for illegal expressions

foreach($_GET as $ob) {
    if(strpos($ob, ';') !== false) {
        echo "Illegal input";
        exit();
    }
}

$intent = $_GET['intent'];

switch($intent) {
    case "login":
        login();
        break;
    case "create":
        create();
        break;
    case "search":
        search();
        break;
    case "update":
        update();
        break;
    case "view":
        view();
        break;
    case "contact":
        contact();
        break;
    case "list":
        listCont();
        break;
    case "views":
        canView();
        break;
    default:
        echo "No intent found.";
}

function login() {
    $user = $_GET["user"];
    $pass = $_GET["pass"];

    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $STH = $conn->prepare("SELECT ID FROM users WHERE PASSWORD='$pass' AND NME='$user'");


    $STH->execute();

    $result = $STH->fetch();

    $id = $result["ID"];

    if($id !== null) {
        //Create new session-token
        $token = mt_rand(1000000000000000, 9999999999999999);

        $STH = $conn->prepare("UPDATE users SET TOKEN='$token' WHERE ID = '$id'");

        $STH->execute();

        echo $token;
    } else {
        echo "Failure";
    }
}

function create() {
    $user = $_GET["user"];
    $mail = $_GET["mail"];
    $pass = $_GET["pass"];
    $number = $_GET["number"];

    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $STH = $conn->prepare("SELECT ID FROM users WHERE MAIL='$mail'");

    $STH->execute();

    $result = $STH->fetch();

    if($result["ID"] == null) {
        $STH = $conn->prepare("INSERT INTO users (NME, MAIL, PASSWORD, NUMBER) VALUES ('$user', '$mail', '$pass', '$number')");

        $STH->execute();

        echo "Success";
    }else {
        echo "Failure";
    }
}

function search() {
    //Get the query and prepare it for search
    $query = '%'.$_GET["query"].'%';

    //Database connection
    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Find the query in name, mail or number
    $STH = $conn->prepare("SELECT ID FROM users WHERE NME LIKE '$query' OR MAIL LIKE '$query' OR NUMBER LIKE '$query'");

    $STH->execute();

    $result = $STH->fetch();

    //Return ID of search if successful
    if($result["ID"] !== null) {
        echo $result["ID"];
    } else {
        echo "Returned nothing";
    }
}

function update() {
    //Get session token, position and time
    $token = $_GET["token"];
    $pos = $_GET["pos"];
    $time = time();

    //Database connection
    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $STH = $conn->prepare("SELECT ID FROM users WHERE TOKEN = '$token'");

    $STH->execute();

    $result = $STH->fetch();
    $id = $result["ID"];

    if($result["ID"] !== null) {
        $STH = $conn->prepare("UPDATE users SET POSITION='$pos', TIME='$time' WHERE ID = '$id'");

        $STH->execute();
        echo "Success";
    } else {
        echo "Failure";
    }
}

function view() {
    //get token and id of person that should be viewed
    $token = $_GET["token"];
    $query = $_GET["query"];



    //Database connection
    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $STH = $conn->prepare("SELECT ID FROM users WHERE TOKEN = '$token'");

    $STH->execute();

    $result = $STH->fetch();
    $id = "%".$result["ID"]."_%";


    //Does Query exist and is user in contacts?
    if($result["ID"] !== null) {
        $STH = $conn->prepare("SELECT ID FROM users WHERE ID = '$query' AND CONTACTS LIKE '$id'");

        $STH->execute();

        $result = $STH->fetch();

        if($result["ID"] !== null) {
            //Does Query show user position?
            $STH = $conn->prepare("SELECT ID FROM users WHERE ID = '$query' AND VIEW LIKE '$id'");

            $STH->execute();

            $result = $STH->fetch();

            if($result["ID"] !== null) {
                //Show position and user info of query
                $STH = $conn->prepare("SELECT NME, POSITION, TIME FROM users WHERE ID = '$query'");

                $STH->execute();

                $result = $STH->fetch();
                echo $result["NME"]."_".$result["TIME"]."_".$result["POSITION"];
            } else {
                //Show user info of query
                $STH = $conn->prepare("SELECT NME FROM users WHERE ID = '$query'");

                $STH->execute();

                $result = $STH->fetch();
                echo $result["NME"];
            }
        } else {
            echo "Failure";
        }
    } else {
        echo "Failure";
    }
}

function contact() {
    //get token and what you want to edit, view or contacts, add or remove, and ID of person that gets edited
    $token = $_GET["token"];
    $userID = $_GET["id"];
    $edit = $_GET["edit"];
    $add = $_GET["add"];

    $userID = $userID."_";
    //Database connection
    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $STH = $conn->prepare("SELECT ID FROM users WHERE TOKEN = '$token'");

    $STH->execute();

    $result = $STH->fetch();
    $id = $result["ID"];

    if($result["ID"] !== null) {
        if($edit == "contact") {
            $STH = $conn->prepare("SELECT CONTACTS FROM users WHERE ID = '$id'");
            $STH->execute();
            $result = $STH->fetch();
            $cont = $result["CONTACTS"];
            if($add == "true" and !strpos($cont, $userID) and strpos($cont, $userID) !== 0) {
                $cont = $cont.$userID;
            } else if($add == "false") {
                $cont = str_replace($userID, "", $cont);
            }
            $STH = $conn->prepare("UPDATE users SET CONTACTS='$cont'");
            $STH->execute();
        } else if($edit == "view") {
            $STH = $conn->prepare("SELECT VIEW FROM users WHERE ID = '$id'");
            $STH->execute();
            $result = $STH->fetch();
            $view = $result["VIEW"];
            if($add == "true" and !strpos($view, $userID) and strpos($view, $userID) !== 0) {
                $view = $view.$userID;
            } else if($add == "false") {
                $view = str_replace($userID, "", $view);
            }
            $STH = $conn->prepare("UPDATE users SET VIEW='$view'");
            $STH->execute();
        }
    } else {
        echo "Failure";
    }
}

function listCont() {
    $token = $_GET["token"];
    
    //Database connection
    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $STH = $conn->prepare("SELECT ID FROM users WHERE TOKEN = '$token'");

    $STH->execute();

    $result = $STH->fetch();
    $id = $result["ID"];

    if($result["ID"] !== null) {
        $STH = $conn->prepare("SELECT CONTACTS FROM users WHERE ID = '$id'");
        $STH->execute();
        
        $result = $STH->fetch();
        
        echo $result["CONTACTS"];
    } else {
        echo "Failure";
    }
}

function canView() {
    $token = $_GET["token"];
    $query = $_GET["query"];

    //Database connection
    $server = "mysql4.000webhost.com";
    $db_username = "a9562517_root";
    $db_password = "admin1";
    $database = "a9562517_db";


    $conn = new PDO("mysql:host=$server;dbname=$database", $db_username, $db_password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $STH = $conn->prepare("SELECT ID FROM users WHERE TOKEN = '$token'");

    $STH->execute();

    $result = $STH->fetch();
    $id = $result["ID"];

    if($result["ID"] !== null) {
        $STH = $conn->prepare("SELECT VIEW FROM users WHERE ID = '$id'");
        $STH->execute();

        $result = $STH->fetch();

        $view = $result["VIEW"];

        if(strpos($view, $query) || strpos($view, $query) === 0) {
            echo "true";
        } else {
            echo "false";
        }

    } else {
        echo "Failure";
    }
}