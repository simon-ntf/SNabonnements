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

function formulaires_selectionner_snabos_charger_dist($id_auteur=0,$retour='') {

	$valeurs = [];
	$valeurs['editable'] = '';
	$autorisation = autoriser('modifier', 'auteur', intval($id_auteur));
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$valeurs['snabonnements'] = [];
		$req = sql_allfetsel('snabonnements','spip_auteurs','id_auteur='.intval($id_auteur));
		if(isset($req[0])){
			$auteur_abo = explode(',',$req[0]['snabonnements']);
			foreach($auteur_abo as $cle => $valeur){
				$valeurs['snabonnements'][$valeur] = 'on';
			}
		}
		$valeurs['editable'] = true;
		$valeurs['id_auteur'] = $id_auteur;
	} else{
		return false;
	}
	
	return $valeurs;
}

function formulaires_selectionner_snabos_verifier_dist($id_auteur=0,$retour='') {

	include_spip('inc/sn_regexr');

	$erreurs = [];
	$snabonnements_datas = sql_allfetsel('id_snabonnement','spip_snabonnements','statut="publie"');
	if(count($snabonnements_datas) > 0){
		foreach($snabonnements_datas as $cle => $obj){
			$abo_ref = 'snabonnement' . $obj['id_snabonnement'];
			$saisie_abo = _request($abo_ref);
			if(sn_verif_bool_on($saisie_abo) !== true){
				$erreurs[$abo_ref] = _T('sncore:regex_gen');
			}
		}
	}
	if(count($erreurs)==0){ $erreurs = formulaires_editer_objet_verifier('auteur',$id_auteur); }

	return $erreurs;

}

function formulaires_selectionner_snabos_traiter_dist($id_auteur=0,$retour='') {

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
		$snabonnements_choisis = [];
		$snabonnements_sql = '';
		$snabonnements_datas = sql_allfetsel('id_snabonnement','spip_snabonnements','statut="publie"');
		if(is_array($snabonnements_datas)){
			if(count($snabonnements_datas) > 0){
				foreach($snabonnements_datas as $cle => $obj){
					$abo_ref = 'snabonnement' . $obj['id_snabonnement'];
					$saisie_abo = _request($abo_ref);
					if($saisie_abo === 'on'){
						$snabonnements_choisis[] = $obj['id_snabonnement'];
					}
				}
				$snabonnements_sql = implode(',',$snabonnements_choisis);
				set_request('snabonnements',$snabonnements_sql);
				$retours = formulaires_editer_objet_traiter('auteur',$id_auteur,0,$retour);
			}
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
