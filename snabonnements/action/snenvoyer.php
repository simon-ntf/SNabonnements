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
 * Gestion de l'action snenvoyer
 *
 * @package SnAbonnements\Action
 **/

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Action d'envoi d'un mail
 *
 * L'identifiant de l'auteur ciblé est donné en paramètre de cette fonction ou
 * en argument de l'action sécurisée. Il est obligatoire.
 *
 * @param null|int $arg
 *     Identifiant de l'auteur. En absence utilise l'argument
 *     de l'action sécurisée.
 * @return array
 *     Liste (identifiant de l'auteur, Texte d'erreur éventuel)
 **/
function action_snenvoyer_dist($id_membre, $champs = null){

	include_spip('inc/sn_const');
	$sn_const_notifs_ref_mails = sn_global_notifs_ref_mails();

	$retour = null;

	if (is_null($id_membre)) {
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_membre = $securiser_action();
	}

	$id_membre = intval($id_membre);

	$id_auteur = intval(session_get('id_auteur'));
	if( ($id_membre == 0) || ($id_auteur == 0) ){
		return _T('snabo:email_erreur_envoi_auth');
	}

	$source = '';
	if(isset($champs['source'])){ $source = email_valide($champs['source']); }
	if($source == ''){
		$source = $GLOBALS['meta']['email_webmaster'];
	}
	if($source == ''){
		return _T('snabo:email_erreur_envoi_source'); // Si pas de source laisse tomber
	}
	$champs['source'] = null; // Une fois verifies on les elimine, ils seront recharges proprement par la fc suivante

	$ref = '';
	if(isset($champs['ref'])){
		if(isset($sn_const_notifs_ref_mails[$champs['ref']])){
			$ref = $champs['ref'];
		}
	}
	$champs['ref'] = null; // Une fois verifies on les elimine, ils seront recharges proprement par la fc suivante

	$mode = 'prive';
	if(isset($champs['mode'])){
		if($champs['mode'] === 'public'){
			$mode = 'public';
		}
	}
	$champs['mode'] = null; // Une fois verifies on les elimine, ils seront recharges proprement par la fc suivante

	$adresses = '';
	if(isset($champs['adresses'])){
		$adresses = email_valide($champs['adresses']);
	}
	if($adresses == ''){
		return _T('snabo:email_erreur_envoi_destination'); // Si pas de destination laisse tomber
	}
	$champs['adresses'] = null; // Une fois verifies on les elimine, ils seront recharges proprement par la fc suivante si tout est ok

	$snenvoyer1 = charger_fonction('snenvoyer_un_mail', 'action');
	$retour = $snenvoyer1($id_membre, $id_auteur, $source, $ref, $mode, $adresses, $champs);

	return $retour;
}

/**
 * Construction d'un mail de notification
 *
 * Fonction redefinissable qui doit retourner un tableau
 * dont les elements seront les arguments de inc_envoyer_mail
 *
 * @param int $id_membre 	Auteur destinataire
 * @param int $id_auteur 	Auteur courant
 * @param string $source	Adresse d'envoi
 * @param string $ref 		Reference modele et textes
 * @param string $mode		Public ou Prive
 * @param array $adresses	Adresses de destination
 * @param array $contexte	Environnement du modele
 * @return array				Parametres envoi mail
 */
function action_snenvoyer_un_mail_dist($id_membre, $id_auteur, $source, $ref, $mode='prive', $adresses=[], $contexte=[]) {

	$contexte['id_membre'] = $id_membre;
	$contexte['id_auteur'] = $id_auteur;
	$contexte['ref'] = $ref;
	$contexte['source'] = $source;
	$contexte['mode'] = $mode;

	$chemin_base = 'prive/';
	if($mode === 'public'){ $chemin_base = ''; }

	$squelette_mail = $chemin_base.'modeles/mail/' . $ref;
	$message = recuperer_fond($squelette_mail, $contexte);

	$destination = $adresses;

	$head = '';
	$sujet = _T('snabo:email_compte_modif_titre');

	return [$destination, $message, $sujet, $source, $head];
}
