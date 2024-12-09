<?php
// TODO : Faire la notification
/**
 * Class : Contrôleur permettant de valider des fiches de frais des visiteurs   
 * Permet au comptable de :
 *  - choisir une fiche de frais en fonction du mois et du visiteur
 *  - visualiser l'état de la fiche selectionnée
 *  - actualiser les frais forfaitaires de la fiche selectionnée
 *  - Refuser des frais hors-forfaits de la fiche selectionnée
 * - Reporter des frais hors-forfaits de la fiche selectionnée
 *  - Valider les frais de la fiche selectionnée
 */
class Validerfrais extends CI_Controller {

    private $id_mois;
    private $id_visiteur;
    private $info = null;
    private $idFiche;

/**
 * Constructeur   
 * si l'utilisateur n'est pas connecté il est redirigé vers le contrôleur de connexion.
 * sinon :
 *  - chargement du modèle, des helpers et bibliothèques
 *  - l'id visiteur et l'id du mois ne sont pas initialisé (il le seront en fonction des actions du comptable)
 */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('gsb_lib');
        $this->load->helper('url_helper');
        if( ! $this->gsb_lib->est_connecte() ){
            redirect(site_url('Connexion'));
        }
        else{
            if ($this->gsb_lib->Verif_date($this->session->modification_mdp)) {
                $this->gsb_lib->ajouter_erreur("Vous devez changer votre mot de passe");
                redirect(site_url('ChangementMdp'));
            }

            $this->load->model('gsb_model');
            $this->load->helper('form_helper');
            $this->load->helper('html_helper');
            $this->load->library('session');
        }
    }

/**
 * méthode action par défaut : le comptable accède à ce contrôleur en ayant cliqué sur le menu correspondant.  
 *  - usage : <application base url>/Validerfrais ou <application base url>/Validerfrais/index
 */
    public function index(){
        $this->id_mois = null;
        $this->commun();
    }

/**
 * méthode action : le comptable vient de choisir un mois dans la liste déroulante.
 *  - usage : <application base url>/Validerfrais/selectionner_fiche
 */ 
    public function selectionner_fiche(){
        $this->idFiche = $this->input->post('lstFiche');
        list($this->id_visiteur, $this->id_mois) = explode("_", $this->idFiche);
        $this->commun();
    }
/**
 * méthode action : le comptable vient d'actualiser les frais hors forfait.
 *  - usage : <application base url>/Validerfrais/actualiser_fhf
 */ 
    public function actualiser_fhf(){
        $this->id_mois = null;
        $this->id_visiteur = null;
        $this->idFiche = null;
        $this->commun();
    }
/**
 * méthode action : le comptable vient d'actualiser les frais forfait.
 *  - usage : <application base url>/Validerfrais/actualiser_ff
 */ 
    public function actualiser_ff($id_fiche){
        $lesFrais = $this->input->post('lesFrais');
        list($id_visiteur, $id_mois) = explode("_", $id_fiche);
        $this->gsb_model->maj_frais_forfait($id_visiteur, $id_mois, $lesFrais);
        $this->info = "Les modifications des frais forfaitaires ont bien été effectuées";
        $this->id_mois = $id_mois;
        $this->id_visiteur = $id_visiteur;
        $this->commun();
    }
/**
 * méthode action : le comptable vient de refuser un frais hors forfait.
 *  - usage : <application base url>/Validerfrais/refuser_fhf
 */ 
    public function refuser_fhf($id_fhf, $id_fiche){
        list($id_visiteur, $id_mois) = explode("_", $id_fiche);
        $this->id_mois = $id_mois;
        $this->id_visiteur = $id_visiteur;
        $this->idFiche = $id_fiche;
        $this->gsb_model->refuser_fhf($id_fhf);
        $this->commun();
    }
/**
 * méthode action : le comptable vient de refuser un frais hors forfait.
 *  - usage : <application base url>/Validerfrais/reporter_fhf
 */ 
    public function reporter_fhf(){
        $this->id_mois = null;
        $this->id_visiteur = null;
        $this->idFiche = null;
        $this->commun();
    }
/**
 * méthode action : le comptable vient de choisir un mois dans la liste déroulante.
 *  - usage : <application base url>/Validerfrais/valider_fiche
 */ 
public function valider_fiche(){
    $idFiche = $this->input->post('idFiche');
    list($id_visiteur, $id_mois) = explode("_", $idFiche);
    // Traiter la fiche à valider CL - > VA
    $this->gsb_model->maj_etat_fiche_frais($id_visiteur, $id_mois, "VA");
    $this->gsb_model->changer_montant_total($id_visiteur, $id_mois);
    // Prévoir la prochaine fiche à afficher
    $this->id_mois = null;
    $this->id_visiteur = null;
    $this->idFiche = null;
    $this->info = "La fiche a bien été validée.";
    $this->commun();
}

