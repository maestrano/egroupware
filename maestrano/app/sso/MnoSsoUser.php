<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    $login = $this->uid;
    $login .= '@'.$GLOBALS['egw_info']['server']['default_domain'];
    //echo $login;
    $GLOBALS['sessionid'] = $GLOBALS['egw']->session->create($login,'','text',false,false);
    //var_dump($GLOBALS['sessionid']);
    // // First set $conn variable (need global variable?)
    // $conn = $this->connection;
    // 
    // $sel1 = $conn->query("SELECT ID,name,lastlogin FROM user WHERE ID = $this->local_id");
    // $chk = $sel1->fetch();
    // if ($chk["ID"] != "") {
    //     $now = time();
    //     
    //     // Set session
    //     $this->session['userid'] = $chk['ID'];
    //     $this->session['username'] = stripslashes($chk['name']);
    //     $this->session['lastlogin'] = $now;
    //     
    //     // Update last login timestamp
    //     $upd1 = $conn->query("UPDATE user SET lastlogin = '$now' WHERE ID = $this->local_id");
    //     
    //     return true;
    // } else {
    //     return false;
    // }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      // Get soaccount instance
      $manager = new soaccounts();
      $userData = $this->buildLocalUser();
      
      // Create user
      $lid = $manager->add_user($userData);
    }
    
    return $lid;
  }
  
  /**
   * Build the local user before creation
   *
   * @return the user object
   */
  protected function buildLocalUser()
  {
    $permissions = array('addressbook' => 1,
    'bookmarks' => 1,
    'calendar' => 1,
    'addressbook' => 1,
    'admin' => 1,
    'bookmarks' => 1,
    'groupdav' => 1,
    'calendar' => 1,
    'felamimail' => 1,
    'emailadmin' => 1,
    'etemplate' => 1,
    'filemanager' => 1,
    'phpfreechat' => 1,
    'gallery' => 1,
    'importexport' => 1,
    'infolog' => 1,
    'phpbrain' => 1,
    'manual' => 1,
    'news_admin' => 1,
    'notifications' => 1,
    'polls' => 1,
    'preferences' => 1,
    'projectmanager' => 1,
    'registration' => 1,
    'resources' => 1,
    'sambaadmin' => 1,
    'sitemgr' => 1,
    'syncml' => 1,
    'phpsysinfo' => 1,
    'timesheet' => 1,
    'tracker' => 1,
    'developer_tools' => 1,
    'sitemgr-link' => 1,
    'wiki' => 1
    );
    
    $password = $this->generatePassword();
		$userData = array(
			'account_type'          => 'u',
			'account_lid'           => $this->uid,
			'account_firstname'     => $this->name,
			'account_lastname'      => $this->surname,
			'account_passwd'        => $password,
			'status'                => 'A',
			'account_status'        => 'A',
			'old_loginid'           => '',
			'account_id'            => 0,
			'account_primary_group' => $this->getRoleIdToAssign(),
			'account_passwd_2'      => $password,
			'account_groups'        => '',
			'anonymous'             => 0,
			'changepassword'        => '1',
			'account_permissions'   => $permissions,
			'homedirectory'         => '',
			'loginshell'            => '',
			'expires'               => -1,
			'account_expires'       => -1,
			'account_email'         => $this->email
		);
    
    return $userData;
  }
  
  /**
   * Create the role to give to the user based on context
   * If the user is the owner of the app or at least Admin
   * for each organization, then it is given the role of 'Admin'.
   * Return 'User' role otherwise
   *
   * @return the ID of the user created, null otherwise
   */
  public function getRoleIdToAssign() {
    $role_id = -1; // User
    
    if ($this->app_owner) {
      $role_id = -2; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $role_id = -2;
        } else {
          $role_id = -1;
        }
      }
    }
    
    return $role_id;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $result = $this->connection->query("SELECT account_id FROM egw_accounts WHERE mno_uid = {$this->connection->quote($this->uid)} LIMIT 1")->fetch();
    
    if ($result && $result['account_id']) {
      return $result['account_id'];
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    $result = $this->connection->query("SELECT account_id FROM egw_addressbook WHERE contact_email = {$this->connection->quote($this->email)} LIMIT 1")->fetch();
    
    if ($result && $result['account_id']) {
      return $result['account_id'];
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       $upd1 = $this->connection->query("UPDATE egw_addressbook 
         SET n_fn = {$this->connection->quote($this->name . ' ' . $this->surname)},
         n_given = {$this->connection->quote($this->name)},
         n_family = {$this->connection->quote($this->name)},
         contact_email = {$this->connection->quote($this->email)}
         WHERE account_id = $this->local_id");
       $upd2 = $this->connection->query("UPDATE egw_accounts 
         SET account_lid = {$this->connection->quote($this->uid)}
         WHERE account_id = $this->local_id");
       return $upd1 && $upd2;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $upd = $this->connection->query("UPDATE egw_accounts SET mno_uid = {$this->connection->quote($this->uid)} WHERE account_id = $this->local_id");
      return $upd;
    }
    
    return false;
  }
}