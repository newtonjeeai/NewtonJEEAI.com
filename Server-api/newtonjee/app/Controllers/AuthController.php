<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use Google\Client as GoogleClient;
use Google\Service\Oauth2;

class AuthController extends BaseController
{
    private function googleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope('email');
        $client->addScope('profile');
        $client->setAccessType('online');
        $client->setPrompt('select_account');
        return $client;
    }

    // ── Step 1: Redirect student to Google ──────────────────────
    public function redirectToGoogle(array $params = []): void
    {
        // Already logged in?
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $client = $this->googleClient();

        // Generate and store state for CSRF protection
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;
        $client->setState($state);

        $authUrl = $client->createAuthUrl();
        $this->redirect($authUrl);
    }

    // ── Step 2: Handle callback from Google ─────────────────────
    public function handleGoogleCallback(array $params = []): void
    {
        // Verify state
        $returnedState = $_GET['state'] ?? '';
        if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $returnedState)) {
            $this->flash('error', 'Authentication failed: invalid state. Please try again.');
            $this->redirect('/auth/google');
        }
        unset($_SESSION['oauth_state']);

        // Check for error from Google
        if (isset($_GET['error'])) {
            $this->flash('error', 'Google sign-in was cancelled or failed.');
            $this->redirect('/auth/google');
        }

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $this->flash('error', 'No authorisation code received from Google.');
            $this->redirect('/auth/google');
        }

        try {
            $client = $this->googleClient();
            $token  = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                throw new \RuntimeException($token['error_description'] ?? $token['error']);
            }

            $client->setAccessToken($token);

            $oauth2    = new Oauth2($client);
            $googleUser = $oauth2->userinfo->get();

            $googleId = $googleUser->id;
            $email    = strtolower($googleUser->email);
            $name     = $googleUser->name;
            $avatar   = $googleUser->picture ?? null;

        } catch (\Throwable $e) {
            error_log('Google OAuth error: ' . $e->getMessage());
            $this->flash('error', 'Sign-in failed. Please try again.');
            $this->redirect('/auth/google');
        }

        // ── Find or create user ──────────────────────────────────
        $user = Database::queryOne(
            'SELECT * FROM users WHERE google_id = ? OR email = ? LIMIT 1',
            [$googleId, $email]
        );

        if ($user) {
            // Link Google ID if not set yet (e.g. admin pre-created the account)
            if (empty($user['google_id'])) {
                Database::execute(
                    'UPDATE users SET google_id = ?, avatar_url = ?, last_login_at = NOW() WHERE id = ?',
                    [$googleId, $avatar, $user['id']]
                );
            } else {
                Database::execute(
                    'UPDATE users SET avatar_url = ?, last_login_at = NOW() WHERE id = ?',
                    [$avatar, $user['id']]
                );
            }

            // Block deactivated accounts
            if (!$user['is_active']) {
                $this->flash('error', 'Your account has been deactivated. Please contact support.');
                $this->redirect('/auth/google');
            }

        } else {
            // Auto-create new student account
            $userId = Database::insert(
                'INSERT INTO users (name, email, google_id, avatar_url, role, last_login_at) VALUES (?,?,?,?,?,NOW())',
                [$name, $email, $googleId, $avatar, ROLE_STUDENT]
            );
            $user = Database::queryOne('SELECT * FROM users WHERE id = ?', [$userId]);
        }

        // ── Set session ──────────────────────────────────────────
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        // ── Redirect ─────────────────────────────────────────────
        if (!$user['profile_complete']) {
            $this->redirect('/setup-profile');
        }

        $this->redirect('/dashboard');
    }

    // ── Logout ───────────────────────────────────────────────────
    public function logout(array $params = []): void
    {
        session_unset();
        session_destroy();
        $this->redirect('/auth/google');
    }

    // ── Login page (shown before Google redirect) ─────────────
    public function loginPage(array $params = []): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        // No layout needed — simple page
        include VIEWS_PATH . '/auth/login.php';
    }
}
