<?php

class CRM_Correctmandaat_Correct_DubbeleMandaat {

    public function removeMandaatFromAcceptgiroContribution() {
        $incasso_id = CRM_Core_DAO::singleValueQuery("SELECT value from civicrm_option_value v inner join civicrm_option_group g ON v.option_group_id = g.id where g.name = 'payment_instrument' AND v.name = 'sp_automatischincasse'");
        $config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
        $sql = "DELETE FROM `".$config->getCustomGroupInfo('table_name')."`
                WHERE `entity_id` NOT IN (
                    SELECT c.id FROM `civicrm_contribution` `c`
                    WHERE `c`.`payment_instrument_id` = '" . $incasso_id . "'
                );";
        CRM_Core_DAO::executeQuery($sql);
    }

    public function removeMandaatFromAcceptgiroMembership() {
        $incasso_id = CRM_Core_DAO::singleValueQuery("SELECT value from civicrm_option_value v inner join civicrm_option_group g ON v.option_group_id = g.id where g.name = 'payment_instrument' AND v.name = 'sp_automatischincasse'");
        $config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
        $sql = "DELETE FROM `".$config->getCustomGroupInfo('table_name')."`
                WHERE `entity_id` NOT IN (
                    SELECT m.id FROM civicrm_membership m
                    INNER JOIN civicrm_membership_payment mp on m.id = mp.membership_id
                    INNER JOIN civicrm_contribution c ON mp.contribution_id = c.id
                    WHERE `c`.`payment_instrument_id` = '" . $incasso_id . "'
                );";
        CRM_Core_DAO::executeQuery($sql);
    }

    public function removeMandaatFromContact() {
        $config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
        $c_config = CRM_Sepamandaat_Config_ContributionSepaMandaat::singleton();
        $m_config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
        $dao = CRM_Core_DAO::executeQuery("SELECT id, entity_id, mandaat_nr FROM `".$config->getCustomGroupInfo('table_name')."`");
        while($dao->fetch()) {
            $existInMembership = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*)
                                                                FROM `".$m_config->getCustomGroupInfo('table_name')."` `mandaat`
                                                                INNER JOIN `civicrm_membership` `m` ON m.id = mandaat.entity_id
                                                                WHERE `mandaat_id` = %1 AND m.contact_id = %2
                                                                ",
                                                                array(
                                                                    1 => array($dao->mandaat_nr, 'String'),
                                                                    2 => array($dao->entity_id, 'Integer')
                                                                ));
            $existInContribution = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*)
                                                                FROM `".$c_config->getCustomGroupInfo('table_name')."` `mandaat`
                                                                INNER JOIN `civicrm_contribution` `c` ON c.id = mandaat.entity_id
                                                                WHERE `mandaat_id` = %1 AND c.contact_id = %2
                                                                ",
                                                                array(
                                                                    1 => array($dao->mandaat_nr, 'String'),
                                                                    2 => array($dao->entity_id, 'Integer')
                                                                ));
            if ($existInContribution == 0 && $existInMembership == 0) {
                CRM_Core_DAO::executeQuery("DELETE FROM `".$config->getCustomGroupInfo('table_name')."` WHERE `id` = %1", array(1 => array($dao->id, 'Integer')));
            }
        }
    }

}