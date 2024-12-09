<?php 
    echo '<div id="sousContenu">';
        echo form_open(site_url('ChangementMdp/valider_mdp')); 
        echo '<div class="corpsForm">';
            echo '<p>';
                echo form_label('Ancien mot de passe*', 'ancienMdp');  
                $data = [	'type'      => 'password',
                            'name'      => 'ancienMdp',
                            'id'        => 'ancienMdp',
                            'maxlength'	=> '45',
                            'size'      => '15',
                        ];
                echo form_input($data);   
            echo '</p>';
            echo '<p>';
                echo form_label('Nouveau mot de passe*', 'pwdMdp');  
                $data = [	'type'      => 'password',
                            'name'      => 'pwdMdp',
                            'id'        => 'pwdMdp',
                            'maxlength'	=> '45',
                            'size'      => '15',
                        ];
                echo form_input($data);   
            echo '</p>';
            echo '<h3> Restrictions de sécurité pour le mot de passe </h3>';
            echo '<ul>';
                echo '<li> Au moins 8 caractères </li>';
                echo '<li> Au moins une lettre majuscule </li>';
                echo '<li> Au moins une lettre minuscule </li>';
                echo '<li> Au moins un chiffre </li>';
                echo '<li> Au moins un caractère spécial (@, !,%) </li>';
            echo '</ul>';
            echo '<p>';
                echo form_label('Confirmation du nouveau mot de passe*', 'confPwdMdp');  
                $data = [	'type'      => 'password',
                            'name'      => 'confPwdMdp',
                            'id'        => 'confPwdMdp',
                            'maxlength'	=> '45',
                            'size'      => '15',
                        ];
                echo form_input($data);   
            echo '</p>';
        echo '</div>';
        echo '<div class="piedForm">';
            if (!$enable) :
            echo '<p>';
            
                $data = [	'type' 	=> 'submit', 
                            'class'	=> 'bouton',
                            'value' => 'Valider',
                            'size'  => '20' 
                        ]; 
                echo form_input($data);
                $data = [	'type' 	=> 'reset',
                            'class'	=> 'bouton',
                            'value' => 'Effacer',
                            'size'  => '20',
                        ];
                echo form_input($data);
            echo '</p>';
            endif;
        echo '</div>';
        echo form_close();      
    echo '</div>'; 
?>