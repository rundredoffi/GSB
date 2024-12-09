<?php
	echo form_open(site_url($action)); 
	echo '<div class="piedForm">';
		echo '<p>';
			$data = [
				'type' 	=> 'hidden', 
				'name' => 'idFiche',
				'value' => $idFiche,
			];
			echo form_input($data);
			$data = [	'type' 	=> 'submit', 
						'class'	=> 'boutonaction',
						'value' => $label,
						'size'  => '50' 
					];
			echo form_input($data);
		echo '</p>';
	echo '</div>';
	echo form_close();
?>	
