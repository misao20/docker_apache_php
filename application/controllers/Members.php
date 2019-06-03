<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('members_model');
	}

	/**
	 * @param $result
	 * @param $data
	 * @return mixed
	 */
	private function output_msg($result, $data) {
		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['result' => $result, 'data' => $data]));
	}

	/**
	 * 회원 데이터 생성(회원 가입)
	 */
	public function signup() {
		$data = (array) json_decode($this->input->raw_input_stream, TRUE);

		if(0 >= count($data)) {
			$data = $this->input->post();
		}

		$this->form_validation->set_data($data);

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirm', 'trim|required|min_length[6]|matches[password]');
		$this->form_validation->set_rules('username', 'User Name', 'trim|required');
		$this->form_validation->set_rules('cell_phone', 'Cell Phone', 'trim|required|min_length[12]');

		if($this->form_validation->run()==FALSE) {
			$this->output_msg('error', $this->form_validation->error_array());
		}
		else {
			$data['rcmd_code'] = $data['rcmd_code']?:null;
			$data['marketing'] = $data['marketing']==="on"?"Y":"N";
			// 비밀번호 확인코드는 디비에 저장하지 않으므로 제거.
			unset($data['password_confirm']);

			$str = $this->members_model->insert_member($data);

			if($str) {
				$this->output_msg('ok', ['msg' => '회원가입이 완료되었습니다.']);
			} else {
				$this->output_msg('error', ['msg' => '회원가입에 실패했습니다.']);
			}
		}
	}

	/**
	 * 회원 데이터 수정
	 */
	public function update() {
		$mid = $this->uri->segment(2);
		// member 존재여부 확인
		$user = $this->members_model->get_member($mid);

		if(0 >= count($user)) {
			$this->output_msg('error', ['msg' => '회원 정보가 없습니다.']);
		}
		else {
			$data = (array) json_decode($this->input->raw_input_stream, TRUE);
			if (0 >= count($data)) {
				parse_str($this->input->raw_input_stream, $data);
			}

			// form_validation
			$this->form_validation->set_data($data);
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

			// password 변경시에만 입력값 확인
			if(0 < strlen($data['password']) || 0 < strlen($data['password_confirm'])) {
				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
				$this->form_validation->set_rules('password_confirm', 'Password Confirm', 'trim|required|min_length[6]|matches[password]');
			}
			else {
				unset($data['password']);
				unset($data['password_confirm']);
			}
			$this->form_validation->set_rules('username', 'User Name', 'trim|required');
			$this->form_validation->set_rules('cell_phone', 'Cell Phone', 'trim|required|min_length[12]');

			if($this->form_validation->run()==FALSE) {
				$this->output_msg('error', $this->form_validation->error_array());
			}
			else {
				$data['rcmd_code'] = $data['rcmd_code']?:null;
				$data['marketing'] = $data['marketing']==="on"?"Y":"N";
				// 추천코드는 수정할 수 없음.
				unset($data['rcmd_code']);
				// 비밀번호 확인코드는 디비에 저장하지 않으므로 제거.
				unset($data['password_confirm']);

				$str = $this->members_model->update_member($data, $mid);

				if($str)
					$this->output_msg('ok', ['msg' => '회원 정보가 수정되었습니다.']);
				else
					$this->output_msg('error', ['msg' => '회원 정보 수정에 실패하였습니다.']);
			}
		}
	}

	/**
	 * 회원 데이터 삭제
	 */
	public function delete() {
		$mid = $this->uri->segment(2);
		$user = $this->members_model->get_member($mid);

		if(0 >= count($user)) {
			$this->output_msg('error', ['msg' => '삭제할 회원 정보가 없습니다.']);
		}
		else {
			$str = $this->members_model->delete_member($mid);

			if($str)
				$this->output_msg('ok', ['msg' => '회원 탈퇴 처리 되었습니다.']);
			else
				$this->output_msg('error', ['msg' => '회원 탈퇴 처리중 오류가 발생하였습니다.']);
		}
	}

	/**
	 * 하나의 회원 데이터 출력
	 */
	public function member() {
		$id = $this->uri->segment(2);
		$data = $this->members_model->get_member($id);

		if(sizeof($data)) {
			$this->output_msg('ok', $data);
		}
		else {
			$this->output_msg('error', ['msg' => '회원 정보가 없습니다.']);
		}
	}

	/**
	 * 여러 회원 데이터 출력(페이지를 나눠 출력)
	 */
	public function membersList() {
		$page = $this->uri->segment(2)?:1;
		$limit = $this->input->get('limit')?:10;
		$offset = ($page-1)*$limit;

		$data = $this->members_model->get_members_list($limit, $offset);

		if(sizeof($data)) {
			$this->output_msg('ok', $data);
		}
		else {
			$this->output_msg('error', ['msg' => '회원 정보가 없습니다.']);
		}
	}

}
