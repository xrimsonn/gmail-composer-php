<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;

function getClient()
{
  $client = new Client();
  $client->setApplicationName('Mail Sender');
  $client->setScopes([
    'https://www.googleapis.com/auth/gmail.modify',
    'https://www.googleapis.com/auth/userinfo.profile'
  ]);
  $client->setRedirectUri("http://localhost/gmail-php/send_gmail.php");
  $client->setAccessType('offline');
  $client->setPrompt('select_account consent');

  $credentialsFile = 'combined_cred.json';
  $tokenPath = 'token.json';

  if (file_exists($tokenPath)) {
    $accessToken = json_decode(file_get_contents($tokenPath), true);
    $client->setAccessToken($accessToken);
  } else {
    $client->setAuthConfig($credentialsFile);
  }

  if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
      if (isset($_GET['code'])) {
        @$authCode = trim($_GET['code']);
      } else {
        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl);
      }
      $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
      $client->setAccessToken($accessToken);
    }
    if (!file_exists(dirname($tokenPath))) {
      mkdir(dirname($tokenPath), 0700, true);
    }
    @file_put_contents($tokenPath, json_encode($client->getAccessToken()));
  }

  return $client;
}

$client = getClient();
$service = new Gmail($client);
$message = new Gmail\Message();

$sqlSelect = "SELECT id, recipient_email, email_subject, message_text FROM gmail";

if ($stmtSelect = $conn->prepare($sqlSelect)) {
  if ($stmtSelect->execute()) {
    $stmtSelect->bind_result($id, $to, $subject, $msg);
    $stmtSelect->fetch();
    $stmtSelect->close();

    if ($id) {
      $sqlDelete = "TRUNCATE TABLE gmail";

      if ($stmtDelete = $conn->prepare($sqlDelete)) {
        if (!$stmtDelete->execute()) {
          echo "Error al eliminar el registro: " . $stmtDelete->error;
        }

        $stmtDelete->close();
      } else {
        echo "Error en la preparación de la consulta de eliminación: " . $conn->error;
      }
    } else {
      echo "No se encontraron registros en la base de datos.";
    }
  } else {
    echo "Error al ejecutar la consulta de selección: " . $stmtSelect->error;
  }
} else {
  echo "Error en la preparación de la consulta de selección: " . $conn->error;
}

$conn->close();

$userEmail = '';
$peopleService = new Google\Service\PeopleService($client);
$person = $peopleService->people->get('people/me', array('personFields' => 'emailAddresses'));

if (isset($person->emailAddresses[0]->value))
  $userEmail = $person->emailAddresses[0]->value;

$mimeMessage = "MIME-Version: 1.0\r\n";
$mimeMessage .= "From: <" . $userEmail . ">\r\n";
$mimeMessage .= "To: <" . $to . ">\r\n";
$mimeMessage .= "Subject: " . $subject . "\r\n";
$mimeMessage .= "Date: " . date(DATE_RFC2822) . "\r\n";
$mimeMessage .= "Content-Type: multipart/alternative; boundary=test\r\n\r\n";
$mimeMessage .= "Content-Type: text/plain; charset=UTF-8\r\n";
$mimeMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
$mimeMessage .= base64_encode($msg) . "\r\n";

$encodedEmail = base64_encode($mimeMessage);
$message->setRaw($encodedEmail);
$message = $service->users_messages->send('me', $message);

header('Location: results.php');
