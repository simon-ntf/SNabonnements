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

// Statuts des abonnements
$GLOBALS['SN_ABONNEMENTS_STATUTS'] = [
	 'publie' => _T('snabo:statut_publie'),
	 'archive' => _T('snabo:statut_archive'),
	 'prepa' => _T('snabo:statut_prepa'),
];

// Vider la balise alphanum car il ne doit jamais être affiché
function balise_SN_ALPHANUM_ID($p){
	$p->code = "''";
	$p->type = 'php';
	return $p;
}

// Vider la balise snid car il ne doit jamais être affiché
function balise_SNID($p){
	$p->code = "''";
	$p->type = 'php';
	return $p;
}
