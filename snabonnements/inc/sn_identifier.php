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
 * Gestion de l'inscription d'un auteur
 *
 * @package SPIP\Core\Inscription
 **/
use Spip\Chiffrer\Chiffrement;
use Spip\Chiffrer\SpipCles;

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Verifier l'identite d'un membre
 * @param int $id_auteur
 * @return bool|string
 */
function sn_identifier(){
	if(!extension_loaded('sodium')){ return true; }

	include_spip('inc/chiffrer');
	include_spip('inc/securiser_action');
	//include_spip('base/abstract_sql'); // Si chiffrer plante peut etre besoin d'ajouter ça
	//include_spip('inc/acces'); // Si chiffrer plante peut etre besoin d'ajouter ça

	$snauth = false;

	if( session_get('id_auteur') && session_get('login') && session_get('snid') ){
		$snid = session_get('snid');
		[$id_auteur, $pass] = caracteriser_auteur(session_get('id_auteur'));
		if( ($id_auteur > 0) && (strlen($pass) > 0) && (strlen($snid) > 0) ){
			if($alphanum = sql_getfetsel('sn_alphanum_id','spip_auteurs','id_auteur=' . intval($id_auteur) )){
				$vsnid = Chiffrement::dechiffrer($alphanum, SpipCles::secret_du_site());
				if(intval(Chiffrement::dechiffrer($snid, $vsnid)) === intval($id_auteur)){
					$snauth = true;
				}
			}
		}
	}

	return $snauth;

}

function sn_form_a_verifier($form){
	if(!extension_loaded('sodium')){ return []; }

	$vforms = [
	// SPIP
	'admin_plugins',
	'declarer_bases',
	'editer_liens',
	'recherche_ecrire',
	'restaurer',
	'sauvegarder',
	'configurer_identite',
	'configurer_urls',
	'configurer_urls_propres',
	'configurer_articles',
	'configurer_rubriques',
	'configurer_logos',
	'configurer_flux',
	'configurer_mots',
	'configurer_sites',
	'configurer_documents',
	'configurer_reducteur',
	'configurer_avertisseur',
	'configurer_relayeur',
	'configurer_porte_plume',
	'configurer_compteur',
	'configurer_bigup',
	'configurer_compresseur',
	'configurer_forums_participants',
	'configurer_forums_contenu',
	'configurer_forums_prives',
	'configurer_forums_notifications',
	'configurer_forums_participants',
	'configurer_redacteurs',
	'configurer_visiteurs',
	'configurer_annonces',
	'configurer_langue',
	'configurer_transcodeur',
	'configurer_multilinguisme',
	'configurer_transcodeur',
	'configurer_revisions_objets',
	'editer_article',
	'editer_auteur',
	'editer_breve',
	'editer_document',
	'editer_mot',
	'editer_rubrique',
	'editer_site',
	// SNIMAGES
	'generer_galerie',
	'generer_image',
	'joindre_snimage',
	'recup_snimages',
	// SNEDITION
	'configurer_snedition',
	'generer_bloc',
	'generer_diapo',
	'generer_geoloc',
	'generer_icone',
	'generer_infobulle',
	'generer_lien',
	'generer_video',
	// SNPUSHS (si besoin)
	'configurer_snpushs',
	'editer_snpushs',
	];

	if(in_array($form, $vforms)){ return true; }

	return false;

}

/*
 * Ajoute des alphanum_id a tous les auteurs lors de l'install du plugin.
 *
 * @param array $maj_data Tableau de mise à jour de la BDD spip issu du fichier administrations.
 * @return array Le tableau en paramètre avec les lignes update en plus

 * ATTENTION : cette fonction est utilisée lors de la maj du plugin, tester obligatoirement l'install en cas de modification.
 * Dépendance : cette fonction fait appel à sn_crea_alphanum_str() (plugin SN Core).
*/
function snid_creer_tous($maj_data){
	if(!extension_loaded('sodium')){ return $maj_data; }
	$alphanum=''; $id_auteur=0;
	if($req = sql_select(['id_auteur'], 'spip_auteurs')){
		$nb_auteurs = sql_count($req);
		for($i=0;$i<$nb_auteurs;$i++){
			$res[$i] = sql_fetch($req);
			$id_auteur = $res[$i]['id_auteur'];
			$alphanum = snid_creer();
			$maj_data[] = ['sql_updateq', 'spip_auteurs', ['sn_alphanum_id' => $alphanum], 'id_auteur=' . $id_auteur];
		}
	}
	return $maj_data;
}

function snid_creer(){
	if(!extension_loaded('sodium')){ return ''; }
	include_spip('inc/chiffrer');
	$alphanum = Chiffrement::chiffrer(sn_crea_alphanum_str(16,4), SpipCles::secret_du_site());
	return $alphanum;
}

function snid_normal($id_auteur,$sn_alphanum_id){
	if(!extension_loaded('sodium')){ return ''; }
	include_spip('inc/chiffrer');
	$alphanum = Chiffrement::dechiffrer($sn_alphanum_id, SpipCles::secret_du_site());
	$snid = Chiffrement::chiffrer(strval($id_auteur), $alphanum);
	return $snid;
}


/*
 * Verifie si une date de consentement RGPD est toujours valable
 *
 * @param str $consentement 	Chaîne de date SQL format 0000-00-00 00:00:00
 * @return str 					Statut du consentement : oui (ok) | ini (pas de consentement) | pre (expire bientot) | exp (a expire)
 *
 */

function sn_rgpd($consentement){
	include_spip('inc/sn_datr');
	include_spip('inc/sn_const');
	$sn_const_dp_conservation_annees = sn_global_dp_conservation_annees();
	$sn_const_dp_renouvellement_jours = sn_global_dp_renouvellement_jours();
	$conservation = $sn_const_dp_conservation_annees*365;
	$prevenir = $conservation-$sn_const_dp_renouvellement_jours;
	$dconsentement = date($consentement);
	$retour = 'oui';
	if(strtotime($dconsentement) == 0){
		$retour = 'ini';
	} elseif(sn_si_echeance_passee_jours($dconsentement,$conservation) === true){
		$retour = 'exp';
	} elseif(sn_si_echeance_passee_jours($dconsentement,$prevenir) === true){
		$retour = 'pre';
	}
	return $retour;
}
