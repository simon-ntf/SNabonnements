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

function formulaires_editer_auteur_login_charger($id_auteur,$retour=''){

	$valeurs = [];
	$valeurs['editable'] = '';

	$autorisation = autoriser('modifier', 'auteur', intval($id_auteur));
	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		$valeurs['logint'] = '';
		$valeurs['loginc'] = '';
		$valeurs['passc'] = '';
		$valeurs['id_auteur'] = $id_auteur;
		$valeurs['editable'] = true;
	} else{
		return false;
	}
	
	return $valeurs;
	
}
function formulaires_editer_auteur_login_verifier($id_auteur,$retour=''){

	include_spip('inc/sn_regexr');

	$erreurs = [];
	if (!_request('logint')){
		$erreurs['logint'] = _T('info_obligatoire');
	}
	if (!_request('loginc')){
		$erreurs['loginc'] = _T('info_obligatoire');
	}
	if (!_request('passc')){
		$erreurs['passc'] = _T('info_obligatoire');
	}
	if(count($erreurs) < 1){
		if(!preg_match(sn_regex_txt_brut(255,1),_request('logint'))){
			$erreurs['logint'] = _T('sncore:regex_txt_brut_nb',['nb'=>255]);
		}
	}
	if(count($erreurs) < 1){
		if(!preg_match(sn_regex_txt_brut(255,1),_request('loginc'))){
			$erreurs['loginc'] = _T('sncore:regex_txt_brut_nb',['nb'=>255]);
		}
	}
	if(count($erreurs) < 1){
		if (_request('passc')){
			if(!preg_match(sn_regex_txt_etendu(64,_PASS_LONGUEUR_MINI),_request('passc'))){
				$erreurs['passc'] = _T('sncore:regex_txt_etendu_nb',array('nb'=>'64')).' '._T('sncore:info_longueur_motdepasse',['min'=>_PASS_LONGUEUR_MINI,'max'=>64]);
			}
		}
	}
	if(count($erreurs) < 1){
		$req = sql_fetsel('login','spip_auteurs','login='.sql_quote(_request('logint')));
		if($req){
			$erreurs['emailt'] = _T('sncore:erreur_doublon_login');
		}
	}
	if(count($erreurs) < 1){
		if(_request('logint') !== _request('loginc')){
			$erreurs['loginc'] = _T('sncore:erreur_comparaison');
		}
	}
	if(count($erreurs) < 1){ // Verif pass confirm ok
		include_spip('inc/auth');
		$login = session_get('login');
		$password = _request('passc');
		$auteur = auth_identifier_login($login, $password);
		if (!is_array($auteur)) {
			$erreurs['passc'] = _T('login_erreur_pass');
			if (is_string($auteur) and strlen($auteur)) {
				$erreurs['passc'] .= ' '.$auteur;
			}
		}
	}

	return $erreurs;
	
}
function formulaires_editer_auteur_login_traiter($id_auteur,$retour=''){

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
		$req = sql_fetsel('email','spip_auteurs','id_auteur=' . intval($id_auteur));
		if($req){
			$p_envoi = [];
			$p_envoi['adresses'] = [$req];
			$p_envoi['ref'] = 'notif_compte_modif';
			$p_envoi['objet'] = 'login';
			$p_envoi['date_modif'] = date('Y-m-d H:i:d');
			$snenvoyer = charger_fonction('snenvoyer', 'action');
			[$destination, $message, $sujet, $source, $head] = $snenvoyer($id_auteur, $p_envoi);
			$notifications = charger_fonction('notifications', 'inc');
			notifications_envoyer_mails($destination, $message, $sujet, $source, $head);

			$retours = [];
			if(sql_updateq('spip_auteurs',['login'=>_request('logint')],'id_auteur='.$id_auteur)){
				$retours['message_ok'] = _T('info_modification_enregistree') . ' ' . _T('message_reconnexion_necessaire');
			} else {
				$retours['message_erreur'] = _T('erreur_ecriture');
			}
		} else {
			$retours['message_erreur'] = _T('sncore:erreur_acces_donnees');
		}
	} else {
		$retours['message_erreur'] = _T('sncore:erreur_autorisation_refusee');
	}

	if(isset($retours['message_ok'])){
		$retours['editable'] = true;
		$retours['apres_modif'] = true;
		if ($retour) {
			$retours['redirect'] = $retour;
		}
	}

	return $retours;
	
}
