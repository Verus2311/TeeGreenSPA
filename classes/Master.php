<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			if(isset($sql))
			$resp['sql'] = $sql;
			return json_encode($resp);
			exit;
		}
	}
	function save_appointment(){
		extract($_POST);
		$sched_set_qry = $this->conn->query("SELECT * FROM `schedule_settings`");
		$sched_set = array_column($sched_set_qry->fetch_all(MYSQLI_ASSOC),'meta_value','meta_field');
		$morning_start = date("Y-m-d ") . explode(',',$sched_set['morning_schedule'])[0];
		$morning_end = date("Y-m-d ") . explode(',',$sched_set['morning_schedule'])[1];
		$afternoon_start = date("Y-m-d ") . explode(',',$sched_set['afternoon_schedule'])[0];
		$afternoon_end = date("Y-m-d ") . explode(',',$sched_set['afternoon_schedule'])[1];
		$sched_time = date("Y-m-d ") . date("H:i",strtotime($date_sched));
		
		if(!in_array(strtolower(date("l",strtotime($date_sched))),explode(',',strtolower($sched_set['day_schedule'])))){
			$resp['status'] = 'failed';
			$resp['msg'] = "Selected Schedule Day of Week is invalid.";
			return json_encode($resp);
			exit;
		}
		if(!( (strtotime($sched_time) >= strtotime($morning_start) && strtotime($sched_time) <= strtotime($morning_end)) || (strtotime($sched_time) >= strtotime($afternoon_start) && strtotime($sched_time) <= strtotime($afternoon_end)) )){
			$resp['status'] = 'failed';
			$resp['msg'] = "Selected Schedule Time is invalid.";
			return json_encode($resp);
			exit;
		}

		if(empty($patient_id))
			$sql = "INSERT INTO `patient_list` set name = '{$name}'  ";
		else
			$sql = "UPDATE `patient_list` set name = '{$name}' where id = '{$id}'  ";
		
		$save_inv = $this->conn->query($sql);
		$this->capture_err();
		
		if($save_inv){
			$patient_id = (empty($patient_id))? $this->conn->insert_id : $patient_id;
			
			// Save the main appointment
			if(empty($id))
				$sql = "INSERT INTO `appointments` set date_sched = '{$date_sched}', patient_id = '{$patient_id}', `status` = '{$status}', `ailment` = '{$ailment}' ";
			else
				$sql = "UPDATE `appointments` set date_sched = '{$date_sched}', patient_id = '{$patient_id}', `status` = '{$status}', `ailment` = '{$ailment}' where id = '{$id}' ";

			$save_sched = $this->conn->query($sql);
			$this->capture_err();
			
			if($save_sched){
				// Delete existing meta data
				$this->conn->query("DELETE FROM `patient_meta` where patient_id = '{$patient_id}'");
				
				// Save basic patient information
				$meta_data = array(
					'name' => $name,
					'Age' => $Age,
					'contact' => $contact,
					'gender' => $gender,
					'dob' => $dob,
					'address' => $address
				);
				
				$data = "";
				foreach($meta_data as $k => $v){
					if(!empty($data)) $data .= ", ";
					$data .= " ({$patient_id},'{$k}','{$v}')";
				}
				
				// Save services and therapists
				if(isset($services) && is_array($services)){
					foreach($services as $index => $service){
						if(!empty($data)) $data .= ", ";
						$data .= " ({$patient_id},'service_{$index}','{$service}')";
						if(isset($therapists[$index])){
							$data .= ", ({$patient_id},'therapist_{$index}','{$therapists[$index]}')";
						}
					}
				}
				
				if(!empty($data)){
					$sql = "INSERT INTO `patient_meta` (patient_id,meta_field,meta_value) VALUES {$data}";
					$save_meta = $this->conn->query($sql);
					$this->capture_err();
					
					if($save_meta){
						$resp['status'] = 'success';
						$resp['name'] = $name;
						$this->settings->set_flashdata('success',' Appointment successfully saved');
					}else{
						$resp['status'] = 'failed';
						$resp['msg'] = "There's an error while saving the appointment details.";
					}
				}else{
					$resp['status'] = 'failed';
					$resp['msg'] = "No data to save.";
				}
			}else{
				$resp['status'] = 'failed';
				$resp['msg'] = "There's an error while saving the appointment.";
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "There's an error while saving the patient information.";
		}
		return json_encode($resp);
	}
	function multiple_action(){
		extract($_POST);
		if($_action != 'delete'){
			$stat_arr = array("pending"=>0,"confirmed"=>1,"cancelled"=>2);
			$status = $stat_arr[$_action];
			$sql = "UPDATE `appointments` set status = '{$status}' where patient_id in (".(implode(",",$ids)).") ";
			$process = $this->conn->query($sql);
			$this->capture_err();
		}else{
			$sql = "DELETE s.*,i.*,im.* from  `appointments` s inner join `patient_list` i on s.patient_id = i.id  inner join `patient_meta` im on im.patient_id = i.id where s.patient_id in (".(implode(",",$ids)).") ";
			$process = $this->conn->query($sql);
			$this->capture_err();
		}
		if($process){
			$resp['status'] = 'success';
			$act = $_action == 'delete' ? "Deleted" : "Updated";
			$this->settings->set_flashdata("success","Appointment/s successfully ".$act);
		}else{
			$resp['status'] = 'failed';
			$resp['error_sql'] = $sql;
		}
		return json_encode($resp);
	}
	function save_sched_settings(){
		$data = "";
		foreach($_POST as $k => $v){
			if(is_array($_POST[$k]))
			$v = implode(',',$_POST[$k]);
			if(!empty($data)) $data .= ",";
			$data .= " ('{$k}','{$v}') ";
		}
		$sql = "INSERT INTO `schedule_settings` (`meta_field`,`meta_value`) VALUES {$data}";
		if(!empty($data)){
			$this->conn->query("DELETE FROM `schedule_settings`");
			$this->capture_err();
		}
		$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',' Schedule settings successfully updated');
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error;
			$resp['msg'] = "An Error occure while excuting the query.";

		}
		return json_encode($resp);
	}
	function check_therapist_availability(){
		extract($_POST);
		$resp = array();
		
		// Service durations in minutes
		$service_durations = array(
			"Triple Bogey Strokes" => 75,
			"Hole-in-One Strokes" => 50,
			"Par Strokes" => 50,
			"Eagle Strokes" => 50,
			"Condor Stroke" => 120,
			"Body Scrub" => 30,
			"Body Scrub w/ full body massage" => 80,
			"Hot Compress or Ear Candle w/ full body massage" => 70
		);

		// Group services by therapist
		$therapist_services = [];
		foreach($therapists as $i => $therapist) {
			if (!isset($therapist_services[$therapist])) $therapist_services[$therapist] = [];
			$therapist_services[$therapist][] = $services[$i];
		}

		foreach($therapist_services as $therapist => $services_for_therapist) {
			$total_duration = 0;
			foreach($services_for_therapist as $service) {
				$total_duration += isset($service_durations[$service]) ? $service_durations[$service] : 30;
			}
			$appointment_time = strtotime($date_sched);
			$appointment_end = $appointment_time + ($total_duration * 60);

			// Check for conflicts with existing appointments for this therapist
			$conflict_check = $this->conn->query(
				"SELECT * FROM `appointments` a
				INNER JOIN `patient_meta` pm ON a.patient_id = pm.patient_id
				WHERE pm.meta_field LIKE 'therapist%'
				AND pm.meta_value = '{$therapist}'
				AND (
					('{$appointment_time}' BETWEEN UNIX_TIMESTAMP(a.date_sched) AND UNIX_TIMESTAMP(DATE_ADD(a.date_sched, INTERVAL 30 MINUTE)))
					OR 
					('{$appointment_end}' BETWEEN UNIX_TIMESTAMP(a.date_sched) AND UNIX_TIMESTAMP(DATE_ADD(a.date_sched, INTERVAL 30 MINUTE)))
					OR 
					(UNIX_TIMESTAMP(a.date_sched) BETWEEN '{$appointment_time}' AND '{$appointment_end}')
				)
				" . (isset($appointment_id) && $appointment_id > 0 ? " AND a.id != '{$appointment_id}'" : "")
			);

			if($conflict_check->num_rows > 0) {
				$resp['status'] = 'conflict';
				$resp['message'] = "Therapist {$therapist} has a scheduling conflict at the selected time.";
				return json_encode($resp);
			}
		}

		$resp['status'] = 'success';
		return json_encode($resp);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_appointment':
		echo $Master->save_appointment();
	break;
	case 'multiple_action':
		echo $Master->multiple_action();
	break;
	case 'save_sched_settings':
		echo $Master->save_sched_settings();
	break;
	case 'check_therapist_availability':
		echo $Master->check_therapist_availability();
	break;
	default:
		// echo $sysset->index();
		break;
}