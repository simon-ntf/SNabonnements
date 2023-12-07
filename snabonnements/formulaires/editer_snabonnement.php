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

/**
 * Formulaire pour éditer un abonnement
 *
 * @plugin SN Abonnements
 **/
 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_snabonnement_charger($id_snabonnement='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){

	$valeurs = [];
	$valeurs['editable'] = '';

	$autorisation = autoriser('modifier', 'snabonnement', intval($id_snabonnement));
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$valeurs = formulaires_editer_objet_charger('snabonnement',$id_snabonnement,$id_rubrique,$lier_trad,$retour,$config_fonc,$row,$hidden);
		$valeurs['liste_abo_statuts'] = $GLOBALS['SN_ABONNEMENTS_STATUTS'];
		$valeurs['id_snabonnement'] = $id_snabonnement;
	} else{
		return false;
	}
	
	return $valeurs;
	
}
function formulaires_editer_snabonnement_identifier($id_snabonnement='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){
	return serialize([intval($id_snabonnement)]);
}
function formulaires_editer_snabonnement_verifier($id_snabonnement='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){

	include_spip('inc/sn_regexr');

	$erreurs = [];
	if (!_request('titre')){
		$erreurs['titre'] = _T('info_obligatoire');
	} elseif (!preg_match(sn_regex_txt(244,1),_request('titre'))){
		$erreurs['titre'] = _T('sncore:regex_txt_nb',array('nb'=>'244'));
	}
	if (!preg_match(sn_regex_txt(512,0),_request('resume'))){
		$erreurs['resume'] = _T('sncore:regex_txt_nb',array('nb'=>'512'));
	}
	if (!isset($GLOBALS['SN_ABONNEMENTS_STATUTS'][_request('statut')])){
		$erreurs['statut'] = _T('sncore:regex_gen');
	}
	if(count($erreurs)==0){ $erreurs = formulaires_editer_objet_verifier('snabonnement',$id_snabonnement); }

	return $erreurs;
	
}
function formulaires_editer_snabonnement_traiter($id_snabonnement='new', $id_rubrique=0, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){

	set_request('redirect', '');
	$retours = [];
	$retours['message_erreur'] = _T('sncore:message_erreur_defaut');
	$autorisation = autoriser('modifier', 'snabonnement', intval($id_snabonnement));

	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$retours = formulaires_editer_objet_traiter('snabonnement',$id_snabonnement,$id_rubrique,$lier_trad,$retour,$config_fonc,$row,$hidden);
	} else {
		$retours['message_erreur'] = _T('sncore:erreur_autorisation_refusee');
	}

	if(isset($retours['message_ok'])){
		$id_snabonnement = $retours['id_snabonnement'];
	}

	return $retours;
}
