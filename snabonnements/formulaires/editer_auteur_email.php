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

function formulaires_editer_auteur_email_charger($id_auteur,$retour=''){

	include_spip('inc/sn_const');
	include_spip('inc/sn_datr');
	$sn_const_email_delai_changement = sn_global_compte_email_delai();

	$valeurs = [];
	$valeurs['editable'] = '';
	$autorisation = autoriser('modifier', 'auteur', intval($id_auteur));

	if ($autorisation === true) {
		include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
		$controle = sn_identifier();
	}
	if ($controle === true && $autorisation === true) {
		// Traitement si le mail a ete modifie dans les 72h
		$req = sql_getfetsel('sn_date_modif_login','spip_auteurs','id_auteur='.intval($id_auteur));
		if($req){
			$attente_non_requise = false;
			if(intval($req) == 0){
				$attente_non_requise = true;
			} else {
				$attente_non_requise = sn_si_echeance_passee_heures(date($req),$sn_const_email_delai_changement);
			}
			if($attente_non_requise === true){
				$valeurs['editable'] = true;
				$valeurs['id_auteur'] = $id_auteur;
			} else {
				$valeurs = [];
				$valeurs['editable'] = '';
				$valeurs['bloquage_temporaire'] = 'oui';
				$valeurs['bloquage_temporaire_heures'] = $sn_const_email_delai_changement;
			}
		} else {
			return false; // Ne devrait jamais arriver mais bon
		}
	} else{
		return false;
	}
	
	return $valeurs;
	
}
function formulaires_editer_auteur_email_verifier($id_auteur,$retour=''){

	include_spip('inc/sn_regexr');

	$erreurs = [];
	if (!_request('emailt')){
		$erreurs['emailt'] = _T('info_obligatoire');
	}
	if (!_request('emailc')){
		$erreurs['emailc'] = _T('info_obligatoire');
	}
	if (!_request('passc')){
		$erreurs['passc'] = _T('info_obligatoire');
	}
	if(count($erreurs) < 1){
		if(strlen(_request('emailt')) > 244){
			$erreurs['emailt'] = _T('sncore:regex_txt_longueur_nb');
		}
	}
	if(count($erreurs) < 1){
		if(strlen(_request('emailc')) > 244){
			$erreurs['emailc'] = _T('sncore:regex_txt_longueur_nb');
		}
	}
	if(count($erreurs) < 1){
		if(!filter_var(_request('emailt'), FILTER_VALIDATE_EMAIL)){
			$erreurs['emailt'] = _T('sncore:regex_email');
		}
	}
	if(count($erreurs) < 1){
		if(!filter_var(_request('emailc'), FILTER_VALIDATE_EMAIL)){
			$erreurs['emailc'] = _T('sncore:regex_email');
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
		if(_request('emailt') !== _request('emailc')){
			$erreurs['emailc'] = _T('sncore:erreur_comparaison_email');
		}
	}
	if(count($erreurs) < 1){
		$req = sql_getfetsel('email','spip_auteurs','sn_email2='.sql_quote(_request('emailt')));
		if($req){
			$erreurs['emailt'] = _T('sncore:erreur_doublon_email');
		}
	}
	if(count($erreurs) < 1){
		$req = sql_getfetsel('sn_email2','spip_auteurs','sn_email2='.sql_quote(_request('emailt')));
		if($req){
			$erreurs['emailt'] = _T('sncore:erreur_doublon_email');
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

function formulaires_editer_auteur_email_traiter($id_auteur,$retour=''){

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
		$req = sql_fetsel(['id_auteur','email'],'spip_auteurs','id_auteur=' . intval($id_auteur));
		if($req){
			$mailex = $req['email'];
			$mailneo = _request('emailt');
			$sqlnow = date('Y-m-d H:i:d');

			$p_envoi = [];
			$p_envoi['ref'] = 'notif_compte_modif';
			$p_envoi['objet'] = 'email';
			$p_envoi['adresses'] = [$mailex];
			$p_envoi['nouvelle_adresse'] = $mailneo;
			$p_envoi['date_modif'] = $sqlnow;
			$snenvoyer = charger_fonction('snenvoyer', 'action');
			[$destination, $message, $sujet, $source, $head] = $snenvoyer($id_auteur, $p_envoi);
			$notifications = charger_fonction('notifications', 'inc');
			notifications_envoyer_mails($destination, $message, $sujet, $source, $head);
			$retours = [];
			if(sql_updateq('spip_auteurs',['email' => $mailneo, 'sn_emailex' => $mailex, 'sn_date_modif_login' => $sqlnow],'id_auteur='.$id_auteur)){
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
