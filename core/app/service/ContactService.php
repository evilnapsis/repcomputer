<?php
namespace App\Service;

use ContactData;

class ContactService {
	public function getAll(): array {
		return ContactData::getAll();
	}

	public function getClients(): array {
		return ContactData::getAllBy("type", 1);
	}

	public function getById(int $id): ?ContactData {
		return ContactData::getById($id);
	}

	public function create(array $data): bool {
		$contact = new ContactData();
		$contact->name = $data['name'] ?? '';
		$contact->lastname = $data['lastname'] ?? '';
		$contact->email = $data['email'] ?? '';
		$contact->address = $data['address'] ?? '';
		$contact->phone = $data['phone'] ?? '';
		$contact->phone2 = $data['phone2'] ?? '';
		$contact->company = $data['company'] ?? '';
		$contact->cp = $data['cp'] ?? '';
		$contact->city = $data['city'] ?? '';
		$contact->description = $data['description'] ?? '';
		$contact->type = 1; // Esta edicion solo maneja clientes.
		$contact->is_active = 1;

		$res = $contact->add();
		return $res ? true : false;
	}

	public function update(int $id, array $data): bool {
		$contact = ContactData::getById($id);
		if (!$contact) return false;

		$contact->name = $data['name'] ?? $contact->name;
		$contact->lastname = $data['lastname'] ?? $contact->lastname;
		$contact->email = $data['email'] ?? $contact->email;
		$contact->address = $data['address'] ?? $contact->address;
		$contact->phone = $data['phone'] ?? $contact->phone;
		$contact->phone2 = $data['phone2'] ?? $contact->phone2;
		$contact->company = $data['company'] ?? $contact->company;
		$contact->cp = $data['cp'] ?? $contact->cp;
		$contact->city = $data['city'] ?? $contact->city;
		$contact->description = $data['description'] ?? $contact->description;
		$contact->type = 1; // Esta edicion solo maneja clientes.
		$contact->is_active = isset($data['is_active']) ? intval($data['is_active']) : $contact->is_active;

		$contact->update();
		return true;
	}

	public function delete(int $id): bool {
		$contact = ContactData::getById($id);
		if ($contact) {
			$contact->del();
			return true;
		}
		return false;
	}
}
