    <?php
    echo heading($heading, 4);
    ?>
    <table class="listeLegere">
        <tr>
            <th class="date">Date</th>
            <th class="libelle">Libellé</th>
            <th class="montant">Montant</th>     
            <th class="action">Refus</th>
            <th class="action">Report</th>
        </tr>
<?php   foreach($fhf as $unfhf) : 
            $date = $this->gsb_lib->date_vers_francais($unfhf['date']);
            $libelle = $unfhf['libelleFraisHorsForfait'];
            $montant = $this->gsb_lib->format_montant($unfhf['montant']); 
            $id = $unfhf['idFraisHorsForfait'];
            $refus = substr($libelle, 0, 9) == 'REFUSER :' ? true : false;
            ?>
            <tr>
                <td><?php echo $date ?></td>
                <td class="libelle"><?php echo $libelle ?></td>
                <td class="montant"><?php echo $montant ?></td>
                <td> <!-- bouton refuser -->
                    <?php if(!$refus) :?>
                        <a 	href="<?php echo site_url('validerfrais/refuser_fhf/'.$id.'/'.$idFiche) ?>" 
                            onclick="return confirm('Voulez-vous vraiment refuser ce frais ?');">
                            <img src="<?php echo site_url('../assets/images/delete.png') ?> " />
                        </a>
                    <?php endif ?>
                </td>
                <td> <!-- bouton reporter -->
                    <a 	href="<?php echo  site_url('validerfrais/reporter_fhf/'.$id) ?>" 
                        onclick="return confirm('Voulez-vous vraiment report ce frais ?');">
                        <img src="<?php echo  site_url('../assets/images/redo.png') ?> " />
                    </a>
                </td>
            </tr>
<?php   endforeach ?>
    </table>
    &nbsp;