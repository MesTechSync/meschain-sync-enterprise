<?php
namespace Opencart\Admin\Model\User;

class User extends \Opencart\System\Engine\Model {
    public function addUser(array $data): int {
        $this->db->query("INSERT INTO " . DB_PREFIX . "user SET username = '" . $this->db->escape($data['username']) . "', user_group_id = '" . (int)$data['user_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', image = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

        $user_id = $this->db->getLastId();

        if ($data['password']) {
            $salt = substr(md5(uniqid(rand(), true)), 0, 9);
            $this->db->query("UPDATE " . DB_PREFIX . "user SET salt = '" . $this->db->escape($salt) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE user_id = '" . (int)$user_id . "'");
        }

        return $user_id;
    }

    public function editUser(int $user_id, array $data): void {
        $this->db->query("UPDATE " . DB_PREFIX . "user SET username = '" . $this->db->escape($data['username']) . "', user_group_id = '" . (int)$data['user_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', image = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "' WHERE user_id = '" . (int)$user_id . "'");

        if ($data['password']) {
            $salt = substr(md5(uniqid(rand(), true)), 0, 9);
            $this->db->query("UPDATE " . DB_PREFIX . "user SET salt = '" . $this->db->escape($salt) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE user_id = '" . (int)$user_id . "'");
        }
    }

    public function editPassword(int $user_id, string $password): void {
        $salt = substr(md5(uniqid(rand(), true)), 0, 9);
        $this->db->query("UPDATE " . DB_PREFIX . "user SET salt = '" . $this->db->escape($salt) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE user_id = '" . (int)$user_id . "'");
    }

    public function deleteUser(int $user_id): void {
        $this->db->query("DELETE FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$user_id . "'");
    }

    public function getUser(int $user_id): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$user_id . "'");

        return $query->row;
    }

    public function getUserByUsername(string $username): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE username = '" . $this->db->escape($username) . "'");

        return $query->row;
    }

    public function getUserByEmail(string $email): array {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "user WHERE LCASE(email) = '" . $this->db->escape(strtolower($email)) . "'");

        return $query->row;
    }

    public function getUsers(array $data = array()): array {
        $sql = "SELECT * FROM " . DB_PREFIX . "user";

        $sort_data = array(
            'username',
            'status',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY username";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalUsers(array $data = array()): int {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "user";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getTotalUsersByGroupId(int $user_group_id): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "user WHERE user_group_id = '" . (int)$user_group_id . "'");

        return $query->row['total'];
    }

    public function getTotalUsersByEmail(string $email): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "user WHERE LCASE(email) = '" . $this->db->escape(strtolower($email)) . "'");

        return $query->row['total'];
    }
}
