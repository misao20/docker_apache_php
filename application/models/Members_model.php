<?php

class Members_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function insert_member($data) {
		$this->db->trans_start();

		$this->db->insert('members', $data);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function update_member($data, $mid) {
		$this->db->trans_start();

		$this->db
			->set($data)
			->set('updateDate', 'NOW()', FALSE)
			->where('id', $mid)
			->update('members');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function delete_member($mid) {
		$this->db->trans_start();

		$this->db
			->set('state', 'X')
			->set('updateDate', 'NOW()', FALSE)
			->where('id', $mid)
			->update('members');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function get_member($mid) {
		$where = [
			'id' => $mid,
			'state' => 'Y'
		];
		$query = $this->db
			->where($where)
			->get('members');

		return $query->row();
	}

	public function get_members_list($limit, $offset) {
		$query = $this->db
			->limit($limit, $offset)
			->get('members');

		return $query->result();
	}
}
?>