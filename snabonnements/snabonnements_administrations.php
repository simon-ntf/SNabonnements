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

if (!defined('_ECRIRE_INC_VERSION')) { return; }

function snabonnements_upgrade($nom_meta_base_version, $version_cible){
	$maj = [];
	$maj['create'] = [
		['maj_tables', ["spip_snabonnements"]],
		['sql_alter', "table spip_auteurs ADD snabonnements varchar(256) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_alphanum_id tinytext"],
		['sql_alter', "table spip_auteurs ADD sn_civilite varchar(32) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_date_consentement_dp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_date_desinscr datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_date_fiche_publique datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_date_inscr datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_date_modif_login datetime DEFAULT '0000-00-00 00:00:00' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_date_naissance datetime DEFAULT '1909-09-09 09:09:09' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_email2 varchar(256) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_emailex varchar(256) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_fonctions varchar(256) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_genre varchar(8) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_nom varchar(64) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_pays varchar(32) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_prenom varchar(64) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_publier_fiche varchar(2) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_societe varchar(128) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_suivi_auth varchar(10) DEFAULT '0spip' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_tel varchar(16) DEFAULT '' NOT NULL"],
		['sql_alter', "table spip_auteurs ADD sn_ville varchar(64) DEFAULT '' NOT NULL"],
	];
	include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');
	$maj['create'] = snid_creer_tous($maj['create']);

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}
function snabonnements_vider_tables($nom_meta_base_version) {
	sql_alter("table spip_auteurs DROP snabonnements");
	sql_alter("table spip_auteurs DROP sn_alphanum_id");
	sql_alter("table spip_auteurs DROP sn_civilite");
	sql_alter("table spip_auteurs DROP sn_date_consentement_dp");
	sql_alter("table spip_auteurs DROP sn_date_desinscr");
	sql_alter("table spip_auteurs DROP sn_date_fiche_publique");
	sql_alter("table spip_auteurs DROP sn_date_inscr");
	sql_alter("table spip_auteurs DROP sn_date_modif_login");
	sql_alter("table spip_auteurs DROP sn_date_naissance");
	sql_alter("table spip_auteurs DROP sn_email2");
	sql_alter("table spip_auteurs DROP sn_emailex");
	sql_alter("table spip_auteurs DROP sn_fonctions");
	sql_alter("table spip_auteurs DROP sn_genre");
	sql_alter("table spip_auteurs DROP sn_nom");
	sql_alter("table spip_auteurs DROP sn_pays");
	sql_alter("table spip_auteurs DROP sn_prenom");
	sql_alter("table spip_auteurs DROP sn_publier_fiche");
	sql_alter("table spip_auteurs DROP sn_societe");
	sql_alter("table spip_auteurs DROP sn_suivi_auth");
	sql_alter("table spip_auteurs DROP sn_tel");
	sql_alter("table spip_auteurs DROP sn_ville");
	sql_drop_table("spip_snabonnements");
	
	effacer_meta($nom_meta_base_version);
}
