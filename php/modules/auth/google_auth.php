<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (isset($_GET['intent'])) {
    $_SESSION['auth_intent'] = $_GET['intent'];
}

$googleClientId = $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID');
$googleClientSecret = $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET');
$redirectUri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/google_callback";

if (!$googleClientId || !$googleClientSecret) {
    die("Google Login is nog niet correct geconfigureerd door de beheerder (Ontbrekende API Keys).");
}

$provider = new League\OAuth2\Client\Provider\Google([
    'clientId'     => $googleClientId,
    'clientSecret' => $googleClientSecret,
    'redirectUri'  => $redirectUri,
]);

// Fetch the authorization URL from the provider; this returns the
// urlGenerate method with necessary parameters.
$authUrl = $provider->getAuthorizationUrl([
    'scope' => [
        'email',
        'profile'
    ]
]);

// Get the state generated for you and store it to the session.
$_SESSION['oauth2state'] = $provider->getState();

// Redirect the user to the authorization URL.
header('Location: ' . $authUrl);
exit;
