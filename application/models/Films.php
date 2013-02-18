<?php

    class Application_Model_Films extends Application_Model_DbTable_Films
    {
#datenbank

        public $UserID, $Vorname, $Nachname, $Email, $Kennwort, $UuID, $Nickname, $Description, $last_login, $itime, $Admin, $utime;
#class
        public $Namespace = "login";
        public $Colums = array('U.UserID', 'U.Vorname', 'U.Nachname', 'U.Email', 'U.Kennwort', 'U.UuID', 'U.Nickname', 'U.Description', 'U.Admin', 'U.itime', 'U.utime', 'U.last_login');
#other
        private $Session;

        public function set_User($User = null)
        {
            if (!is_object($User)) {
                $this->UserID = -1;
                $this->Vorname = "";
                $this->Nachname = "";
                $this->Email = "";
                $this->Kennwort = "";
                $this->UuID = null;
                $this->Nickname = "";
                $this->Description = null;
                $this->utime = null;
                $this->itime = null;
                $this->last_login = null;
                $this->Admin = false;
                return false;
            } else {
                $this->UserID = $User->UserID;
                $this->Vorname = $User->Vorname;
                $this->Nachname = $User->Nachname;
                $this->Kennwort = $User->Kennwort;
                $this->Email = $User->Email;
                $this->Nickname = $User->Nickname;
                $this->Description = null;
                $this->Admin = $User->Admin;
                $this->UuID = $User->UuID;
                $this->itime = $User->itime;
                $this->last_login = $User->last_login;
                $this->utime = $User->utime;
                return true;
            }
        }

        public function __construct()
        {
            $this->Session = new Zend_Session_Namespace($this->Namespace);
            parent::__construct();
            $this->set_User();
        }

        public function getByUuID($UuID)
        {
            $User = $this->fetchAll($this->select()
                        ->from(array('U' => 'User'), $this->Colums)
                        ->where('U.UuID = ?', $UuID)
                        ->setIntegrityCheck(false))->current();
            return $this->set_User($User);
        }

        public function getByID($ID)
        {
            $User = $this->fetchAll($this->select()
                        ->from(array('U' => 'User'), $this->Colums)
                        ->where('U.UserID = ?', $ID)
                        ->setIntegrityCheck(false))->current();
            return $this->set_User($User);
        }

        public function getByNickname($Nickname)
        {
            $User = $this->fetchAll($this->select()
                        ->from(array('U' => 'User'), $this->Colums)
                        ->where('U.Nickname = ?', $Nickname)
                        ->setIntegrityCheck(false))->current();
            return $this->set_User($User);
        }

        public function getByEmail($Email)
        {
            $User = $this->fetchAll($this->select()
                        ->from(array('U' => 'User'), $this->Colums)
                        ->where('U.Email = ?', $Email)
                        ->setIntegrityCheck(false))->current();
            return $this->set_User($User);
        }

        public function getonlinefriends()
        {
            $Friends = $this->fetchAll()->toArray();
            foreach ($Friends as $K => $V) {
                if ($Friends[$K]['UserID'] == $this->UserID) {
                    unset($Friends[$K]);
                } else {
                    unset($Friends[$K]['UserID']);
                    unset($Friends[$K]['Vorname']);
                    unset($Friends[$K]['Email']);
                    unset($Friends[$K]['Kennwort']);
                    unset($Friends[$K]['CityID']);
                    unset($Friends[$K]['Admin']);
                    unset($Friends[$K]['time']);
                    unset($Friends[$K]['utime']);
                }
            }

            return $Friends;
        }

        public function getAllyUsersByCityID($CityID)
        {
            $Users = $this->fetchAll($this->select()
                    ->from(array('U' => 'User'), $this->Colums)
                    ->JoinLeft(array('ACU' => 'AllyCityUser'), 'U.UserID = ACU.UserID')
                    ->where('ACU.CityID = ?', $CityID)
                    ->where('ACU.Ally = ?', 1)
                    ->setIntegrityCheck(false));
            return $Users;
        }

        public function getAdminUsersByCityID($CityID)
        {
            $Users = $this->fetchAll($this->select()
                    ->from(array('U' => 'User'), $this->Colums)
                    ->JoinLeft(array('ACU' => 'AllyCityUser'), 'U.UserID = ACU.UserID')
                    ->where('ACU.CityID = ?', $CityID)
                    ->where('ACU.Member = ?', 1)
                    ->setIntegrityCheck(false));

            return $Users;
        }

        public function getModelCity()
        {
            return new Application_Model_City();
        }

        public function create($Vorname, $Nachname, $Kennwort, $Email, $UuID = null, $Admin = false)
        {
            $this->UserID = -1;
            $this->Vorname = $Vorname;
            $this->Nachname = $Nachname;
            $this->Kennwort = hash('sha512', $Kennwort);
            $this->Email = $Email;
            $this->Admin = $Admin;
            $this->UuID = $UuID;
            $this->Session->Email = $this->Email;
            $this->Session->Kennwort = $this->Kennwort;
            $this->write();
        }

        public function write()
        {
            if ($this->UuID === null) {
                $this->UuID = ZFC_Generate_UuID::UuID();
            }
            $Data = array('Vorname' => $this->Vorname,
                'Nachname' => $this->Nachname,
                'Email' => $this->Email,
                'Kennwort' => $this->Kennwort,
                'Admin' => $this->Admin,
                'UuID' => $this->UuID);
            if ($this->UserID == -1) {
                $this->UserID = $this->insert($Data);
            } else {
                $Data['utime'] = date('Y-m-d H:i:s');
                $this->update($Data, 'UserID = ' . $this->UserID);
            }
        }

        #updating lastlogin time

        public function UpdateLoginTime()
        {
            $this->last_login = date('Y-m-d H:i:s');
            $Data = array('last_login' => $this->last_login);
            $this->update($Data, 'UserID = ' . $this->UserID);
        }

        public function login($Email, $Kennwort, $Save = false)
        {
            if (null != $Email && null != $Kennwort && '' != trim($Email) && '' != $Kennwort) {
                if ($this->getByEmail($Email)) {
                    if ($this->Kennwort == $Kennwort) {
                        $this->Session->Email = $this->Email;
                        $this->Session->Kennwort = $this->Kennwort;
                        if ($Save) {
                            setcookie("user_unique", $this->UuID, strtotime('+30 days'), '/');
                            setcookie("user_token", crypt($this->Kennwort, $this->Email), strtotime('+30 days'), '/');
                        }
                        $this->UpdateLoginTime();
                        return true;
                    }
                }
            }
            $this->set_User();
            return false;
        }

        public function getCity()
        {
            $Model_City = new Application_Model_City();
            $Model_City->getByID($this->CityID);
            return $Model_City;
        }

        public function logout()
        {
# die();
            $this->Session->Email = null;
            $this->Session->Kennwort = null;
            unset($_COOKIE["user_token"]);
            unset($_COOKIE["user_unique"]);
            setcookie("user_token", "", time() - 3600, '/');
            setcookie("user_unique", "", time() - 3600, '/');
        }

        public function loadSessionUser($lookForCookie = false)
        {
            if ($this->login($this->Session->Email, $this->Session->Kennwort)) {
                return true;
            }
            if ($lookForCookie && isset($_COOKIE['user_thoken']) && isset($_COOKIE['user_unique'])) {
                if ($this->getByUuID($_COOKIE['user_unique'])) {
                    if (crypt($this->Kennwort, $this->Email) == $_COOKIE['user_thoken']) {
                        $this->Session->Kennwort = $this->Kennwort;
                        $this->Session->Email = $this->Email;
                        $this->UpdateLoginTime();
                        return true;
                    }
                }
            }
            $this->set_User();
            return false;
        }

    }

    