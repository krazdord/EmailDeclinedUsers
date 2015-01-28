<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

// Define the plugin:
$PluginInfo['EmailDeclinedUsers'] = array(
   'Description' => 'Sends an email to declined applications.',
   'Version' => '1.0',
   'RequiredApplications' => array('Vanilla' => '2.1'),
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => FALSE,
   'HasLocale' => TRUE,
   'SettingsUrl' => '/plugin/EmailDeclinedUsers',
   'SettingsPermission' => 'Garden.AdminUser.Only',
   'Author' => "Krazdord",
   'AuthorEmail' => 'krazdord@gmail.com'
);

class EmailDeclinedUsersPlugin extends Gdn_Plugin {

   /**
    * Plugin constructor
    *
    * This fires once per page load, during execution of bootstrap.php. It is a decent place to perform
    * one-time-per-page setup of the plugin object. Be careful not to put anything too strenuous in here
    * as it runs every page load and could slow down your forum.
    */
   public function __construct() {
      
   }
   
   /**
    * Base_Render_Before Event Hook
    *
    * This is a common hook that fires for all controllers (Base), on the Render method (Render), just 
    * before execution of that method (Before). It is a good place to put UI stuff like CSS and Javascript 
    * inclusions. Note that all the Controller logic has already been run at this point.
    *
    * @param $Sender Sending controller instance
    */
   //public function Base_Render_Before($Sender) {
      //$Sender->AddCssFile($this->GetResource('design/example.css', FALSE, FALSE));
      //$Sender->AddJsFile($this->GetResource('js/example.js', FALSE, FALSE));
  // }
   
   /**
    * Create a method called "Example" on the PluginController
    *
    * One of the most powerful tools at a plugin developer's fingertips is the ability to freely create
    * methods on other controllers, effectively extending their capabilities. This method creates the 
    * Example() method on the PluginController, effectively allowing the plugin to be invoked via the 
    * URL: http://www.yourforum.com/plugin/Example/
    *
    * From here, we can do whatever we like, including turning this plugin into a mini controller and
    * allowing us an easy way of creating a dashboard settings screen.
    *
    * @param $Sender Sending controller instance
    */
   public function PluginController_EmailDeclinedUsers_Create($Sender) {
      /*
       * If you build your views properly, this will be used as the <title> for your page, and for the header
       * in the dashboard. Something like this works well: <h1><?php echo T($this->Data['Title']); ?></h1>
       */
      $Sender->Title('EmailDeclinedUsers Plugin');
      //$Sender->AddSideMenu('plugin/example');
      //$Sender->AddSideMenu('plugin/example');
      
      // If your sub-pages use forms, this is a good place to get it ready
      //$Sender->Form = new Gdn_Form();
      
      /*
       * This method does a lot of work. It allows a single method (PluginController::Example() in this case) 
       * to "forward" calls to internal methods on this plugin based on the URL's first parameter following the 
       * real method name, in effect mimicing the functionality of as a real top level controller. 
       *
       * For example, if we accessed the URL: http://www.yourforum.com/plugin/Example/test, Dispatch() here would
       * look for a method called ExamplePlugin::Controller_Test(), and invoke it. Similarly, we we accessed the
       * URL: http://www.yourforum.com/plugin/Example/foobar, Dispatch() would find and call 
       * ExamplePlugin::Controller_Foobar().
       *
       * The main benefit of this style of extending functionality is that all of a plugin's external API is 
       * consolidated under one namespace, reducing the chance for random method name conflicts with other
       * plugins. 
       *
       * Note: When the URL is accessed without parameters, Controller_Index() is called. This is a good place
       * for a dashboard settings screen.
       */
      $this->Dispatch($Sender, $Sender->RequestArgs);
   }
   
   public function Controller_Index($Sender) {
      // Prevent non-admins from accessing this page
      $Sender->Permission('Vanilla.Settings.Manage');
      
      $Sender->SetData('PluginDescription',$this->GetPluginKey('Description'));
		/*
		$Validation = new Gdn_Validation();
      $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
      $ConfigurationModel->SetField(array(
         'Plugin.Example.RenderCondition'    => 'all',
         'Plugin.Example.TrimSize'     => 100
      ));
      
      // Set the model on the form.
      $Sender->Form->SetModel($ConfigurationModel);
   
      // If seeing the form for the first time...
      if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
         // Apply the config settings to the form.
         $Sender->Form->SetData($ConfigurationModel->Data);
		} else {
         $ConfigurationModel->Validation->ApplyRule('Plugin.Example.RenderCondition', 'Required');

         $ConfigurationModel->Validation->ApplyRule('Plugin.Example.TrimSize', 'Required');
         $ConfigurationModel->Validation->ApplyRule('Plugin.Example.TrimSize', 'Integer');
         
         $Saved = $Sender->Form->Save();
         if ($Saved) {
            $Sender->StatusMessage = T("Your changes have been saved.");
         }
      }
      */
      // GetView() looks for files inside plugins/PluginFolderName/views/ and returns their full path. Useful!
      //$Sender->Render($this->GetView('example.php'));
   }
   
   /**
    * Add a link to the dashboard menu
    * 
    * By grabbing a reference to the current SideMenu object we gain access to its methods, allowing us
    * to add a menu link to the newly created /plugin/Example method.
    *
    * @param $Sender Sending controller instance
    */
   public function Base_GetAppSettingsMenuItems_Handler($Sender) {
      //$Menu = &$Sender->EventArguments['SideMenu'];
      //$Menu->AddLink('Add-ons', 'EmailDeclinedUsers', 'plugin/example', 'Garden.AdminUser.Only');
   }

  
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

   
   /**
    * Plugin setup
    *
    * This method is fired only once, immediately after the plugin has been enabled in the /plugins/ screen, 
    * and is a great place to perform one-time setup tasks, such as database structure changes, 
    * addition/modification ofconfig file settings, filesystem changes, etc.
    */
   public function Setup() {
   
      // Set up the plugin's default values
      //SaveToConfig('Plugin.Example.TrimSize', 100);
      //SaveToConfig('Plugin.Example.RenderCondition', "all");

      /*
      // Create table GDN_Example, if it doesn't already exist
      Gdn::Structure()
         ->Table('Example')
         ->PrimaryKey('ExampleID')
         ->Column('Name', 'varchar(255)')
         ->Column('Type', 'varchar(128)')
         ->Column('Size', 'int(11)')
         ->Column('InsertUserID', 'int(11)')
         ->Column('DateInserted', 'datetime')
         ->Column('ForeignID', 'int(11)', TRUE)
         ->Column('ForeignTable', 'varchar(24)', TRUE)
         ->Set(FALSE, FALSE);
      */
   }
   
   /**
    * Plugin cleanup
    *
    * This method is fired only once, immediately before the plugin is disabled, and is a great place to 
    * perform cleanup tasks such as deletion of unsued files and folders.
    */
   public function OnDisable() {
      //RemoveFromConfig('Plugin.Example.TrimSize');
      //RemoveFromConfig('Plugin.Example.RenderCondition');
   }
   
}
