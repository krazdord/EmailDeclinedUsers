<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['EmailDeclinedUsers'] = array(
   'Description' => 'Sends an email to declined applicants.',
   'Version' => '1.0',
   'RequiredApplications' => array('Vanilla' => '2.1'),
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => FALSE,
   'HasLocale' => TRUE,
   'SettingsUrl' => '/plugin/EmailDeclinedUsers',
   'SettingsPermission' => 'Garden.AdminUser.Only',
   'Author' => "Krazdord",
   'AuthorEmail' => 'krazdord@gmail.com',
   'License' => 'MIT'
);

class EmailDeclinedUsersPlugin extends Gdn_Plugin {

  
    public function userController_beforeDeclineUser_handler ($Sender) {
      $ApplicantRoleID = C('Garden.Registration.ApplicantRoleID', 0);
      $UserModel = Gdn::UserModel();


      $UserID = $Sender->EventArguments['UserID'];
      // Make sure the user is an applicant
      $RoleData = $UserModel->GetRoles($UserID);
      if ($RoleData->NumRows() == 0) {
         throw new Exception(T('ErrorRecordNotFound'));
      } else {
         $AppRoles = $RoleData->Result(DATASET_TYPE_ARRAY);
         $ApplicantFound = FALSE;
         foreach ($AppRoles as $AppRole)
            if (GetValue('RoleID', $AppRole) == $ApplicantRoleID) $ApplicantFound = TRUE;
      }

      if ($ApplicantFound) {
         // Send out a notification to the user
         $User = $UserModel->GetID($UserID);
         if ($User) {
		  $Email = new Gdn_Email();
                                $Email->Subject(sprintf(T('[%1$s] Membership Declined'), C('Garden.Title')));
                                $Email->Message(sprintf(T('EmailMembershipDeclined'), $User->Name));
                                $Email->To($User->Email);                                
                                $Email->Send();


         }

       }
   }
   
}
