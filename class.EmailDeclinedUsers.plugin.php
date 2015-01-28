<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['EmailDeclinedUsers'] = array(
   'Description' => 'Sends an email to declined applicants.',
   'Version' => '1.0',
   'RequiredApplications' => array('Vanilla' => '2.1'),
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => FALSE,
   'HasLocale' => TRUE,
   'SettingsUrl' => '/settings/emaildeclinedusers',
   'SettingsPermission' => 'Garden.Settings.Manage',
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
                                $Email->Subject(sprintf(C('EmailDeclinedUsers.Subject'), C('Garden.Title')));
                                $Email->Message(sprintf(C('EmailDeclinedUsers.Body'), C('Garden.Title'), $User->Name));
                                $Email->To($User->Email);                                
                                $Email->Send();


         }

       }
   }
    public function settingsController_emailDeclinedUsers_create ($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->setData('Title', t('EmailDeclinedUsers Settings'));
        $sender->addSideMenu('dashboard/settings/plugins');
        $configurationModel = new ConfigurationModule($sender);

        $mailSubject = t('[%1$s] Membership Declined');
        $mailBody = t('Hello %2$s'."\r\n\r\nWe are sorry to inform you that you were declined for ".'%1$s'." membership. Please review your application and resubmit it with more information.\r\n\r\nThanks!");
        $configurationModel->Initialize(array(
            'EmailDeclinedUsers.Subject' => array(
                'LabelCode' => 'Subject',
                'Control' => 'TextBox',
                'Options' => array('Class' => 'InputBox WideInput'),
                'Default' => $mailSubject,
                'Description' => 'This is the subject of the mail a declined user will get.<br>"%1$s" is the name of your forum.'
            ),
            'EmailDeclinedUsers.Body' => array(
                'LabelCode' => 'Body',
                'Control' => 'TextBox',
                'Options' => array('Multiline' => true),
                'Default' => $mailBody,
                'Description' => 'This is the body of the mail a declined user will get.<br>"%1$s" is the name of your forum and "%2$s" is the name of the user.'
            )
        ));
        $configurationModel->renderAll();
    }
   
}
