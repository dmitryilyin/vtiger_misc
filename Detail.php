<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

# modules/Leads/views/Detail.php
# Assign lead fro ma group to the user who have opened this lead
# add $lead_assign_to_user_on_open_groups to config.inc.php
# with an array of group names or id from which user should be assigned

class Leads_Detail_View extends Accounts_Detail_View {

        function assignLeadToUser($lead_id, $user_id) {
          $db = PearDatabase::getInstance();
          $sql = 'UPDATE vtiger_crmentity c SET c.smownerid = ? WHERE c.crmid = ?';
          $db->pquery($sql, array($user_id, $lead_id));
          return true;
        }

        function preProcess(Vtiger_Request $request, $display=true) {
                parent::preProcess($request, $display);

		global $lead_assign_to_user_on_open_groups;

		if (empty($lead_assign_to_user_on_open_groups)) {
                  # group to user assignment is disabled or empty
                  return;
                }

                if (gettype($lead_assign_to_user_on_open_groups) != 'array') {
                  # group to user assignment has incorrect config
                  return;
                }

                $record = $this->record->getRecord();
                $lead_id = $record->getId();
                $assigned_user_id = $record->get('assigned_user_id');
		$group = Vtiger_Functions::getGroupRecordLabel($assigned_user_id);

                if (!$group) {
                  # lead doesn't belong to a group
                  return;
                }

                if (!(in_array($group, $lead_assign_to_user_on_open_groups) or in_array($assigned_user_id, $lead_assign_to_user_on_open_groups))) {
                  # lead doesn't belong to a group in the list or group_id in the list
                  return;
                }

                $currect_user = Users_Record_Model::getCurrentUserModel();
                $current_user_id = $currect_user->get('id');

                if ($current_user_id) {
                  # modify and save the record
                  $record->set('assigned_user_id', $current_user_id);
                  $this->assignLeadToUser($lead_id, $current_user_id);
                }
	}

}
