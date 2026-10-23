<?php
namespace App\Service;

use UserData;

class UserService {
	public function getAll(): array {
		return UserData::getAll();
	}

	public function getById(int $id): ?UserData {
		return UserData::getById($id);
	}

	public function create(array $data): bool {
		$user = new UserData();
		$user->name = $data['name'] ?? '';
		$user->lastname = $data['lastname'] ?? '';
		$user->username = $data['username'] ?? '';
		$user->email = $data['email'] ?? '';
		$user->password = sha1(md5($data['password'] ?? '12345'));
		$user->type = 1; // Edicion de rol unico: todos los usuarios son administradores.
		$user->is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;

		$res = $user->add();
		return $res ? true : false;
	}

	public function update(int $id, array $data): bool {
		$user = UserData::getById($id);
		if (!$user) return false;

		$user->name = $data['name'] ?? $user->name;
		$user->lastname = $data['lastname'] ?? $user->lastname;
		$user->username = $data['username'] ?? $user->username;
		$user->email = $data['email'] ?? $user->email;
		$user->type = 1; // Edicion de rol unico: todos los usuarios son administradores.
		$user->is_active = isset($data['is_active']) ? intval($data['is_active']) : $user->is_active;

		$user->update();

		if (!empty($data['password'])) {
			$user->password = sha1(md5($data['password']));
			$user->update_passwd();
		}

		return true;
	}

	public function changePassword(int $id, string $newPass): bool {
		$user = UserData::getById($id);
		if (!$user) return false;

		$user->password = sha1(md5($newPass));
		$user->update_passwd();
		return true;
	}

	public function delete(int $id): bool {
		$currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
		if ($id === $currentUserId) {
			return false;
		}
		$user = UserData::getById($id);
		if ($user) {
			$user->del();
			return true;
		}
		return false;
	}
}