/**
 * Traitement commun au contrôleur Validerfrais.
 */
    private function commun(){
        $this->gsb_model->cloture_fiches_frais();
        //infos générales page
        $this->load->view('structures/v_page_entete');
        $data ['menus'] = $this->gsb_lib->get_menu($this->session->idRole);
        $this->load->view('v_sommaire', $data);
        $data['titre'] = "Validation des fiches de frais";
        $this->load->view('structures/v_contenu_entete', $data);
        //gestion des notifications
        if($this->info!= null){
            $data['info'] = $this->info;
            $this->load->view('structures/v_notification', $data);
        }
        //récupération des mois où l'utilisateur à des fiches de frais
        $les_fiches = $this->gsb_model->get_les_fiches_etat("CL");
        if(count($les_fiches) == 0){
            // Notification : Toutes les fiches de frais ont été remboursée
            $data['info'] = "Toutes les fiches de frais ont été remboursées.";
            $this->load->view('structures/v_notification', $data);
        }
        else{
            if (!isset($this->id_mois) ){  // si aucun mois choisi, on prend par défaut le premier mois dans la liste
                $this->id_mois = $les_fiches[0]['mois'];
                $this->id_visiteur = $les_fiches[0]['idVisiteur'];
                $this->idFiche = $this->id_visiteur."_".$this->id_mois;
            }

            //gestion liste déroulante
            $options = []; // création d'un tableau contenant les 'options' de la liste 'select'
            foreach ($les_fiches as $une_fiche){
                $idFiche = $une_fiche['idVisiteur']."_".$une_fiche['mois'];
                $libelle = $une_fiche['prenom']." ".$une_fiche['nom']." - ".$this->gsb_lib->get_nom_mois($une_fiche['numMois']). " ".$une_fiche['numAnnee'];
                $options[$idFiche] = $libelle; // <option value=$idFiche> $libelle </>
            }
            $data['lst_contenu'] = $options;
            $data['lst_select'] = $this->idFiche;  // correspondant à l'élément selectionné dans la liste (attribut selected pour un option)
            $data['lst_action'] = 'Validerfrais/selectionner_fiche'; //action effectuée par le formulaire un fois soummis
            $data['lst_id'] = 'lstFiche';
            $data['lst_label'] = 'Fiche';
            $data['sc_titre'] = 'Fiche à sélectionner :';
            $this->load->view('structures/v_souscontenu_entete', $data);
            $this->load->view('templates/v_liste_deroulante', $data);
            $this->load->view('structures/v_souscontenu_pied');
            //gestion de la fiche
            $num_annee = substr($this->id_mois, 0, 4);
            $num_mois = substr($this->id_mois, 4, 2);
            $date_titre = $this->gsb_lib->get_nom_mois($num_mois)." ".$num_annee;
            $utilisateur = $this->gsb_model->get_detail_utilisateur($this->id_visiteur);

            $data['sc_titre'] = 'Fiche de frais de '.$utilisateur['prenom'].' '.$utilisateur['nom'].' du '.$date_titre.' :';
            $this->load->view('structures/v_souscontenu_entete', $data);
            //gestion zone Etat
            $fiche = $this->gsb_model->get_les_infos_ficheFrais($this->id_visiteur, $this->id_mois);
            $fiche['montantValide'] = $this->gsb_model->calculer_montant_total($this->id_visiteur, $this->id_mois);
            $fiche['textButton'] = 'Montant à valider';
            $data['fiche'] = $fiche;
            $this->load->view('v_etat_fiche', $data);
            
            //gestion des éléments forfaitisés
            $data['heading'] = 'Eléments forfaitisés';
            $data['ff'] = $this->gsb_model->get_les_frais_forfait($this->id_visiteur, $this->id_mois);
            $data['action'] = 'Validerfrais/actualiser_ff/'.$this->idFiche;
            $data['label'] = 'Actualiser';
            $this->load->view('v_fraisforfait_edit', $data);
            //gestion des éléments hors forfait
            $data['heading'] = 'Eléments hors forfait';
            $data['fhf'] = $this->gsb_model->get_les_frais_hors_forfait($this->id_visiteur, $this->id_mois);
            $data['idFiche'] = $this->idFiche;
            $this->load->view('v_fraishorsforfait_table_valider', $data);
            $this->load->view('structures/v_souscontenu_pied');
            // Bouton pour valider la fiche
            $data = [
                'action'=> 'Validerfrais/valider_fiche',
                'label' => 'Valider la fiche',
                'idFiche' => $this->idFiche
            ];
            $this->load->view('v_action', $data);
            //fin de la fiche
            $this->load->view('structures/v_souscontenu_pied');
            //fin du contenu et de la page
            $this->load->view('structures/v_page_pied');
        }
    }
}