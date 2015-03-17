<?php

/**
 * Collection of upgrade steps
 */
class CRM_Correctmandaat_Upgrader extends CRM_Correctmandaat_Upgrader_Base {

    const BATCH_SIZE = 150;

    public function upgrade_1001() {
        $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
        $minId = CRM_Core_DAO::singleValueQuery('SELECT min(id) FROM '.$config->getCustomGroupInfo('table_name'));
        $maxId = CRM_Core_DAO::singleValueQuery('SELECT max(id) FROM '.$config->getCustomGroupInfo('table_name'));
        for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
            $endId = $startId + self::BATCH_SIZE - 1;
            $title = ts('Correct contributions (%1 / %2)', array(
                1 => $startId,
                2 => $maxId,
            ));
            $this->addTask($title, 'correctVoorloopnullen', $startId, $endId);
        }

        return true;
    }

    public function install() {
        $d = new CRM_Correctmandaat_Correct_DubbeleMandaat();
        $d->removeMandaatFromAcceptgiroContribution();
        $d->removeMandaatFromAcceptgiroMembership();
        $d->removeMandaatFromContact();
    }

    public function enable() {
        CRM_Core_BAO_Setting::setItem('1000', 'Extension', 'nl.sp.correctmandaat:version');
    }

    public static function correctVoorloopnullen($startId, $endId) {
        $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
        $dao = CRM_Core_DAO::executeQuery("SELECT mandaat_nr FROM `".$config->getCustomGroupInfo('table_name')."` WHERE `id` BETWEEN %1 AND %2", array(
            1 => array($startId, 'Integer'),
            2 => array($endId, 'Integer'),
        ));
        $correct = new CRM_Correctmandaat_Correct_Voorloopnullen();
        while($dao->fetch()) {
            $correct->verwijderVoorloopnullen($dao->mandaat_nr);
        }
        return true;
    }



}
