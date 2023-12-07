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

include_spip(_DIR_PLUGIN_SNABONNEMENTS . 'inc/sn_identifier');

function exec_snabonnements_export(){

	if(sn_identifier() !== true){
		return '';
	}
	$id_snabonnement = session_get('snabonnement_a_exporter');
	$nom_fichier = 'Abonnements_' . $id_snabonnement;
	$export = snabonnements_exporter($id_snabonnement,'*');
	header('Content-Transfer-Encoding: binary');
	header('Content-Disposition: attachment; filename="' . $nom_fichier . '.csv"');
	header('Content-Length: ' . strlen($export));
	header('Content-Type: text/csv');
	echo $export;
	return;

}

function snabonnements_exporter($id_snabonnement,$champs='*'){

	if(sn_identifier() !== true){
		return null;
	}
	$lignes = [];
	$ligne;
	$abonnements;
	if($champs === '*'){
		$champs = ['id_auteur','email','snabonnements','sn_civilite','sn_nom','sn_prenom','sn_genre','sn_ville','sn_pays','sn_fonctions','sn_societe','sn_tel','sn_date_inscr','sn_publier_fiche','nom','statut'];
	}
	if(!is_array($champs)){
		return ['message_erreur' => _T('sncore:erreur_technique',['err'=>'Type incorrect (array attendu) / Plugin snabonnements/exec/snabonnement_export->snabonnements_exporter'])];
	}
	$lignes[] = $champs;
	$abonnes_req = sql_allfetsel($champs,'spip_auteurs','statut="1comite" OR "6forum" OR "0minirezo"');
	foreach($abonnes_req as $cle => $abo_data){
		$abonnements = explode(',',$abo_data['snabonnements']);
		if(in_array($id_snabonnement,$abonnements) === true){
			$ligne = [];
			foreach($champs as $cle => $champ_ref){
				$ligne[] = $abo_data[$champ_ref];
			}
			$lignes[] = $ligne;
		}
	}
	return outputCSV($lignes);

}

function outputCSV($data) {
    $output = fopen("php://output", "w");
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
}

