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
 * Formulaire de configuration SN Suite
 *
 * @plugin SN Abonnements
 **/
 
if (!defined("_ECRIRE_INC_VERSION")) return;

function formulaires_configurer_snabonnements_charger_dist(){

	$valeurs = [];
	$valeurs['editable'] = '';
	$autorisation = autoriser('modifier', 'snabonnements');
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$valeurs = [
			'sn_abo_page_compte_activer' => $GLOBALS['meta']['sn_abo_page_compte_activer'],
			'sn_abo_abonnement_activer' => $GLOBALS['meta']['sn_abo_abonnement_activer'],
			'sn_abo_menu_membre_activer' => $GLOBALS['meta']['sn_abo_menu_membre_activer'],
			'sn_abo_fiches_auteurs_activer' => $GLOBALS['meta']['sn_abo_fiches_auteurs_activer'],
			'sn_abo_fiches_visiteurs_autoriser' => $GLOBALS['meta']['sn_abo_fiches_visiteurs_autoriser'],
		];

		$valeurs['snid_conformite'] = false;
		$req = sql_fetsel(['id_auteur'],'spip_auteurs','statut IN ("0minizero", "1comite", "6forum") AND sn_alphanum_id IS NULL');
		if(!$req){
			$valeurs['snid_conformite'] = true;
		}
		$valeurs['snid_lib'] = false;
		if(extension_loaded('sodium') === true){
			$valeurs['snid_lib'] = true;
		}

		$valeurs['editable'] = true;

	} else{
		return false;
	}

	return $valeurs;
	
}

function formulaires_configurer_snabonnements_verifier_dist(){
	
	include_spip('inc/sn_regexr');

	$erreurs = [];
	if(_request('sn_abo_page_compte_activer')){
		if(sn_verif_bool_on(_request('sn_abo_page_compte_activer')) === true){
		} else {
			$erreurs['sn_abo_page_compte_activer'] = _T('sncore:regex_gen');
		}
	}
	if(_request('sn_abo_abonnement_activer')){
		if(sn_verif_bool_on(_request('sn_abo_abonnement_activer')) === true){
		} else {
			$erreurs['sn_abo_abonnement_activer'] = _T('sncore:regex_gen');
		}
	}
	if(_request('sn_abo_menu_membre_activer')){
		if(sn_verif_bool_on(_request('sn_abo_menu_membre_activer')) === true){
		} else {
			$erreurs['sn_abo_menu_membre_activer'] = _T('sncore:regex_gen');
		}
	}
	if(_request('sn_abo_fiches_auteurs_activer')){
		if(sn_verif_bool_on(_request('sn_abo_fiches_auteurs_activer')) === true){
		} else {
			$erreurs['sn_abo_fiches_auteurs_activer'] = _T('sncore:regex_gen');
		}
	}
	if(_request('sn_abo_fiches_visiteurs_autoriser')){
		if(sn_verif_bool_on(_request('sn_abo_fiches_visiteurs_autoriser')) === true){
		} else {
			$erreurs['sn_abo_fiches_visiteurs_autoriser'] = _T('sncore:regex_gen');
		}
	}

	return $erreurs;
	
}

function formulaires_configurer_snabonnements_traiter_dist(){

	$retours = [];
	$retours['message_erreur'] = _T('sncore:message_erreur_defaut');

	$autorisation = autoriser('modifier', 'snabonnements');
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		include_spip('inc/config');
		appliquer_modifs_config();
		$retours = [];
		$retours['message_ok'] = _T('config_info_enregistree');
	} else {
		$retours['message_erreur'] = _T('sncore:erreur_autorisation_refusee');
	}
	
	return $retours;
	
}
