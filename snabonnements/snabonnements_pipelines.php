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

if (!function_exists('snabonnements_affiche_gauche')){ function snabonnements_affiche_gauche($flux){

	$page_exec = $flux['args']['exec'];
	if($page_exec == 'snabonnements'){
		$flux['data'] .= '<div class="box info"><p class="sn-prive-demarge sn-prive-padding-16">' . _T('snabo:colonne_snabonnements_texte') . '</p></div>';
	}
	return $flux;

}}

if (!function_exists('snabonnements_affiche_milieu')){ function snabonnements_affiche_milieu($flux) {

	// Export abonnes
	if ($e = trouver_objet_exec($flux['args']['exec']) AND $e['type'] == 'snabonnement' AND $e['edition'] == false) {
		$ref_snabonnement = $e['id_table_objet'];
		$id_snabonnement = $flux['args']['id_snabonnement'];
		$param_abo = [$ref_snabonnement => $id_snabonnement];
		$affmilieu = recuperer_fond('prive/squelettes/inclure/snabonnement_exporter',$param_abo);
		if($p=strpos($flux['data'],"<!--affiche_milieu-->")){
			$flux['data'] = substr_replace($flux['data'],$affmilieu,$p,0);
		}
	}

	return $flux;

}}

if (!function_exists('snabonnements_configurer_liste_metas')){ function snabonnements_configurer_liste_metas($metas){

	$metas['sn_abo_page_compte_activer'] = 'on'; // off|on
	$metas['sn_abo_abonnement_activer'] = 'on'; // off|on
	$metas['sn_abo_menu_membre_activer'] = 'on'; // off|on
	$metas['sn_abo_fiches_auteurs_activer'] = 'on'; // off|on
	$metas['sn_abo_fiches_visiteurs_autoriser'] = 'on'; // off|on

	return $metas;

}}

if (!function_exists('snabonnements_editer_contenu_objet')){ function snabonnements_editer_contenu_objet($flux) {

	// Ajout sur édition auteur
	$contenu = '';
	if($flux['args']['type'] === 'auteur'){
		$contenu .= recuperer_fond('prive/inclure/editer/sn_auteur',$flux['args']['contexte']);
		$flux['data'] = str_replace('<div class="editer editer_bio', $contenu . '<div class="editer editer_bio', $flux['data']);
	}

	return $flux;

}}

if (!function_exists('snabonnements_formulaire_charger')){ function snabonnements_formulaire_charger($flux) {

	include_spip('inc/session');

	$form = $flux['args']['form'];

	// Conversion des dates issues de SQL.
	// Pour la declencher la fc charger du CVT doit contenir une valeur "sqldates"
	// (array contenant les ref des champs concernes)
	if(isset($flux['data']['sqldates'])){ if(is_array($flux['data']['sqldates'])){
		$nb_sqldates = count($flux['data']['sqldates']);
		$sqldate_ref;
		for($i=0;$i<$nb_sqldates;$i++){
			$sqldate_ref = $flux['data']['sqldates'][$i];
			$flux['data'][$sqldate_ref] = affdate($flux['data'][$sqldate_ref],'d/m/Y');
		}
	}}

	// Conformite
	include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
	if(sn_form_a_verifier($form) === true){
		$controle = sn_identifier();
		if ($controle === true) { } else {
			return null;
		}
	}

	return $flux;

}}


if (!function_exists('snabonnements_formulaire_verifier')){ function snabonnements_formulaire_verifier($flux) {

	$form = $flux['args']['form'];

	include_spip('inc/sn_regexr');
	if($form === 'inscription'){
		if(sn_verif_bool_on(_request('rgpd')) !== true){
			$flux['data']['rgpd'] = _T('sncore:regex_gen');
		} elseif(_request('rgpd') !== 'on'){
			$flux['data']['rgpd'] = _T('snabo:inscription_rgpd_obligatoire',['site'=>$GLOBALS['meta']['nom_site']]);
		}
		if(!preg_match(sn_regex_txt_brut(244,6,'_'),'nom_inscription')){
			$flux['data']['rgpd'] = _T('sncore:regex_txt_brut_nb',['nb'=>244,'ajouts'=>'_']);
		}
	}

	return $flux;

}}

if (!function_exists('snabonnements_formulaire_traiter')){ function snabonnements_formulaire_traiter($flux) {

	$form = $flux['args']['form'];

	// Conformite
	include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
	if(sn_form_a_verifier($form) === true){
		$controle = sn_identifier();
		if ($controle === true) { } else {
			$flux['args']['data'] = null;
			return null;
		}
	}

	return $flux;

}}

if (!function_exists('snabonnements_pre_edition')){ function snabonnements_pre_edition($flux) {

	include_spip('inc/sn_const');
	$sn_const_compte_dn_defaut = sn_global_compte_dn_defaut();

	if ( $flux['args']['type'] === 'auteur' ) {
		// Conversion date naissance SQL
		if(isset($flux['data']['sn_date_naissance'])){
			if( is_string($flux['data']['sn_date_naissance']) && ($flux['data']['sn_date_naissance'] !== '')){
				$flux['data']['sn_date_naissance'] = sn_conv_date_saisie_sql($flux['data']['sn_date_naissance']);
			}  else {
				$flux['data']['sn_date_naissance'] = $sn_const_compte_dn_defaut;
			}
		}
		// Enregistrement de la date de mise en ligne de la fiche auteur si elle est activée
		if($flux['data']['sn_publier_fiche'] == 'on'){
			if(intval(date(session_get('sn_date_fiche_publique'))) == 0){
				$flux['data']['sn_date_fiche_publique'] = date('Y-m-d H:i:d');
			}
		}
	}

	return $flux;
	
}}

if (!function_exists('snabonnements_pre_insertion')){ function snabonnements_pre_insertion($flux) {

	/* Crée les données auto pour chaque nouvel auteur */
	$arr_concat .= ']';
	if($flux['args']['table'] === 'spip_auteurs'){
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$flux['data']['sn_alphanum_id'] = snid_creer();
		$flux['data']['sn_date_inscr'] = date('Y-m-d H:i:d');
		$flux['data']['sn_date_consentement_dp'] = date('Y-m-d H:i:d');
		$flux['data']['sn_date_modif_login'] = date('Y-m-d H:i:d');
	}

	return $flux;
	
}}

if (!function_exists('snabonnements_preparer_fichier_session')){ function snabonnements_preparer_fichier_session($flux) {

	// Préparation de la vérification
	include_spip('inc/sn_identifier');
	if(isset($flux['data']['id_auteur'])){
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		if($flux['data']['sn_alphanum_id'] == null){
		} else{
			$flux['data']['snid'] = snid_normal($flux['data']['id_auteur'],$flux['data']['sn_alphanum_id']);
			$flux['data']['sn_alphanum_id'] = null;
		}
		// RGPD
		$flux['data']['consentement_dp'] = sn_rgpd($flux['data']['sn_date_consentement_dp']);
	}

	return $flux;
}}
