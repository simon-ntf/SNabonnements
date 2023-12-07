<?php
/***************************************************************************\
 *  SN Suite, suite de plugins pour SPIP                                   *
 *  Copyright © depuis 2014                                                *
 *  Simon N                                                            *
 *                                                                         *
 *  Ce programme est un logiciel libre distribué sous licence GNU/GPL.     *
 *  Pour plus de détails voir l'aide en ligne.                             *
 *  https://www.snsuite.net                                                *
\**************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/autoriser');
include_spip('inc/editer');

function formulaires_editer_auteur_dp_charger($id_auteur,$retour=''){

	$valeurs = [];
	$valeurs['editable'] = '';
	$autorisation = autoriser('modifier', 'auteur', intval($id_auteur));
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$champs = ['id_auteur', 'statut', 'bio', 'sn_genre', 'sn_nom', 'sn_prenom', 'sn_civilite', 'sn_societe', 'sn_fonctions', 'sn_pays', 'sn_ville', 'sn_date_naissance', 'sn_publier_fiche', 'sn_date_consentement_dp'];
		$liste_civilites = sn_const_options_trads('civilite','A','snabo');
		$liste_genres = sn_const_options_trads('genre','A','snabo');
		$explications_publicite_profil = [];
		$explications_publicite_profil['0minirezo'] = _T('snabo:edit_auteur_publier_fiche_explication_admin');
		$explications_publicite_profil['1comite'] = _T('snabo:edit_auteur_publier_fiche_explication_auteur',['site'=>$GLOBALS['meta']['nom_site_spip']]);
		$explications_publicite_profil['6forum'] = _T('snabo:edit_auteur_publier_fiche_explication_visiteur',['site'=>$GLOBALS['meta']['nom_site_spip']]);
		if($req = sql_fetsel($champs,'spip_auteurs','id_auteur=' . intval($id_auteur))){
			foreach($req as $champ => $d){
				$valeurs[$champ] = $req[$champ];
			}
			// Pour respecter le consentement RGPD (a raison inconnue, b expiration, c expire d'ici 60 jours)
			$statut_rgpd = sn_rgpd($valeurs['sn_date_consentement_dp']);
			if($statut_rgpd === 0){
				return false;
			} elseif($statut_rgpd === 'pre'){
				$valeurs['champ_rgpd_actif'] = true;
				$valeurs['explication_rgpd'] = _T('snabo:edit_auteur_rgpd_expiration_avenir');
			} elseif($statut_rgpd === 'exp'){
				$valeurs['champ_rgpd_actif'] = true;
				$valeurs['explication_rgpd'] = _T('snabo:edit_auteur_rgpd_expiration_passee');
			} elseif($statut_rgpd === 'ini'){
				$valeurs['champ_rgpd_actif'] = true;
			} else{
				$valeurs['champ_rgpd_actif'] = null;
			}

			$valeurs['liste_civilites'] = $liste_civilites;
			$valeurs['liste_genres'] = $liste_genres;

			// Pour activer la pipeline de conversion des dates
			$valeurs['sqldates'] = ['sn_date_naissance'];

			$valeurs['champ_publicite_profil_actif'] = false;
			if($GLOBALS['meta']['sn_abo_fiches_auteurs_activer']){
				if($valeurs['statut'] === '1comite'){
					$valeurs['champ_publicite_profil_actif'] = true;
				} elseif($valeurs['statut'] === '6forum') {
					if($GLOBALS['meta']['sn_abo_fiches_visiteurs_autoriser']){
						$valeurs['champ_publicite_profil_actif'] = true;
					}
				}
			}
			$valeurs['explication_fiche_publique'] = $explications_publicite_profil[$valeurs['statut']];
			$valeurs['id_auteur'] = $id_auteur;
			$valeurs['editable'] = true;
		} else {
			return false;
		}
	} else{
		return false;
	}
	
	return $valeurs;
	
}
function formulaires_editer_auteur_dp_verifier($id_auteur,$retour=''){

	include_spip('inc/sn_regexr');

	$erreurs = [];
	$liste_civilites = sn_const_options_trads('civilite','A','snabo');
	$liste_genres = sn_const_options_trads('genre','A','snabo');
	if(!isset($liste_genres[_request('sn_genre')])){
		$erreurs['sn_genre'] = _T('sncore:regex_gen');
	}
	if (_request('sn_rgpd')){
		if(sn_verif_bool_on(_request('sn_rgpd')) === true){
		} else {
			$erreurs['sn_rgpd'] = _T('sncore:regex_gen');
		}
	}
	if (!_request('sn_date_naissance')){
	} else if(!preg_match(sn_regex_date_spip(),_request('sn_date_naissance'))){
		$erreurs['sn_date_naissance'] = _T('sncore:regex_date_spip');
	}
	if (!_request('sn_tel')){
	} else if(!preg_match(sn_regex_tel(),_request('sn_tel'))){
		$erreurs['sn_tel'] = _T('sncore:regex_int_nb',array('nb'=>'6'));
	}
	if (_request('sn_prenom')){
		if(!preg_match(sn_regex_txt_limite(64,0),_request('sn_prenom'))){
			$erreurs['sn_prenom'] = _T('sncore:regex_txt_limite_nb',array('nb'=>'64'));
		}
	}
	if (_request('sn_nom')){
		if(!preg_match(sn_regex_txt_limite(64,0),_request('sn_nom'))){
			$erreurs['sn_nom'] = _T('sncore:regex_txt_limite_nb',array('nb'=>'64'));
		}
	}
	if (_request('sn_fonctions')){
		if(!preg_match(sn_regex_txt_limite(244,0,','),_request('sn_fonctions'))){
			$erreurs['sn_fonctions'] = _T('sncore:regex_txt_limite_nb_ajouts',array('nb'=>'244','ajouts'=>','));
		}
	}
	if (_request('sn_societe')){
		if(!preg_match(sn_regex_txt_limite(128,0),_request('sn_societe'))){
			$erreurs['sn_societe'] = _T('sncore:regex_txt_limite_nb',array('nb'=>'128'));
		}
	}
	if (_request('sn_ville')){
		if(!preg_match(sn_regex_txt_limite(128,0),_request('sn_ville'))){
			$erreurs['sn_ville'] = _T('sncore:regex_txt_limite_nb',array('nb'=>'64'));
		}
	}
	if (_request('sn_pays')){
		if(!preg_match(sn_regex_txt_limite(128,0),_request('sn_pays'))){
			$erreurs['sn_pays'] = _T('sncore:regex_txt_limite_nb',array('nb'=>'32'));
		}
	}
	if (_request('bio')){
		if(!preg_match(sn_regex_txt_etendu(8000,0),_request('bio'))){
			$erreurs['bio'] = _T('sncore:regex_txt_etendu_nb',array('nb'=>'8000'));
		}
	}
	if(_request('sn_publier_fiche')){
		if(sn_verif_bool_on(_request('sn_publier_fiche')) === true){} else {
			$erreurs['sn_publier_fiche'] = _T('sncore:regex_gen');
		}
	}
	if(count($erreurs)==0){
		$erreurs = formulaires_editer_objet_verifier('auteur',$id_auteur);
	}

	return $erreurs;
	
}
function formulaires_editer_auteur_dp_traiter($id_auteur,$retour=''){

	if ($retour) {
		refuser_traiter_formulaire_ajax();
	}

	$retours = [];
	$retours['message_erreur'] = _T('sncore:message_erreur_defaut');
	$retours['editable'] = '';
	$autorisation = autoriser('modifier', 'auteur', intval($id_auteur));
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$req = sql_fetsel(['id_auteur'],'spip_auteurs','id_auteur=' . intval($id_auteur));
		if($req){
			if (_request('sn_rgpd')){
				set_request('sn_date_consentement_dp', date('Y-m-d H:i:d'));
			}
			$retours = formulaires_editer_objet_traiter('auteur',$id_auteur,0,$retour);
		} else {
			$retours['message_erreur'] = _T('sncore:erreur_acces_donnees');
		}
	} else {
		$retours['message_erreur'] = _T('sncore:erreur_autorisation_refusee');
	}

	if(isset($retours['message_ok'])){
		$retours['editable'] = true;
		if ($retour) {
			$retours['redirect'] = $retour;
		}
	}

	return $retours;
}
