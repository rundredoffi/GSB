<?php
class Gsb_model extends CI_Model {
/** 
 * Classe d'accès aux données 
 * Hérite de la classe CI_Model
 */

    public function __construct()
    {
        $this->load->database();
    }

/**
 * Retourne les informations d'un utilisateur
 * @param $login 
 * @param $mdp
 * @return tableau associatif contenant l'id, le nom, le prénom et le login
*/
	public function get_infos_utilisateur($login, $mdp){
        $this->db->select(' idUtilisateur, 
                            nom, 
                            prenom, 
                            login, libelleRole, utilisateur.idRole, modification_mdp');
        $this->db->from('utilisateur');
        $this->db->join('role', 'utilisateur.idRole = role.idRole');
        $this->db->where('login', $login);
        $this->db->where('mdp', $mdp);
        $query = $this->db->get();
        return $query->row_array();
    }
/**
 * Permet de modifier le mot de passe d'un utilisateur
 * @param $login
 * @param $mdp
 * 
 */
    public function Modif_Mdp_Utilisateur($login, $mdp){
        $this->db->where('IdUtilisateur', $login);
        $this->db->order_by('idMdp', 'DESC');
        $this->db->limit(10);
        $this->db->not_like('MDP', $mdp);
        $this->db->set('IdUtilisateur', $login);
        $this->db->set('MDP', $mdp);
        $this->db->insert('historique_mdp');
        $query = $this->db->get();
        return $query->row_array();
    }

/**
 * Retourne les informations d'un utilisateur
 * @param $id 
 * @return tableau associatif contenant toutes les informations d'un utilisateur
*/
    public function get_detail_utilisateur($id){ 
        $this->db->from('utilisateur');
        $this->db->where('idUtilisateur', $id);
        $query = $this->db->get();
        return $query->row_array();
    }  

/**
 * Retourne les mois pour lesquel un utilisateur a une fiche de frais
 * @param $idUtilisateur 
 * @return tableau des mois (au format -aaaamm-), de l'année (au format -aaaa-) et du mois (au format -mm-) correspondants 
 * */
    public function get_les_mois_disponibles($idVisiteur){
        $this->db->select(' mois, 
                            SUBSTR(fichefrais.mois, 1, 4) AS numAnnee, 
                            SUBSTR(fichefrais.mois, 5) AS numMois');
        $this->db->from('fichefrais');
        $this->db->where('idVisiteur', $idVisiteur);
        $this->db->order_by('mois', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }    

 /**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 * @param $idVisiteur 
 * @param $mois (sous la forme aaaamm)
 * @return tableau asoociatif avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function get_les_infos_ficheFrais($idVisiteur, $mois){
        $this->db->select(' fichefrais.idEtat, 
                            fichefrais.dateModif, 
                            fichefrais.nbJustificatifs, 
                            fichefrais.montantValide, 
                            etat.libelleEtat');
        $this->db->from('fichefrais');
        $this->db->join('etat', 'fichefrais.idEtat =  etat.idEtat');
        $this->db->where('fichefrais.idVisiteur', $idVisiteur);
        $this->db->where('fichefrais.mois', $mois);
        $query = $this->db->get();
        return $query->row_array();
 	}
    
/**
 * Retourne toutes les lignes de frais forfait d'un visiteur pour un mois donné 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return tableau associatif contenant l'id, le libelle et la quantité pour chaque frais forfait  
*/
    public function get_les_frais_forfait($idVisiteur, $mois){
        $this->db->select(' fraisforfait.idFraisForfait, 
                            fraisforfait.libelleFraisForfait, 
                            lignefraisforfait.quantite');
        $this->db->from('lignefraisforfait');
        $this->db->join('fraisforfait', 'fraisforfait.idFraisForfait = lignefraisforfait.idFraisForfait');
        $this->db->where('lignefraisforfait.idVisiteur', $idVisiteur);
        $this->db->where('lignefraisforfait.mois', $mois);
        $this->db->order_by('lignefraisforfait.idFraisForfait', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

/**
 * Retourne toutes les lignes de frais hors forfait d'un visiteur pour un mois donné

 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return tableau associatif contenant tous les champs des lignes de frais hors forfait  
*/
    public function get_les_frais_hors_forfait($idVisiteur, $mois){
        $this->db->from('lignefraishorsforfait');
        $this->db->where('idVisiteur', $idVisiteur);
        $this->db->where('mois', $mois);
        $query = $this->db->get();
        return $query->result_array();
    }

/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
    public function est_premier_frais_mois($idVisiteur, $mois)
    {
        $this->db->select('count(*) AS nblignesfrais');
        $this->db->from('fichefrais');
        $this->db->where('fichefrais.idVisiteur', $idVisiteur);
        $this->db->where('fichefrais.mois', $mois);
        $query = $this->db->get();
        $laLigne = $query->row_array();
        $ok = ($laLigne['nblignesfrais'] === "0");
        return $ok;
    }

/**
 * Retourne le dernier mois en cours d'un visiteur
 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
    public function dernier_mois_saisi($idVisiteur)
    {
        $this->db->select('max(mois) AS dernierMois');
        $this->db->from('fichefrais');
        $this->db->where('fichefrais.idVisiteur', $idVisiteur);
        $query = $this->db->get();
        $laLigne = $query->row_array();
        return $laLigne['dernierMois'];
    }

/**
 * Retourne tous les id de la table FraisForfait
 * @return tableau associatif des  idFraisForfait
*/
	public function get_les_id_frais_forfait(){
        $this->db->select('idFraisForfait');
        $this->db->from('fraisforfait');
        $this->db->order_by('idFraisForfait');
        $query = $this->db->get();
        return $query->result_array();
    }
    
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function cree_nouvelles_lignes_frais($idVisiteur, $mois){
        // mise a jour de la dernière fiche
        $dernierMois = $this->dernier_mois_saisi($idVisiteur);
        $laDerniereFiche = $this->get_les_infos_ficheFrais($idVisiteur, $dernierMois);
		if($laDerniereFiche['idEtat'] == 'CR'){
			$this->maj_etat_fiche_frais($idVisiteur, $dernierMois, 'CL');
        }
        
        // création de la nouvelle fiche
        $data = array(  'idVisiteur' => $idVisiteur, 
                        'mois' => $mois, 
                        'nbJustificatifs' => 0, 
                        'montantValide' => 0, 
                        'dateModif' => date('Y-m-d'), 
                        'idEtat' => 'CR');
        $this->db->insert('fichefrais', $data);
        
        // initialisation des frais fortfaits à 0
        $lesIdFraisForfait = $this->get_les_id_frais_forfait();
		foreach($lesIdFraisForfait AS $unIdFraisForfait){
            $data = array(  'idVisiteur' => $idVisiteur, 
                            'mois' => $mois, 
                            'idFraisForfait' => $unIdFraisForfait['idFraisForfait'], 
                            'quantite' => 0);
            $this->db->insert('lignefraisforfait', $data);
		}
    }

 /**
 * Modifie l'état et la date de modification d'une fiche de frais
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */
	public function maj_etat_fiche_frais($idVisiteur, $mois, $etat){
        $data = array(  'idEtat' => $etat, 
                        'dateModif' => date('Y-m-d'));
        $where = array( 'idVisiteur' => $idVisiteur, 
                        'mois' => $mois);
        $this->db->update('fichefrais', $data, $where);
    }

/**
 * Met à jour la table ligneFraisForfait pour un visiteur et un mois donné en enregistrant les nouveaux montants
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
*/
	public function maj_frais_forfait($idVisiteur, $mois, $lesFrais){
        $lesIdFraisForfait = array_keys($lesFrais);
		foreach($lesIdFraisForfait AS $unIdFraisForfait){
            $qte = $lesFrais[$unIdFraisForfait];
            $data = array(  'quantite' => $qte);
            $where = array( 'idVisiteur' => $idVisiteur, 
                            'mois' => $mois, 
                            'idFraisForfait'=>  $unIdFraisForfait);
            $this->db->update('lignefraisforfait', $data, $where);
		}
    }

/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 * @param $idFrais 
*/
	public function supprimer_frais_hors_forfait($idFrais){
        $where = array( 'idFraisHorsForfait' => $idFrais);
        $this->db->delete('lignefraishorsforfait', $where);
    }
    
/**
 * Crée un nouveau frais hors forfait pour un visiteur un mois donné à partir des informations fournies en paramètre
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj/mm/aaaa
 * @param $montant : le montant
*/
    public function creer_nouveau_frais_hors_forfait($idVisiteur, $mois, $libelle, $date, $montant){
        $data = array(  'idVisiteur' => $idVisiteur, 
                        'mois' => $mois, 
                        'libelleFraisHorsForfait' => $libelle, 
                        'date' =>  $date, 
                        'montant' => $montant);
        $this->db->insert('lignefraishorsforfait', $data);
    }

/**
 * Retourne fiches dont l'état est précisé en argument
 * @param $etat 
 * @return Tableau de fiches
 * */
    public function get_les_fiches_etat($etat){
        $this->db->select('nom, prenom, mois, fichefrais.idVisiteur,SUBSTR(fichefrais.mois, 1, 4) AS numAnnee, 
                            SUBSTR(fichefrais.mois, 5) AS numMois');
        $this->db->from('fichefrais');
        $this->db->join('visiteur', 'fichefrais.idVisiteur = visiteur.idVisiteur');
        $this->db->where('idEtat', $etat);
        $this->db->order_by('mois', 'ASC');
        $this->db->order_by('nom', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
/**
 * Modifie l'état CR en CL des mois précédents
 */
	public function cloture_fiches_frais(){
        $data = array(  'idEtat' => "CL");
        $where = array( 'idEtat' => "CR", 
                        'mois <' => date('Ym'));
        $this->db->update('fichefrais', $data, $where);
    }

/**
 * Calcule le montant de frais hors forfait d'une fiche
 * @param $idMois
 * @param $idVisiteur 
 * @return tableau associatif contenant le montant total des frais hors forfait
*/
    public function calculer_montant_fhf($idVisiteur, $mois){
        $this->db->select('SUM(montant) AS montantfhf');
        $this->db->from('lignefraishorsforfait');
        $this->db->where('idVisiteur', $idVisiteur);
        $this->db->where('mois', $mois);
        $this->db->not_like('libelleFraisHorsForfait', 'REFUSER', 'after');
        $query = $this->db->get();
        return $query->row_array();
    }
/**
 * Calcule le montant de frais forfait d'une fiche
 * @param $idMois
 * @param $idVisiteur 
 * @return tableau associatif contenant le montant total des frais hors forfait
*/
    public function calculer_montant_ff($idVisiteur, $mois){
        $this->db->select('SUM(quantite*montant) AS montantff');
        $this->db->from('lignefraisforfait');
        $this->db->join('fraisforfait', 'fraisforfait.idFraisForfait = lignefraisforfait.idFraisForfait');
        $this->db->where('idVisiteur', $idVisiteur);
        $this->db->where('mois', $mois);
        $query = $this->db->get();
        return $query->row_array();
    }
/**
 * Calcule le montant de frais forfait d'une fiche
 * @param $idMois
 * @param $idVisiteur 
 * @return tableau associatif contenant le montant total des frais hors forfait
*/
    public function calculer_montant_total($idVisiteur, $mois){
        $montant = $this->calculer_montant_ff($idVisiteur, $mois)['montantff'] + $this->calculer_montant_fhf($idVisiteur, $mois)['montantfhf'];
        return $montant;
    }
/**
 * Calcule le montant de frais forfait d'une fiche
 * @param $idMois
 * @param $idVisiteur 
 * @return tableau associatif contenant le montant total des frais hors forfait
*/
    public function changer_montant_total($idVisiteur, $mois){
        $montant = $this->calculer_montant_total($idVisiteur, $mois);
        $data = array(  'montantValide' => $montant);
        $where = array( 'idVisiteur' => $idVisiteur,'mois' => $mois);
        $this->db->update('fichefrais', $data, $where);
    }
/**
 * Mets à jours un frais hors forfait
 * @param $idMois
 * @param $idVisiteur 
 * @return tableau associatif contenant le montant total des frais hors forfait
*/
    public function refuser_fhf($idFhf){
        $this->db->where('idFraisHorsForfait', $idFhf);
        $this->db->set('libelleFraisHorsForfait', 'CONCAT("REFUSER : ",libelleFraisHorsForfait)', FALSE); // FALSE => Execute la requête sans échapper les valeurs
        $this->db->not_like('libelleFraisHorsForfait', 'REFUSER', 'after');
        $this->db->update('lignefraishorsforfait');
    }

    public function getlastModifMdp($idFhf){
        
    }
}
