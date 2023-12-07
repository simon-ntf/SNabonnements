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
function snabonnements_autoriser() {
}
function autoriser_snabonnements_voir_dist($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo';
}
function autoriser_snabonnements_menu_dist($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo';
}
function autoriser_snabonnement_modifier_dist($faire, $type, $id, $qui, $opt) {
	if ( !intval($id) OR !$qui['id_auteur'] OR !autoriser('ecrire', '', '', $qui) ) {
		return false;
	}
	return $qui['statut'] == '0minirezo';
}
function autoriser_snabonnement_supprimer_dist($faire, $type, $id, $qui, $opt) {
	if ( !intval($id) OR !$qui['id_auteur'] OR !autoriser('ecrire', '', '', $qui) ) {
		return false;
	}
	return autoriser('modifier', 'snabonnement', $id, $qui, $opt);
}
function autoriser_snabonnement_voir_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('modifier', 'snabonnement', $id, $qui, $opt);
}
function autoriser_snabonnement_exporter_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('modifier', 'snabonnement', $id, $qui, $opt);
}
