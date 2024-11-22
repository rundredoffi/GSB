<div id="menuGauche">
	<div id="infosUtil">
		<div id="user">
			<img src="<?php echo site_url('../assets/images/UserIcon.png') ?>" />
		</div>
		<div id="infos">
			<h2><?php echo $this->session->prenom." ".$this->session->nom  ?> </h2>
			<h3><?php echo $this->session->libelleRole?></h3>  
		</div>
		<ul class="menuList">
			<li class="smenu">
				<?php echo anchor('Connexion/deconnexion', 'Déconnexion', 'title="Se déconnecter"'); ?>
			</li>
			<li class="smenu">
				<?php echo anchor('ChangementMdp', 'Changer de mot de passe', 'title="Changer de mot de passe"'); ?>
			</li>
		</ul>    
	</div>  
	<ul id="menuPrincipal" class="menuList">
		<?php foreach ($menus as $menu => $libelle): ?>
			<li class="smenu">
				<?php echo anchor($libelle, $menu, 'title="'.$libelle.'"'); ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>