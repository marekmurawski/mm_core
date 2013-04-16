<?php

/* Security measure */
if ( !defined('IN_CMS') )
    exit();


class mmInstaller {

    private $errorMessages   = array( );
    private $infoMessages    = array( );
    private $successMessages = array( );
    private $plugin_name     = '';

    public function __construct($name) {
        $this->plugin_name = trim($name, '/\\');

    }


    public function __destruct() {
        $this->send();

    }


    public function logError($msg) {
        $this->errorMessages[] = '<b>[' . $this->plugin_name . ']</b> ' . $msg;
        echo 'ERROR: ' . $msg . '<br/>';

    }


    public function logInfo($msg) {
        $this->infoMessages[] = '<b>[' . $this->plugin_name . ']</b> ' . $msg;
        echo 'INFO: ' . $msg . '<br/>';

    }


    public function logSuccess($msg) {
        $this->successMessages[] = '<b>[' . $this->plugin_name . ']</b> ' . $msg;
        echo $msg . '<br/>';

    }


    public function send() {
        if ( !empty($this->infoMessages) ) {
            Flash::set('info', implode('<br/>', $this->infoMessages));
        }
        if ( !empty($this->errorMessages) ) {
            Flash::set('error', implode('<br/>', $this->errorMessages));
        }

        if ( empty($this->errorMessages) )
            $this->successMessages[] = __('Successfully activated plugin!');

        if ( !empty($this->successMessages) ) {
            Flash::set('success', implode('<br/>', $this->successMessages));
        }

    }


    public function importSnippets($path) {
        $path       = trim($path, '/\\');
        $samplesDir = PLUGINS_ROOT . DS . $this->plugin_name . DS . $path;

        $scandir = scandir($samplesDir);

        foreach ( $scandir as $k => $v ) {
            if ( $v == '.' || $v == '..' || !endsWith($v, '.php') ) {
                unset($scandir[$k]);
            }
        }

        //echo '<pre>' . print_r( $scandir, true ) . '</pre>';
        $snippetNamesStr = '';
        $cnt             = 0;
        foreach ( $scandir as $file ) {
            if ( $snippetContent = file_get_contents($samplesDir . DS . $file) ) {

                $newSnippet                = new Snippet;
                $newSnippet->name          = str_replace('.php', '', $file);
                $newSnippet->created_on    = date('Y-m-d H:i:s');
                $newSnippet->content       = $snippetContent;
                $newSnippet->content_html  = $snippetContent;
                $newSnippet->created_by_id = 1;
                if ( $newSnippet->save() ) {
                    $snippetNamesStr .= $newSnippet->name . ', ';
                    $cnt++;
                };
                if ( $cnt ) {
                    $this->logInfo(__('Imported <b>:count</b> snippets! <br/><b>:names</b>', array(
                                ':count' => $cnt,
                                ':names' => $snippetNamesStr,
                    )));
                }
            }
        }

    }


    /**
     * Delete Permission
     *
     * @param string $permissionName
     */
    public static function deletePermission($permissionName) {
        if ( $perm = Permission::findByName($permissionName) ) {

            // unrelate roles assigned to permission
            RolePermission::deleteWhere('RolePermission', 'permission_id=?', array( $perm->id ));

            if ( !$perm->delete() ) {
                self::logError(__('Permission <b>:perm</b> could not be deleted', array( ':perm' => $permissionName )));
                return false;
            } else {
                self::logInfo(__('Permission <b>:perm</b> deleted', array( ':perm' => $permissionName )));
                return true;
            }
        } else {
            self::logInfo(__('Permission <b>:perm</b> was not found and not deleted!', array( ':perm' => $permissionName )));
            return true;
        }

    }


    /**
     * Create Permission
     *
     * @param string $permissionName
     */
    public function createPermission($permissionName) {
        if ( !Permission::findByName($permissionName) ) {
            $perm = new Permission(array( 'name' => $permissionName ));
            if ( !$perm->save() ) {
                $this->logError(__('Permission <b>:perm</b> could not be created', array( ':perm' => $permissionName )));
                return false;
            } else {
                $this->logInfo(__('Permission <b>:perm</b> created', array( ':perm' => $permissionName )));
                return true;
            }
        } else {
            $this->logInfo(__('Permission <b>:perm</b> already exists', array( ':perm' => $permissionName )));
            return true;
        }

    }


    /**
     * Delete Role
     *
     * @param string $roleName
     */
    public function deleteRole($roleName) {
        if ( $role = Role::findByName($roleName) ) {

            if ( Record::existsIn('RolePermission', 'role_id=?', array( $role->id )) ) {
                $this->logError(__('Role <b>:role</b> has some permissions - cannot delete role with existing permissions'));
                return false;
            };

            if ( !$role->delete() ) {
                $this->logError(__('Role <b>:role</b> could not be deleted', array( ':role' => $roleName )));
                return false;
            } else {
                $this->logInfo(__('Role <b>:role</b> deleted!', array( ':role' => $roleName )));
                return true;
            }
        } else {
            $this->logInfo(__('Role <b>:role</b> was not found and not deleted!', array( ':role' => $roleName )));
            return true;
        }

    }


    /**
     * Create Role
     *
     * @param string $roleName
     */
    public function createRole($roleName) {
        if ( !Role::findByName($roleName) ) {
            $role = new Role(array( 'name' => $roleName ));
            if ( !$role->save() ) {
                $this->logError(__('Could not create role <b>:role</b>', array( ':role' => $roleName )));
                return false;
            } else {
                $this->logInfo(__('Created role <b>:role</b>', array( ':role' => $roleName )));
                return true;
            }
        } else {
            $this->logInfo(__('Role <b>:role</b> already exists!', array( ':role' => $roleName )));
            return true;
        }

    }


    /**
     * Assign Permission to Role
     *
     * @global type $errorMessages
     * @global string $infoMessages
     * @param type $permissionName
     * @param type $roleName
     * @return boolean
     */
    public function assignPermissionToRole($permissionName, $roleName) {

        $perm = Permission::findByName($permissionName);
        $role = Role::findByName($roleName);
        if ( ($role && $perm ) ) {
            if ( Record::existsIn('RolePermission', 'permission_id=? AND role_id=?', array( $perm->id, $role->id )) ) {
                $this->logInfo(__('Role <b>:role</b> already has permission <b>:perm</b>!', array( ':perm' => $permissionName, ':role' => $roleName )));
                return true;
            }
            $rp = new RolePermission(array( 'permission_id' => $perm->id, 'role_id'       => $role->id ));
            if ( !$rp->save() ) {
                $this->logError(__('Could not assign permission <b>:perm</b> to role <b>:role</b>!', array( ':perm' => $permissionName, ':role' => $roleName )));
                return false;
            }
            else
                $this->logInfo(__('Assigned permission <b>:perm</b> to role <b>:role</b>!', array( ':perm' => $permissionName, ':role' => $roleName )));
            return true;
        } else {
            $this->logError(__('Either permission <b>:perm</b> or role <b>:role</b> does not exist!', array( ':perm' => $permissionName, ':role' => $roleName )));
            return false;
        }

    }


}