<?php

/**
 * Users
 *
 */

class admin_1200_Settings extends admin_MAIN_Settings {

	/**
	 * Initialization
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Calls from Administration-Interface
	 *
	 * @access public
	 */
	public function createActionBasedContent() {
		$userCheck = pCheckUserData::getInstance();

		if ($userCheck->checkAccess($this->_adminAccess)) {
		// Check which Action is called and return the appropriate content or JSON
			switch (pURIParameters::get('action', '', pURIParameters::$STRING)) {
				case 'template':
					$tpl = pURIParameters::get('template', 'usermanager', pURIParameters::$STRING);
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$tpl = $this->getAdminContent($tpl, 1200);
					$tpl = str_replace('[SECTION_ID]', $section, $tpl);
					$tpl = str_replace('[ENTRY_ID]', $entry, $tpl);
					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;

				case 'sections':
					echo $this->getJSONUserList();
					exit();
					break;

				case 'content':
					$section = pURIParameters::get('section', 0, pURIParameters::$INT);
					echo '{"section":'.$section.',data:';
					echo $this->getJSONUserData($section);
					echo '}';
					exit();
					break;

				case 'grouplist':
					$user = pURIParameters::get('section', 0, pURIParameters::$INT);
					echo '{"success":true,"call":"_loadUserGroups",data:';
					echo $this->getJSONGroupList();
					echo '}';
					exit();
					break;

				case 'accesslist':
					echo '{"success":true,"call":"_loadAccessList",data:';
					echo $this->getJSONAccessList();
					echo '}';
					exit();
					break;

				case 'saveuser':
					$user = pURIParameters::get('id', 0, pURIParameters::$INT);
					$data = pURIParameters::get('data', null, pURIParameters::$OBJECT);
					$groups = pURIParameters::get('groups', array(), pURIParameters::$ARRAY);
					$rights = pURIParameters::get('rights', array(), pURIParameters::$ARRAY);

					$success = $this->changeUserData($user, $data);
					if ($success) {
						$success = $this->changeUserGroups($user, $groups);
						if ($success) {
							$success = $this->changeUserRights($user, $rights);
						}
					}

					echo '{"success":'.($success ? 'true' : 'false').'}';
					exit();
					break;

				case 'section':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$name = utf8_decode(pURIParameters::get('name', 'Benutzername', pURIParameters::$STRING));
					$newid = $this->createUserOrChangeUsername($id, $name);

					if ($newid === false) {
						echo '{"success":false}';

					} else if ($id != $newid) {
						echo '{"adm":1200,"name":"'.$this->_escapeJSONString($name).'","oldid":'.$id.',"id":'.$newid.',"parent":0,"call":"sectionCreateFinal","click":"UserManagement.sectionClick","icon":"'.MAIN_DIR.'editor/admin_editor/css/user.png","success":true}';

					} else {
						echo '{"success":true}';
					}
					exit();
					break;

				case 'section_remove':
					$id = pURIParameters::get('id', 0, pURIParameters::$INT);
					$success = $this->deleteUser($id);
					echo '{"id":'.$id.',"call":"sectionDeleteFinal","success":'.($success?'true':'false').',"scope":"UserManagement"}';
					exit();
					break;

				case 'addusergroup':
					$entry = pURIParameters::get('entry', 0, pURIParameters::$INT);
					$groupData = $this->getGroupData($entry);
					$tpl = $this->getAdminContent('usergroups_form', 1200);
					$tpl = str_replace('[GROUP_ID]', $groupData->id, $tpl);
					$tpl = str_replace('[GROUP_NAME]', $groupData->name, $tpl);
					$tpl = str_replace('[GROUP_DESCRIPTION]', $groupData->description, $tpl);
					echo $this->ReplaceAjaxLanguageParameters( $tpl );
					exit();
					break;
				case 'savegroup':
					$groupId = pURIParameters::get('groupid', 0, pURIParameters::$INT);
					$groupName = pURIParameters::get('name', '', pURIParameters::$STRING);
					$groupDescription = pURIParameters::get('description', '', pURIParameters::$STRING);
					$id = $this->changeGroupData($groupId, $groupName, $groupDescription);
					echo '{"success":'.($id > 0 ? 'true' : 'false').',"call":"'.(($id == $groupId) ? '_changeGroupData' : '_addCreatedGroup').'","scope":"UserManagement","data":{"id":'.$id.',"name":"'.$groupName.'","description":"'.$groupDescription.'"}}';
					exit();
					break;
				case 'deletegroup':
					$groupId = pURIParameters::get('groupid', 0, pURIParameters::$INT);
					$success = $this->deleteGroup($groupId);
					echo '{"success":'.($success ? 'true' : 'false').',"groupid":'.$groupId.',"call":"deleteGroupFinal","scope":"UserManagement"}';
					exit();
					break;
			}
		} else {
			$this->showNoAccess();
		}
	}

