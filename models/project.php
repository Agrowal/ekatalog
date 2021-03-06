<?php
class ProjectModel extends Model{
	public function index(){

		$this->query('SELECT 
			ProjectID,ProjectName
			FROM Project WHERE
			User_UserID ='. $_SESSION['user_data']['id']
			);
		$rows=$this->resultSet();
		if(!$rows){
			Messages::setMsg('No projects !','error');	
		}
		return $rows;
		
	}

	public function add(){

		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		if($post['submit']){

			if($post['projectName']==''){
			Messages::setMsg('Please Fill In Project Name','error');
			return;
			}
			if(!$_SESSION['user_data']['id']){
			Messages::setMsg('ACCESS VIOLATION','error');
			return;
			}

			// Insert into MySQL
			$tabela='Project';
			$this->query('INSERT INTO '.$tabela.' (ProjectName, User_UserID, ProjectDescription) VALUES (:projectName,:userID,:ProjectDescription)');

			$this->bind(':projectName',$post['projectName']);
			$this->bind(':userID',$_SESSION['user_data']['id']);
			$this->bind(':ProjectDescription',$post['projectDesc']);

			$this->execute();

			// Verify
			if($this->lastInsertId()){
				//Redirect
				header('Location:'.ROOT_URL.'projects/index');
			}
		}
		return ;	
	}


	public function deleteProject(){

		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$tabela='Project';
		$this->query('DELETE FROM '.$tabela.' WHERE ProjectID = :projectID');

		$this->bind(':projectID',$post['projectID']);

		$this->execute();

		header('Location:'.ROOT_URL.'projects/index');

		return;

	}

	public function edit(){

		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);


		if($post['projectID']){
			$_SESSION['active_projectID'] = $post['projectID'];
			$_SESSION['active_projectName'] = $post['projectName'];
		}

		$tabela1='Project';
		$tabela2='Gatunek';
		$tabelaJunction='Project_has_gatunek';

		$this->query('SELECT 
			*
			FROM 
			Project,
			Gatunek,
			Project_has_gatunek
			WHERE 
			ProjectID = phg_ProjectID AND
			phg_GatunekNazwaPL = GatunekNazwaPL AND
			ProjectID ='.$_SESSION['active_projectID']);

		//$this->bind(':projectID',$post['projectID']);

		$rows=$this->resultSet();

		if(!$rows){
			Messages::setMsg('No data','error');

			$this->query('SELECT ProjectName, ProjectID, ProjectDescription FROM project WHERE ProjectID ='.$_SESSION['active_projectID']);
			//$this->bind(':projectID',$post['projectID']);
			$rows=$this->resultSet();
			return $rows;
		}
		return $rows;

	}


	public function deletePosition(){

		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$tabela='Project_has_gatunek';
		$this->query('DELETE FROM '.$tabela.' WHERE 
			phg_GatunekNazwaPL = :gatunekNazwaPL AND
			phg_ProjectID = :projectID');

		$this->bind(':gatunekNazwaPL',$post['GatunekNazwaPL']);
		$this->bind(':projectID',$post['ProjectID']);

		$this->execute();

		header('Location:'.ROOT_URL.'projects/edit');

		return;

	}

	public function addPosition(){
		// Sanitize POST
		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		if($post['addSubmit']){

			// Duplicate check
			$this->query("SELECT * FROM Project_has_gatunek WHERE phg_GatunekNazwaPL = :gatunekNazwaPL AND phg_ProjectID = $_SESSION[active_projectID]");
			$this->bind(':gatunekNazwaPL',$post['insertedNazwa']);	
			$rowAlreadyExists=$this->single();
			if($rowAlreadyExists){
				Messages::setMsg('Position already exists.','error');
				return;
			}

			// Insert into MySQL
			$this->query('INSERT INTO Project_has_gatunek (phg_GatunekNazwaPL, phg_ProjectID) VALUES (:gatunekNazwaPL,'.$_SESSION['active_projectID'].')');

			$this->bind(':gatunekNazwaPL',$post['insertedNazwa']);	

			$this->execute();

			// Verify
			if($this->errorCode()==00000){
				//Redirect
				Messages::setMsg('Wstawiono','success');
				header('Location:'.ROOT_URL.'projects/edit');
			}
			else{
				Messages::setMsg('Error encountered, error number: '.$this->errorCode(),'error');
			}
			
		}

		// Database browse function
		require(dirname(__FILE__)."/../sharedFunctions/browse.php");
		return $rows;

	}

	public function changeProjectName(){

		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		if($post['submit']){

			// Insert into MySQL

			$this->query("UPDATE Project SET ProjectName = :newName WHERE ProjectID = $_SESSION[active_projectID]");
			$this->bind(':newName',$post['projectNewName']);
			$this->execute();

			// return $this->errorCode();		

			// Verify
			if($this->errorCode()==00000){
				//Redirect
				Messages::setMsg('Zmieniono','success');

				unset($_SESSION['active_projectName']);
				$_SESSION['active_projectName'] = $post['projectNewName'];

				header('Location:'.ROOT_URL.'projects/edit');
			}
			else{
				Messages::setMsg('Error encountered, error number: '.$this->errorCode(),'error');
			}
			
		}
		return ;
		
	}

	public function changeProjectDesc(){

		$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		if($post['submit']){

			// Insert into MySQL

			$this->query("UPDATE Project SET ProjectDescription = :newDesc WHERE ProjectID = $_SESSION[active_projectID]");
			$this->bind(':newDesc',$post['projectNewDesc']);
			$this->execute();

			// return $this->errorCode();		

			// Verify
			if($this->errorCode()==00000){
				//Redirect
				Messages::setMsg('Zmieniono','success');

				unset($_SESSION['active_projectName']);
				$_SESSION['active_projectName'] = $post['projectNewName'];

				header('Location:'.ROOT_URL.'projects/edit');
			}
			else{
				Messages::setMsg('Error encountered, error number: '.$this->errorCode(),'error');
			}
			
		}
		return ;
		
	}

}


