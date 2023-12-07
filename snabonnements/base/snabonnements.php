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
 * Bases de données du plugin SN Abonnements
 *
 * @plugin snabonnements
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function snabonnements_declarer_tables_interfaces($interface){
	$interface['table_des_tables']['snabonnements'] = 'snabonnements';
	return $interface;
}

function snabonnements_declarer_tables_objets_sql($tables) {
	$tables['spip_snabonnements'] = [
		'principale' => "oui",
		'field'=> [
			"id_snabonnement" 	=> "bigint(21) NOT NULL",
			"titre"				=> "tinytext DEFAULT '' NOT NULL",
			"date" 				=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
			"maj" 				=> "TIMESTAMP",
			"statut"			=> "varchar(10) DEFAULT 'prepa' NOT NULL",
			"resume"			=> "text DEFAULT '' NOT NULL",
		],
		'key' => [
			"PRIMARY KEY"		=> "id_snabonnement",
			"KEY statut"		=> "statut",
		],
		'join' => [
			"id_snabonnement" => "id_snabonnement",
		],
		'champs_editables' 		=> ["date","maj","titre","statut","resume"],
		'titre'					=> "titre AS titre, '' AS lang",
		'date' 					=> "date",
		'info_aucun_objet' => 'snabo:info_aucun_objet',
		'info_1_objet' => 'snabo:info_1_objet',
		'info_nb_objets' => 'snabo:info_nb_objets',
		'texte_ajouter'	=> 'snabo:texte_ajouter',
		'texte_creer' => 'snabo:texte_creer',
		'texte_creer_associer' => 'snabo:texte_creer_associer',
		'texte_modifier' => 'snabo:texte_modifier',
		'texte_objets' => 'snabo:texte_objets',
		'texte_objet' => 'snabo:texte_objet',
		'texte_retour' => 'icone_retour',
	];
	$tables['spip_auteurs']['champs_editables'][] = "snabonnements";
	$tables['spip_auteurs']['champs_editables'][] = "sn_alphanum_id";
	$tables['spip_auteurs']['champs_editables'][] = "sn_civilite";
	$tables['spip_auteurs']['champs_editables'][] = "sn_date_consentement_dp";
	$tables['spip_auteurs']['champs_editables'][] = "sn_date_desinscr";
	$tables['spip_auteurs']['champs_editables'][] = "sn_date_inscr";
	$tables['spip_auteurs']['champs_editables'][] = "sn_date_fiche_publique";
	$tables['spip_auteurs']['champs_editables'][] = "sn_date_modif_login";
	$tables['spip_auteurs']['champs_editables'][] = "sn_date_naissance";
	$tables['spip_auteurs']['champs_editables'][] = "sn_email2";
	$tables['spip_auteurs']['champs_editables'][] = "sn_emailex";
	$tables['spip_auteurs']['champs_editables'][] = "sn_fonctions";
	$tables['spip_auteurs']['champs_editables'][] = "sn_genre";
	$tables['spip_auteurs']['champs_editables'][] = "sn_nom";
	$tables['spip_auteurs']['champs_editables'][] = "sn_pays";
	$tables['spip_auteurs']['champs_editables'][] = "sn_prenom";
	$tables['spip_auteurs']['champs_editables'][] = "sn_publier_fiche";
	$tables['spip_auteurs']['champs_editables'][] = "sn_societe";
	$tables['spip_auteurs']['champs_editables'][] = "sn_suivi_auth";
	$tables['spip_auteurs']['champs_editables'][] = "sn_tel";
	$tables['spip_auteurs']['champs_editables'][] = "sn_ville";
	return $tables;
}