	/**
	 * Get all users as a JSON-String
	 *
	 * @return string
	 * @access private
	 */
	private function getJSONUserList() {
		$back = '';
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'SELECT [user.id] FROM [table.user] ORDER BY [user.user] ASC;';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$list = array();
				while ($res->getNext()) {
					$person = new pUserAccount($res->{$db->getFieldName('user.id')});
					if ($person->isLoaded()) {
						$o = $person->getJSONObject(false, true);
						$o->name = $o->username;
						$o->icon = MAIN_DIR.'editor/admin_editor/css/user.png';
						$list[] = json_encode($o);
					}
				}
			}
			$back .= '['.implode(',', $list).']';
		} else {
			$back .= '["error":"no access"]';
		}
		$back .= '';
		return $back;
	}

	/**
	 * Get all Userdata as a JSON-String
	 *
	 * @param integer $id UserID to get Data from
	 * @return string
	 * @access private
	 */
	private function getJSONUserData($id) {
		$back = '';
		$userCheck = pCheckUserData::getInstance();
		$user = new pUserAccount($id);
		if ($userCheck->checkAccess($this->_adminAccess) || ( $user->get('userId') == $userCheck->getPerson()->get('userId') )) {
			$back = $user->getJSONObject(false);
			$back->groups = $this->getUserGroups($id);
			$back->rights = $this->getUserRights($id);
			$back = json_encode($back);
		} else {
			$back .= '{"error":"no access"}';
		}
		return $back;
	}

	/**
	 * Create a new User if $id is '0' or change the Username from given User
	 *
	 * @param int $id UserID to change the Username from
	 * @param string $name Username
	 * @return integer The UserID
	 * @access private
	 */
	private function createUserOrChangeUsername($id, &$name) {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$person = new pUserAccount($id);

			// Don't let the User change the username from 'admin'
			// Don't let the User change a Username into 'admin'
			if ( ($person->get('username') == 'admin') || (strtolower($name) == 'admin') ) {
				return false;
			}

			// If the User does not exist, create it with a random Password, else change the Username
			if (!$person->isLoaded()) {
				$state = 'username';
				$name_add = '';
				while ($state == 'username') {
					$state = $person->registerUser($name . (string)$name_add, pGUID::getGUID(), '', false);
					$name_add = empty($name_add) ? 1 : $name_add++;
				}
				$name . (string)$name_add;
			} else {
				$person->set('username', $name);
			}
			return $person->get('userId');
		}
		return false;
	}

	/**
	 * Delete the given User
	 * Attention: The User 'admin' cannot be deleted
	 *
	 * @param unknown_type $id
	 */
	private function deleteUser($id) {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$person = new pUserAccount($id);

			// Don't let the User delete the user 'admin'
			if ($person->get('username') == 'admin') {
				return false;
			}

			// Delete the User
			if ($person->isLoaded()) {
				$person->deleteUserAccount();
				$this->changeUserGroups($id, array());
				$person = new pUserAccount($id);
			}
			return $person->get('userId') <= 0;
		}
		return false;
	}

	/**
	 * Get all Groups as a JSON-String
	 *
	 * @return string
	 * @access private
	 */
	private function getJSONGroupList() {
		$back = '{"error":"no access"}';
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'SELECT * FROM [table.grp] ORDER BY [grp.name] ASC;';
			$db->run($sql, $res);
			$list = array();
			if ($res->getFirst()) {
				while ($res->getNext()) {
					$group = new stdClass();
					$group->id = (int)$res->{$db->getFieldName('grp.id')};
					$group->name = $res->{$db->getFieldName('grp.name')};
					$group->description = $res->{$db->getFieldName('grp.descr')};
					array_push($list, $group);
				}
			}
			$back = json_encode($list);
		}
		return $back;
	}

	/**
	 * Get all Access-Rights as JSON-Content
	 *
	 * @return string JSON-Array
	 * @access private
	 */
	private function getJSONAccessList() {
		$back = '{"error":"no access"}';
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$back = json_encode( $this->getAllUserRightsAsObjectlist() );
		}
		return $back;
	}

	/**
	 * Change a user or create a new one
	 *
	 * @param int $id UserID - '0' for a new User
	 * @param stdClass $user Userdata to use for the User
	 * @return boolean state of success
	 * @access private
	 */
	private function changeUserData($id, stdClass $user) {
		$back = false;
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$person = new pUserAccount($id);

			// Cannot change a nonexistent User
			if (!$person->isLoaded()) {
				return false;
			}

			// Set all Data
			$person->set('address', $user->address, false);
			$person->set('surname', $user->surname, false);
			$person->set('lastname', $user->lastname, false);
			$person->set('usernumber', $user->usernumber, false);
			$person->set('company', $user->company, false);
			$person->set('zip', $user->zip, false);
			$person->set('title', $user->title, false);
			$person->set('city', $user->city, false);
			if (!empty($user->password)) {
				$person->set('password', $user->password, false);
			}
			$person->saveUserAccount();
			$back = $person->get('userId') > 0;

		}
		return $back;
	}

	/**
	 * Change Usergroups
	 *
	 * @param int $id UserID
	 * @param array $userGroups all Groups the user is assigned to
	 * @return boolean state off success
	 * @access private
	 */
	private function changeUserGroups($id, array $userGroups) {
		$success = false;
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			// Drop user out of each group first
			$sql = 'DELETE FROM [table.usrgrp] WHERE [usrgrp.user]='.(int)$id.';';
			$db->run($sql, $res);

			// Insert the User to all selected Groups
			if (count($userGroups) > 0) {
				$sql = '';
				foreach ($userGroups as $grp) {
					if (strlen($sql) > 0) {
						$sql .= ',';
					}
					$sql .= '('.(int)$id.','.(int)$grp.')';
				}
				$sql = 'INSERT INTO [table.usrgrp] ([field.usrgrp.user],[field.usrgrp.group]) VALUES '.$sql.';';
				$db->run($sql, $res);
				$success = $res->numAffected();
			} else {
				$success = true;
			}
		}
		return $success;
	}

	/**
	 * Delete a UserGroup
	 *
	 * @param int $id GroupID to delete
	 * @return boolean
	 * @access private
	 */
	private function deleteGroup($id) {
		$id = (int)$id;
		$back = false;
		if ($id > 0) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'DELETE FROM [table.grp] WHERE [field.grp.id]='.$id.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.usrgrp] WHERE [field.usrgrp.group]='.$id.';';
			$db->run($sql, $res);
			$res = null;

			$sql = 'DELETE FROM [table.menugrp] WHERE [field.menugrp.group]='.$id.';';
			$db->run($sql, $res);
			$res = null;

			$back = true;
		}
		return $back;
	}

	/**
	 * Change Userrights
	 *
	 * @param int $id UserID
	 * @param array $userRights User-Rights
	 * @return boolean state of success
	 * @access private
	 */
	private function changeUserRights($id, array $userRights) {
		$back = false;
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$person = new pUserAccount($id);
			if ($person->isLoaded()) {
				$list = array();

				// Don't let the user change rights from User "admin"
				if ($person->get('username') == 'admin') {
					$list[] = (int)RGT_FULLADMIN;

				} else {
					foreach ($userRights as $rgt) {
						if ((substr($rgt, 0, 4) == 'RGT_') && defined($rgt)) {
							$list[] = (int)constant($rgt);
						}
					}
				}

				$person->setRight($list);
				$back = true;
			}
		}
		return $back;
	}

	/**
	 * Change or Create a new Group
	 *
	 * @param int $groupId GroupID to change or '0' for a new Group
	 * @param string $groupName Name of the Group
	 * @param string $groupDescription Group-Description
	 * @return string
	 * @access private
	 */
	private function changeGroupData($groupId, $groupName, $groupDescription) {
		$userCheck = pCheckUserData::getInstance();
		if ($userCheck->checkAccess($this->_adminAccess)) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;

			// Get GroupData to check if the Group exists or not
			$group = $this->getGroupData($groupId);

			// Add a new Group if no ID is given or save it
			if ($group->id <= 0) {
				$sql = 'INSERT INTO [table.grp] ([field.grp.name],[field.grp.descr]) VALUES (\''.mysql_real_escape_string($groupName).'\',\''.mysql_real_escape_string($groupDescription).'\');';
				$db->run($sql, $res);
				$groupId = $res->getInsertId();
			} else {
				$sql = 'UPDATE [table.grp] SET [field.grp.name]=\''.mysql_real_escape_string($groupName).'\',[field.grp.descr]=\''.mysql_real_escape_string($groupDescription).'\' WHERE [field.grp.id]='.(int)$groupId.';';
				$db->run($sql, $res);
			}
			$res = null;
			return $groupId;
		}
		return 0;
	}

	/**
	 * Get Data from a UserGroup
	 *
	 * @param int $id The GroupID
	 * @return stdClass {id,name,description}
	 */
	private function getGroupData($id) {
		$userCheck = pCheckUserData::getInstance();
		$back = new stdClass();
		$back->id = 0;
		$back->name = '';
		$back->description = '';

		if ($userCheck->checkAccess($this->_adminAccess)) {
			$db = pDatabaseConnection::getDatabaseInstance();
			$res = null;
			$sql = 'SELECT * FROM [table.grp] WHERE [grp.id]='.(int)$id.';';
			$db->run($sql, $res);
			if ($res->getFirst()) {
				$back->id = (int)$res->{$db->getFieldName('grp.id')};
				$back->name = $res->{$db->getFieldName('grp.name')};
				$back->description = $res->{$db->getFieldName('grp.descr')};
			}
			$res = null;
		}

		return $back;
	}

}
?>