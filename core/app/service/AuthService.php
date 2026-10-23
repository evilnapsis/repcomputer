<?php
namespace App\Service;

use UserData;
use Session;

class AuthService {
	public static function login(string $username, string $password): bool {
		// En RepComputer la contraseña en DB usa sha1(md5(pass))
		$hash = sha1(md5($password));
		$user = UserData::getBy("username", $username);
		
		if (!$user) {
			$user = UserData::getBy("email", $username);
		}

		if ($user && $user->password === $hash) {
			if ($user->is_active == 1) {
				$_SESSION['user_id'] = $user->id;
				$_SESSION['user_type'] = $user->type;
				return true;
			}
		}
		return false;
	}

	public static function check(): bool {
		return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
	}

	public static function user(): ?UserData {
		if (self::check()) {
			return UserData::getById($_SESSION['user_id']);
		}
		return null;
	}

	public static function logout(): void {
		if (isset($_SESSION['user_id'])) {
			unset($_SESSION['user_id']);
			unset($_SESSION['user_type']);
		}
		session_destroy();
	}
}
