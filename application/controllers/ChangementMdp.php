<?php
/**
 * Class : Contrôleur permettant de visualiser l'état des fiches de frais d'un visiteur préalablement connecté.   
 * Permet au visiteur de :
 *  - choisir une fiche de frais en fonction du mois
 *  - visualiser l'état de la fiche selectionnée
 *  - visualiser les frais forfaitaires et hors-forfaits de la fiche selectionnée
 */
class ChangementMdp extends CI_Controller {
    
    private $id_visiteur;
    private $info = null; 


/**
 * Constructeur   
 * si l'utilisateur n'est pas connecté il est redirigé vers le contrôleur de connexion.
 * sinon :
 *  - chargement du modèle, des helpers et bibliothèques
 *  - l'id visiteur est initalisé à celui du visiteur connecté
 *  - l'id du mois n'est pas initialisé (il le sera en fonction des actions de l'utilisateur)
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
            $this->load->model('gsb_model');
            $this->load->helper('form_helper');
            $this->load->library('session');
            $this->id_visiteur = $this->session->id;
        }
    }

/**
 * méthode action par défaut : le visiteur accède à ce contrôleur en ayant cliqué sur le menu correspondant.  
 *  - usage : <application base url>/Etatfrais ou <application base url>/Etatfrais/index
 */
    public function index(){
        $this->id_mois = null;
        $this->commun();
    }

/**
 * méthode action : le visiteur vient de choisir un mois dans la liste déroulante.
 *  - usage : <application base url>/Gererfrais/valider_maj_fraisforfait
 */ 
    public function valider_mdp(){
        $login = $this->session->login;
        $mdp = $this->input->post('ancienMdp');
       
        $pwdMdp = $this->input->post('pwdMdp');
        $confPwdMdp = $this->input->post('confPwdMdp');

        if ($pwdMdp !=  $confPwdMdp) {
            $this->gsb_lib->ajouter_erreur("Les deux nouveaux mot de passe ne corresponde pas");
        }


        $resultP = $this->gsb_lib->Restric_mdp($this->input->post('pwdMdp'));

        if ($resultP) {
            $this->gsb_lib->ajouter_erreur("Mot de passe pas assez sécurisé");
        }
    

        $utilisateur = $this->gsb_model->get_infos_utilisateur($login, $mdp);
        if(!is_array($utilisateur) ){ //le login+mot de passe existe, le utilisateur peut accéder à l'application
            $this->gsb_lib->ajouter_erreur("Mot de passe actuel incorrect");
            
        }
      
        $this->commun();
    }

/**
 * Traitement commun au contrôleur Etatfrais.
 */
    private function commun(){
        //$this->info = "test";

        //infos générales page
        $this->load->view('structures/v_page_entete');
        $result = $this->gsb_lib->Verif_date($this->session->modification_mdp);
        if ($result) {
            $data ['menus'] = $this->gsb_lib->get_menu("rst");
            $this->gsb_lib->ajouter_erreur("Vous devez changer votre mot de passe");

        }
        else {
        $data ['menus'] = $this->gsb_lib->get_menu($this->session->idRole);
        }
        $this->load->view('v_sommaire', $data);
        $data['titre'] = "Changement du mot de passe";

        
        $this->load->view('structures/v_contenu_entete', $data);

         //gestion des notifications
         if( isset($this->info) ){
            $data['info'] = $this->info;
            $this->load->view('structures/v_notification', $data);
         }

          //gestion des erreurs
        if($this->gsb_lib->nb_erreurs() > 0){
            $this->load->view('errors/html/v_error_gsb');
        }



        $this->load->view('v_modif_mdp');
        $this->load->view('structures/v_souscontenu_pied');

        //fin du contenu et de la page
        $this->load->view('structures/v_page_pied');
    }
}
