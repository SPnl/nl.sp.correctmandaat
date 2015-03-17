<?php


class CRM_Correctmandaat_Correct_Voorloopnullen {

    public function verwijderVoorloopnullen($mandaat_nr) {
        //verwijder voorloopnullen
        //origineel is: MBOA-0000000000040364-0000000000001
        //het moet zijn: MBOA-40364-1
        $new_mandaat_nr = preg_replace('/(MBOA\-)(0*)([1-9][0-9]*\-)(0*)([1-9][0-9]*)/i', '${1}${3}${5}', $mandaat_nr);
        $this->changeMandaatInDatabase($mandaat_nr, $new_mandaat_nr);
    }

    /**
     * Update database with the new mandaat
     *
     * @param $original_mandaat_nr
     * @param $new_mandaat_nr
     */
    protected function changeMandaatInDatabase($original_mandaat_nr, $new_mandaat_nr) {
        $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
        $c_config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
        $m_config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();

        $params = array(
            1 => array($new_mandaat_nr, 'String'),
            2 => array($original_mandaat_nr, 'String')
        );

        $sql = "UPDATE `".$config->getCustomGroupInfo('table_name')."` SET `mandaat_nr` = %1 WHERE `mandaat_nr` = %2";
        CRM_Core_DAO::executeQuery($sql, $params);

        $sql = "UPDATE `".$c_config->getCustomGroupInfo('table_name')."` SET `mandaat_id` = %1 WHERE `mandaat_id` = %2";
        CRM_Core_DAO::executeQuery($sql, $params);

        $sql = "UPDATE `".$m_config->getCustomGroupInfo('table_name')."` SET `mandaat_id` = %1 WHERE `mandaat_id` = %2";
        CRM_Core_DAO::executeQuery($sql, $params);
    }
}